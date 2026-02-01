<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeImportService
{
    /**
     * Import employees from CSV file
     *
     * @param UploadedFile $file
     * @param array $options
     * @return array
     */
    public function importFromCsv(UploadedFile $file, array $options = []): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        try {
            $data = $this->parseCsvFile($file);

            if (empty($data)) {
                $results['errors'][] = 'The CSV file is empty or could not be parsed.';
                return $results;
            }

            // Validate headers
            $headerValidation = $this->validateHeaders($data[0]);
            if (!$headerValidation['valid']) {
                $results['errors'] = array_merge($results['errors'], $headerValidation['errors']);
                return $results;
            }

            $headers = $data[0];
            $rows = array_slice($data, 1);

            // Process in chunks to avoid memory issues
            $chunkSize = $options['chunk_size'] ?? 100;
            $chunks = array_chunk($rows, $chunkSize);

            foreach ($chunks as $chunk) {
                $chunkResults = $this->processChunk($chunk, $headers, $options);
                $results['success'] += $chunkResults['success'];
                $results['failed'] += $chunkResults['failed'];
                $results['errors'] = array_merge($results['errors'], $chunkResults['errors']);
                $results['warnings'] = array_merge($results['warnings'], $chunkResults['warnings']);
            }

        } catch (\Exception $e) {
            Log::error('Employee import failed: ' . $e->getMessage());
            $results['errors'][] = 'Import failed: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Parse CSV file into array
     *
     * @param UploadedFile $file
     * @return array
     */
    private function parseCsvFile(UploadedFile $file): array
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');

        while (($row = fgetcsv($handle)) !== false) {
            $data[] = $row;
        }

        fclose($handle);
        return $data;
    }

    /**
     * Validate CSV headers
     *
     * @param array $headers
     * @return array
     */
    private function validateHeaders(array $headers): array
    {
        $requiredHeaders = ['first_name', 'last_name', 'email'];
        $allowedHeaders = [
            'employee_code', 'first_name', 'middle_name', 'last_name', 'email',
            'phone', 'department_name', 'designation_name', 'employee_type_name',
            'status', 'gender', 'date_of_birth', 'date_of_joining', 'salary',
            'manager_email', 'nationality', 'blood_group', 'marital_status'
        ];

        $result = ['valid' => true, 'errors' => []];

        // Check for required headers
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers)) {
                $result['valid'] = false;
                $result['errors'][] = "Required header '{$required}' is missing.";
            }
        }

        // Check for invalid headers
        foreach ($headers as $header) {
            if (!in_array($header, $allowedHeaders)) {
                $result['errors'][] = "Unknown header '{$header}' will be ignored.";
            }
        }

        return $result;
    }

    /**
     * Process a chunk of CSV rows
     *
     * @param array $chunk
     * @param array $headers
     * @param array $options
     * @return array
     */
    private function processChunk(array $chunk, array $headers, array $options): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => [], 'warnings' => []];

        $employees = [];
        $rowNumber = ($options['start_row'] ?? 1);

        foreach ($chunk as $row) {
            $rowNumber++;

            if (count($row) !== count($headers)) {
                $results['errors'][] = "Row {$rowNumber}: Column count mismatch.";
                $results['failed']++;
                continue;
            }

            $employeeData = array_combine($headers, $row);

            // Validate and prepare employee data
            $validationResult = $this->validateEmployeeData($employeeData, $rowNumber);

            if (!$validationResult['valid']) {
                $results['errors'] = array_merge($results['errors'], $validationResult['errors']);
                $results['failed']++;
                continue;
            }

            // Prepare data for bulk insert
            $preparedData = $this->prepareEmployeeData($validationResult['data']);
            if ($preparedData) {
                $employees[] = $preparedData;
            } else {
                $results['failed']++;
            }
        }

        // Bulk insert valid employees
        if (!empty($employees)) {
            try {
                DB::beginTransaction();

                foreach ($employees as $employee) {
                    Employee::create($employee);
                }

                DB::commit();
                $results['success'] += count($employees);

            } catch (\Exception $e) {
                DB::rollBack();
                $results['errors'][] = 'Database error during import: ' . $e->getMessage();
                $results['failed'] += count($employees);
            }
        }

        return $results;
    }

    /**
     * Validate employee data
     *
     * @param array $data
     * @param int $rowNumber
     * @return array
     */
    private function validateEmployeeData(array $data, int $rowNumber): array
    {
        $result = ['valid' => true, 'errors' => [], 'data' => $data];

        $validator = Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'employee_code' => 'nullable|string|unique:employees,employee_code',
            'status' => 'nullable|in:active,inactive,terminated',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'date_of_joining' => 'nullable|date|before_or_equal:today',
            'salary' => 'nullable|numeric|min:0',
            'nationality' => 'nullable|string|max:255',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
        ]);

        if ($validator->fails()) {
            $result['valid'] = false;
            foreach ($validator->errors()->all() as $error) {
                $result['errors'][] = "Row {$rowNumber}: {$error}";
            }
        }

        return $result;
    }

    /**
     * Prepare employee data for database insertion
     *
     * @param array $data
     * @return array|null
     */
    private function prepareEmployeeData(array $data): ?array
    {
        $prepared = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
        ];

        // Handle optional fields
        $optionalFields = [
            'employee_code', 'middle_name', 'phone', 'status', 'gender',
            'date_of_birth', 'date_of_joining', 'salary', 'nationality',
            'blood_group', 'marital_status'
        ];

        foreach ($optionalFields as $field) {
            if (!empty($data[$field])) {
                $prepared[$field] = $data[$field];
            }
        }

        // Handle relationships
        if (!empty($data['department_name'])) {
            $department = Department::firstOrCreate(['name' => $data['department_name']]);
            $prepared['department_id'] = $department->id;
        }

        if (!empty($data['designation_name'])) {
            $designation = Designation::firstOrCreate(['name' => $data['designation_name']]);
            $prepared['designation_id'] = $designation->id;
        }

        if (!empty($data['employee_type_name'])) {
            $employeeType = EmployeeType::firstOrCreate(['name' => $data['employee_type_name']]);
            $prepared['employee_type_id'] = $employeeType->id;
        }

        if (!empty($data['manager_email'])) {
            $manager = Employee::where('email', $data['manager_email'])->first();
            if ($manager) {
                $prepared['manager_id'] = $manager->id;
            }
        }

        return $prepared;
    }

    /**
     * Get import template headers
     *
     * @return array
     */
    public function getImportTemplate(): array
    {
        return [
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
            'manager_email',
            'nationality',
            'blood_group',
            'marital_status'
        ];
    }
}