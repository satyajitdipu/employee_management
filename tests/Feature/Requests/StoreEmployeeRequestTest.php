<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreEmployeeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreEmployeeRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_required_fields()
    {
        $request = new StoreEmployeeRequest();

        $rules = $request->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('last_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('hire_date', $rules);
        $this->assertArrayHasKey('salary', $rules);
        $this->assertArrayHasKey('department_id', $rules);
        $this->assertArrayHasKey('designation_id', $rules);
        $this->assertArrayHasKey('employee_type_id', $rules);
        $this->assertArrayHasKey('status', $rules);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $request = new StoreEmployeeRequest();

        $validator = validator([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'hire_date' => '2023-01-01',
            'salary' => 50000,
            'department_id' => 1,
            'designation_id' => 1,
            'employee_type_id' => 1,
            'status' => 'active',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_salary_is_numeric_and_positive()
    {
        $request = new StoreEmployeeRequest();

        $validator = validator([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => '2023-01-01',
            'salary' => -1000,
            'department_id' => 1,
            'designation_id' => 1,
            'employee_type_id' => 1,
            'status' => 'active',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('salary', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_hire_date_is_not_in_future()
    {
        $request = new StoreEmployeeRequest();

        $futureDate = now()->addDays(1)->format('Y-m-d');

        $validator = validator([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => $futureDate,
            'salary' => 50000,
            'department_id' => 1,
            'designation_id' => 1,
            'employee_type_id' => 1,
            'status' => 'active',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('hire_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_status_enum_values()
    {
        $request = new StoreEmployeeRequest();

        $validator = validator([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => '2023-01-01',
            'salary' => 50000,
            'department_id' => 1,
            'designation_id' => 1,
            'employee_type_id' => 1,
            'status' => 'invalid_status',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function it_provides_custom_error_messages()
    {
        $request = new StoreEmployeeRequest();

        $messages = $request->messages();

        $this->assertArrayHasKey('first_name.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('salary.min', $messages);
        $this->assertArrayHasKey('status.in', $messages);
    }
}