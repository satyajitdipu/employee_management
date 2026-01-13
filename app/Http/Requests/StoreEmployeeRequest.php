<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date|before_or_equal:today',
            'salary' => 'required|numeric|min:0',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'employee_type_id' => 'required|exists:employee_types,id',
            'status' => 'required|in:active,inactive,terminated',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'hire_date.required' => 'Hire date is required.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'salary.required' => 'Salary is required.',
            'salary.numeric' => 'Salary must be a valid number.',
            'salary.min' => 'Salary cannot be negative.',
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'Selected department does not exist.',
            'designation_id.required' => 'Designation is required.',
            'designation_id.exists' => 'Selected designation does not exist.',
            'employee_type_id.required' => 'Employee type is required.',
            'employee_type_id.exists' => 'Selected employee type does not exist.',
            'status.required' => 'Employee status is required.',
            'status.in' => 'Invalid employee status selected.',
        ];
    }
}