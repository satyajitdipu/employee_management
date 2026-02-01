<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{4}'),
            'email' => fake()->unique()->safeEmail(),
            'user_id' => User::factory(),
            'manager_id' => null,
            'department_id' => Department::factory(),
            'designation_id' => Designation::factory(),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'last_name' => fake()->lastName(),
            'head_to_face_ratio' => fake()->randomFloat(2, 1.0, 2.0),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'date_of_birth' => fake()->date('Y-m-d', '-18 years'),
            'status' => fake()->randomElement(['active', 'inactive', 'terminated']),
            'birthday' => fake()->date('m-d'),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'nationality' => fake()->country(),
            'country_of_birth' => fake()->country(),
            'marital_status' => fake()->randomElement(['married', 'unmarried']),
            'employee_type_id' => EmployeeType::factory(),
            'field' => null,
            'salary' => fake()->numberBetween(30000, 150000),
            'hire_date' => fake()->date('Y-m-d', '-5 years'),
        ];
    }
}