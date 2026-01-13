<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $department = Department::factory()->create(['name' => 'Engineering']);
        $designation = Designation::factory()->create(['name' => 'Software Engineer']);
        $employeeType = EmployeeType::factory()->create(['name' => 'Full-time']);

        Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_code' => 'EMP001',
            'email' => 'john.doe@example.com',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'employee_type_id' => $employeeType->id,
            'status' => 'active',
            'gender' => 'male',
            'salary' => 75000,
            'date_of_joining' => '2023-01-15',
        ]);

        Employee::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'employee_code' => 'EMP002',
            'email' => 'jane.smith@example.com',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'employee_type_id' => $employeeType->id,
            'status' => 'active',
            'gender' => 'female',
            'salary' => 80000,
            'date_of_joining' => '2023-03-20',
        ]);
    }

    /** @test */
    public function it_can_search_by_first_name()
    {
        $results = Employee::searchAndFilter(['search' => 'John'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John', $results->first()->first_name);
    }

    /** @test */
    public function it_can_search_by_last_name()
    {
        $results = Employee::searchAndFilter(['search' => 'Smith'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Smith', $results->first()->last_name);
    }

    /** @test */
    public function it_can_search_by_employee_code()
    {
        $results = Employee::searchAndFilter(['search' => 'EMP001'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('EMP001', $results->first()->employee_code);
    }

    /** @test */
    public function it_can_search_by_email()
    {
        $results = Employee::searchAndFilter(['search' => 'jane.smith'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('jane.smith@example.com', $results->first()->email);
    }

    /** @test */
    public function it_can_search_by_full_name()
    {
        $results = Employee::searchAndFilter(['search' => 'John Doe'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John', $results->first()->first_name);
        $this->assertEquals('Doe', $results->first()->last_name);
    }

    /** @test */
    public function it_can_filter_by_department()
    {
        $department = Department::first();

        $results = Employee::searchAndFilter(['department_id' => $department->id])->get();

        $this->assertCount(2, $results);
        $results->each(function ($employee) use ($department) {
            $this->assertEquals($department->id, $employee->department_id);
        });
    }

    /** @test */
    public function it_can_filter_by_status()
    {
        $results = Employee::searchAndFilter(['status' => 'active'])->get();

        $this->assertCount(2, $results);
        $results->each(function ($employee) {
            $this->assertEquals('active', $employee->status);
        });
    }

    /** @test */
    public function it_can_filter_by_gender()
    {
        $results = Employee::searchAndFilter(['gender' => 'male'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('male', $results->first()->gender);
    }

    /** @test */
    public function it_can_filter_by_salary_range()
    {
        $results = Employee::searchAndFilter([
            'salary_min' => 70000,
            'salary_max' => 78000
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals(75000, $results->first()->salary);
    }

    /** @test */
    public function it_can_filter_by_hire_date_range()
    {
        $results = Employee::searchAndFilter([
            'hire_date_from' => '2023-01-01',
            'hire_date_to' => '2023-02-28'
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('2023-01-15', $results->first()->date_of_joining);
    }

    /** @test */
    public function it_can_sort_by_different_columns()
    {
        $results = Employee::searchAndFilter([
            'sort_by' => 'first_name',
            'sort_direction' => 'asc'
        ])->get();

        $this->assertCount(2, $results);
        $this->assertEquals('Jane', $results->first()->first_name);
        $this->assertEquals('John', $results->last()->first_name);
    }

    /** @test */
    public function it_combines_search_and_filters()
    {
        $department = Department::first();

        $results = Employee::searchAndFilter([
            'search' => 'John',
            'department_id' => $department->id,
            'status' => 'active'
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John', $results->first()->first_name);
    }

    /** @test */
    public function it_returns_all_records_when_no_filters_applied()
    {
        $results = Employee::searchAndFilter([])->get();

        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_prevents_sql_injection_in_sort_column()
    {
        // This should not cause an error and should default to created_at desc
        $results = Employee::searchAndFilter([
            'sort_by' => 'DROP TABLE users;--',
            'sort_direction' => 'asc'
        ])->get();

        $this->assertCount(2, $results);
    }
}