<?php

namespace Tests\Unit\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Services\EmployeeExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmployeeExportService $exportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exportService = new EmployeeExportService();

        // Create test data
        $department = Department::factory()->create(['name' => 'Engineering']);
        $designation = Designation::factory()->create(['name' => 'Software Engineer']);
        $employeeType = EmployeeType::factory()->create(['name' => 'Full-time']);
        $manager = Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Manager',
        ]);

        Employee::factory()->create([
            'employee_code' => 'EMP001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '+1234567890',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'employee_type_id' => $employeeType->id,
            'status' => 'active',
            'gender' => 'female',
            'date_of_birth' => '1990-01-15',
            'date_of_joining' => '2023-01-15',
            'salary' => 75000,
            'manager_id' => $manager->id,
        ]);
    }

    /** @test */
    public function it_can_get_available_columns()
    {
        $columns = $this->exportService->getAvailableColumns();

        $this->assertIsArray($columns);
        $this->assertArrayHasKey('employee_code', $columns);
        $this->assertArrayHasKey('first_name', $columns);
        $this->assertArrayHasKey('department_name', $columns);
        $this->assertEquals('Employee Code', $columns['employee_code']);
    }

    /** @test */
    public function it_can_validate_export_columns()
    {
        $validColumns = ['employee_code', 'first_name', 'email'];
        $invalidColumns = ['invalid_column', 'employee_code'];

        $validated = $this->exportService->validateColumns($validColumns);

        $this->assertCount(3, $validated);
        $this->assertContains('employee_code', $validated);
        $this->assertContains('first_name', $validated);
        $this->assertContains('email', $validated);

        $validatedWithInvalid = $this->exportService->validateColumns($invalidColumns);

        $this->assertCount(1, $validatedWithInvalid);
        $this->assertContains('employee_code', $validatedWithInvalid);
        $this->assertNotContains('invalid_column', $validatedWithInvalid);
    }

    /** @test */
    public function it_can_export_to_excel_format()
    {
        $employees = Employee::all();

        $data = $this->exportService->exportToExcel($employees);

        $this->assertIsArray($data);
        $this->assertCount(2, $data); // Header + 1 employee

        // Check header row
        $this->assertEquals('Employee Code', $data[0][0]);
        $this->assertEquals('First Name', $data[0][1]);
        $this->assertEquals('Email', $data[0][4]);

        // Check data row
        $this->assertEquals('EMP001', $data[1][0]);
        $this->assertEquals('Jane', $data[1][1]);
        $this->assertEquals('jane.doe@example.com', $data[1][4]);
    }

    /** @test */
    public function it_can_export_with_custom_columns()
    {
        $employees = Employee::all();
        $customColumns = ['employee_code', 'first_name', 'email'];

        $data = $this->exportService->exportToExcel($employees, $customColumns);

        $this->assertCount(2, $data); // Header + 1 employee
        $this->assertCount(3, $data[0]); // Only 3 columns
        $this->assertEquals('Employee Code', $data[0][0]);
        $this->assertEquals('First Name', $data[0][1]);
        $this->assertEquals('Email', $data[0][2]);
    }

    /** @test */
    public function it_formats_relationship_fields_correctly()
    {
        $employees = Employee::with(['department', 'designation', 'employeeType', 'manager'])->get();

        $data = $this->exportService->exportToExcel($employees);

        // Check relationship fields
        $this->assertEquals('Engineering', $data[1][6]); // department_name
        $this->assertEquals('Software Engineer', $data[1][7]); // designation_name
        $this->assertEquals('Full-time', $data[1][8]); // employee_type_name
        $this->assertEquals('John Manager', $data[1][14]); // manager_name
    }

    /** @test */
    public function it_formats_dates_correctly()
    {
        $employees = Employee::all();

        $data = $this->exportService->exportToExcel($employees);

        $this->assertEquals('1990-01-15', $data[1][11]); // date_of_birth
        $this->assertEquals('2023-01-15', $data[1][12]); // date_of_joining
    }

    /** @test */
    public function it_formats_salary_correctly()
    {
        $employees = Employee::all();

        $data = $this->exportService->exportToExcel($employees);

        $this->assertEquals('75,000.00', $data[1][13]); // salary
    }

    /** @test */
    public function it_handles_missing_relationships()
    {
        // Create employee without relationships
        Employee::factory()->create([
            'employee_code' => 'EMP002',
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'department_id' => null,
            'designation_id' => null,
            'employee_type_id' => null,
            'manager_id' => null,
        ]);

        $employees = Employee::where('employee_code', 'EMP002')->get();

        $data = $this->exportService->exportToExcel($employees);

        $this->assertEquals('', $data[1][6]); // department_name should be empty
        $this->assertEquals('', $data[1][7]); // designation_name should be empty
        $this->assertEquals('', $data[1][14]); // manager_name should be empty
    }

    /** @test */
    public function it_can_create_csv_response()
    {
        $employees = Employee::all();

        $response = $this->exportService->exportToCsv($employees);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContains('attachment; filename="employees_', $response->headers->get('Content-Disposition'));
    }
}