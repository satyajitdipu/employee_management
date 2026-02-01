<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeExportService
{
    /**
     * Export employees to CSV format
     *
     * @param Collection $employees
     * @param array $columns
     * @return StreamedResponse
     */
    public function exportToCsv(Collection $employees, array $columns = []): StreamedResponse
    {
        $defaultColumns = [
            'employee_code',
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'phone',
            'department_name',
            'designation_name',
            'employee_type_name',
            'status',
            'gender',
            'date_of_birth',
            'date_of_joining',
            'salary',
            'manager_name',
        ];

        $exportColumns = !empty($columns) ? $columns : $defaultColumns;

        return response()->stream(function () use ($employees, $exportColumns) {
            $handle = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($handle, array_map(function ($column) {
                return $this->formatColumnHeader($column);
            }, $exportColumns));

            // Write employee data
            foreach ($employees as $employee) {
                $row = [];
                foreach ($exportColumns as $column) {
                    $row[] = $this->getEmployeeFieldValue($employee, $column);
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employees_' . date('Y-m-d_H-i-s') . '.csv"',
        ]);
    }

    /**
     * Export employees to Excel format (returns data array for libraries like Laravel Excel)
     *
     * @param Collection $employees
     * @param array $columns
     * @return array
     */
    public function exportToExcel(Collection $employees, array $columns = []): array
    {
        $defaultColumns = [
            'employee_code',
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'phone',
            'department_name',
            'designation_name',
            'employee_type_name',
            'status',
            'gender',
            'date_of_birth',
            'date_of_joining',
            'salary',
            'manager_name',
        ];

        $exportColumns = !empty($columns) ? $columns : $defaultColumns;

        $data = [];

        // Add headers
        $data[] = array_map(function ($column) {
            return $this->formatColumnHeader($column);
        }, $exportColumns);

        // Add employee data
        foreach ($employees as $employee) {
            $row = [];
            foreach ($exportColumns as $column) {
                $row[] = $this->getEmployeeFieldValue($employee, $column);
            }
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get available export columns
     *
     * @return array
     */
    public function getAvailableColumns(): array
    {
        return [
            'employee_code' => 'Employee Code',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'department_name' => 'Department',
            'designation_name' => 'Designation',
            'employee_type_name' => 'Employee Type',
            'status' => 'Status',
            'gender' => 'Gender',
            'date_of_birth' => 'Date of Birth',
            'date_of_joining' => 'Date of Joining',
            'salary' => 'Salary',
            'manager_name' => 'Manager',
            'nationality' => 'Nationality',
            'blood_group' => 'Blood Group',
            'marital_status' => 'Marital Status',
            'years_of_experience' => 'Years of Experience',
        ];
    }

    /**
     * Format column header for display
     *
     * @param string $column
     * @return string
     */
    private function formatColumnHeader(string $column): string
    {
        return $this->getAvailableColumns()[$column] ?? ucwords(str_replace('_', ' ', $column));
    }

    /**
     * Get employee field value
     *
     * @param Employee $employee
     * @param string $field
     * @return mixed
     */
    private function getEmployeeFieldValue(Employee $employee, string $field)
    {
        switch ($field) {
            case 'department_name':
                return $employee->department?->name ?? '';
            case 'designation_name':
                return $employee->designation?->name ?? '';
            case 'employee_type_name':
                return $employee->employeeType?->name ?? '';
            case 'manager_name':
                return $employee->manager ? trim($employee->manager->first_name . ' ' . $employee->manager->last_name) : '';
            case 'years_of_experience':
                return $employee->years_of_experience ?? '';
            case 'date_of_birth':
            case 'date_of_joining':
                return $employee->$field ? date('Y-m-d', strtotime($employee->$field)) : '';
            case 'salary':
                return $employee->$field ? number_format($employee->$field, 2) : '';
            default:
                return $employee->$field ?? '';
        }
    }

    /**
     * Validate export columns
     *
     * @param array $columns
     * @return array
     */
    public function validateColumns(array $columns): array
    {
        $availableColumns = array_keys($this->getAvailableColumns());

        return array_filter($columns, function ($column) use ($availableColumns) {
            return in_array($column, $availableColumns);
        });
    }
}