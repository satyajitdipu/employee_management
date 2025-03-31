<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Faker\Factory;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmployeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            'active',
            'inactive',
            'terminated',
            'resigned',
            'absconded',
            'suspended',
            'retired',
            'laid_off',
            'deceased',
            'on_sabbatical'
        ];

        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        $nationalities = ['USA', 'Canada', 'UK', 'Australia', 'Germany', 'France', 'India', 'China', 'Japan', 'Brazil'];

        $countriesOfBirth = ['USA', 'Canada', 'UK', 'Australia', 'Germany', 'France', 'India', 'China', 'Japan', 'Brazil'];

        for ($i = 1; $i <= 50; $i++) {
            $faker = Factory::create();
            $gender = $faker->randomElement(['male', 'female', 'other']);
            $first_name = $faker->firstName($gender);
            $last_name = $faker->lastName();
            $work_email = $faker->email();
            $birthdate = $faker->dateTimeBetween('-60 years', '-18 years');

            Employee::create([
                'employee_code' => sprintf("NTE-%03d", $i),
                'user_id' => User::factory()->create([
                    'name' => "{$first_name} {$last_name}",
                    'email' => $work_email,
                ])->assignRole('employee')->id,
                'manager_id' => rand(0, 10) > 6 ? Employee::inRandomOrder()->pluck('id')->first() : null,
                'department_id' => Department::inRandomOrder()->pluck('id')->first(),
                'designation_id' => Designation::inRandomOrder()->pluck('id')->first(),
                'first_name' => $first_name,
                'middle_name' => "",
                'last_name' => $last_name,
                'gender' => $gender, // ['male', 'female', 'other']
                'date_of_birth' => $faker->dateTimeBetween('1970-01-01', '2004-01-01'),
                'status' => $faker->randomElement($statuses),
                'birthday' => $birthdate,
                'blood_group' => $faker->randomElement($bloodGroups),
                'nationality' => $faker->randomElement($nationalities),
                'country_of_birth' => $faker->randomElement($countriesOfBirth),
                'marital_status' => $faker->randomElement(['married', 'unmarried']),
            ]);
        }
    }
}
