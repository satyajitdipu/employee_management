<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test department listing.
     */
    public function test_user_can_view_departments(): void
    {
        $user = User::factory()->create();
        Department::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/departments');

        $response->assertStatus(200);
        $response->assertViewHas('departments');
    }

    /**
     * Test department creation.
     */
    public function test_user_can_create_department(): void
    {
        $user = User::factory()->create();

        $departmentData = [
            'name' => 'Quality Assurance',
            'description' => 'Testing and quality control department',
        ];

        $response = $this->actingAs($user)->post('/departments', $departmentData);

        $response->assertRedirect('/departments');
        $this->assertDatabaseHas('departments', $departmentData);
    }

    /**
     * Test department update.
     */
    public function test_user_can_update_department(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $updateData = [
            'name' => 'Updated Department',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($user)->put("/departments/{$department->id}", $updateData);

        $response->assertRedirect('/departments');
        $this->assertDatabaseHas('departments', $updateData);
    }

    /**
     * Test department deletion.
     */
    public function test_user_can_delete_department(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $response = $this->actingAs($user)->delete("/departments/{$department->id}");

        $response->assertRedirect('/departments');
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}