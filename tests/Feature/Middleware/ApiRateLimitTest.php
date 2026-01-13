<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\ApiRateLimit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_requests_within_rate_limit()
    {
        $request = Request::create('/api/test', 'GET');
        $middleware = new ApiRateLimit(app(\Illuminate\Cache\RateLimiter::class));

        $response = $middleware->handle($request, function () {
            return response()->json(['message' => 'success']);
        }, '10', 1);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('10', $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals('9', $response->headers->get('X-RateLimit-Remaining'));
    }

    /** @test */
    public function it_blocks_requests_exceeding_rate_limit()
    {
        $request = Request::create('/api/test', 'GET');
        $middleware = new ApiRateLimit(app(\Illuminate\Cache\RateLimiter::class));

        // Make requests up to the limit
        for ($i = 0; $i < 5; $i++) {
            $middleware->handle($request, function () {
                return response()->json(['message' => 'success']);
            }, '5', 1);
        }

        // This request should be blocked
        $response = $middleware->handle($request, function () {
            return response()->json(['message' => 'success']);
        }, '5', 1);

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertArrayHasKey('message', $response->getData(true));
        $this->assertArrayHasKey('retry_after', $response->getData(true));
        $this->assertEquals('Too many requests. Please try again later.', $response->getData(true)['message']);
    }

    /** @test */
    public function it_returns_correct_headers_when_rate_limited()
    {
        $request = Request::create('/api/test', 'GET');
        $middleware = new ApiRateLimit(app(\Illuminate\Cache\RateLimiter::class));

        // Exceed the rate limit
        for ($i = 0; $i < 3; $i++) {
            $middleware->handle($request, function () {
                return response()->json(['message' => 'success']);
            }, '2', 1);
        }

        $response = $middleware->handle($request, function () {
            return response()->json(['message' => 'success']);
        }, '2', 1);

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals('2', $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals('0', $response->headers->get('X-RateLimit-Remaining'));
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    /** @test */
    public function it_uses_different_limits_for_different_routes()
    {
        $middleware = new ApiRateLimit(app(\Illuminate\Cache\RateLimiter::class));

        $request1 = Request::create('/api/users', 'GET');
        $request2 = Request::create('/api/posts', 'GET');

        // Both should succeed initially
        $response1 = $middleware->handle($request1, function () {
            return response()->json(['message' => 'success']);
        }, '2', 1);

        $response2 = $middleware->handle($request2, function () {
            return response()->json(['message' => 'success']);
        }, '2', 1);

        $this->assertEquals(200, $response1->getStatusCode());
        $this->assertEquals(200, $response2->getStatusCode());
    }

    /** @test */
    public function it_resets_rate_limit_after_decay_time()
    {
        $request = Request::create('/api/test', 'GET');
        $middleware = new ApiRateLimit(app(\Illuminate\Cache\RateLimiter::class));

        // Use up the limit
        for ($i = 0; $i < 2; $i++) {
            $middleware->handle($request, function () {
                return response()->json(['message' => 'success']);
            }, '2', 1);
        }

        // This should be blocked
        $response = $middleware->handle($request, function () {
            return response()->json(['message' => 'success']);
        }, '2', 1);

        $this->assertEquals(429, $response->getStatusCode());

        // Simulate time passing (clear cache to reset)
        Cache::flush();

        // This should succeed again
        $response = $middleware->handle($request, function () {
            return response()->json(['message' => 'success']);
        }, '2', 1);

        $this->assertEquals(200, $response->getStatusCode());
    }
}