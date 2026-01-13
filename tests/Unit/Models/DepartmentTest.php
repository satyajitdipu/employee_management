<?php

namespace Tests\Unit;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test department creation.
     */
    public function test_department_can_be_created(): void
    {
        $department = Department::factory()->create([
            'name' => 'Engineering',
            'description' => 'Software development department',
        ]);

        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('Engineering', $department->name);
        $this->assertEquals('Software development department', $department->description);
    }

    /**
     * Test department relationships.
     */
    public function test_department_has_employees_relationship(): void
    {
        $department = Department::factory()->create();
        $employee = \App\Models\Employee::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $department->employees);
        $this->assertCount(1, $department->employees);
        $this->assertEquals($employee->id, $department->employees->first()->id);
    }
}