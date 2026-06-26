<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\District;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $dhaka = District::where('name', 'Dhaka')->first()->id;
        $ctg = District::where('name', 'Chittagong')->first()->id;

        $driver = Designation::where('name', 'Driver')->first()->id;
        $supervisor = Designation::where('name', 'Supervisor')->first()->id;
        $helper = Designation::where('name', 'Helper')->first()->id;
        $mechanic = Designation::where('name', 'Mechanic')->first()->id;
        $manager = Designation::where('name', 'Manager')->first()->id;

        $employees = [
            [
                'name' => 'Karim Uddin',
                'contact_no' => '01711100001',
                'date_of_birth' => '1985-03-15',
                'job_type' => 'Full-time',
                'joining_date' => '2018-01-10',
                'designation_id' => $driver,
                'district_id' => $dhaka,
                'license_category' => 'Heavy',
                'license_no' => 'DL-2018-001',
                'blood_group' => 'B+',
                'religion' => 'Islam',
                'marital_status' => 'Married',
            ],
            [
                'name' => 'Rahim Mia',
                'contact_no' => '01711100002',
                'date_of_birth' => '1987-07-22',
                'job_type' => 'Full-time',
                'joining_date' => '2019-03-15',
                'designation_id' => $driver,
                'district_id' => $ctg,
                'license_category' => 'Heavy',
                'license_no' => 'DL-2019-002',
                'blood_group' => 'O+',
                'religion' => 'Islam',
                'marital_status' => 'Married',
            ],
            [
                'name' => 'Jamal Hossain',
                'contact_no' => '01711100003',
                'date_of_birth' => '1990-11-05',
                'job_type' => 'Full-time',
                'joining_date' => '2020-06-01',
                'designation_id' => $supervisor,
                'district_id' => $dhaka,
                'blood_group' => 'A+',
                'religion' => 'Islam',
                'marital_status' => 'Single',
            ],
            [
                'name' => 'Selim Reza',
                'contact_no' => '01711100004',
                'date_of_birth' => '1992-04-18',
                'job_type' => 'Full-time',
                'joining_date' => '2021-01-20',
                'designation_id' => $supervisor,
                'district_id' => $ctg,
                'blood_group' => 'AB+',
                'religion' => 'Islam',
                'marital_status' => 'Married',
            ],
            [
                'name' => 'Babul Islam',
                'contact_no' => '01711100005',
                'date_of_birth' => '1995-09-30',
                'job_type' => 'Full-time',
                'joining_date' => '2022-03-01',
                'designation_id' => $helper,
                'district_id' => $dhaka,
                'blood_group' => 'O-',
                'religion' => 'Islam',
                'marital_status' => 'Single',
            ],
            [
                'name' => 'Milon Ahmed',
                'contact_no' => '01711100006',
                'date_of_birth' => '1996-12-12',
                'job_type' => 'Full-time',
                'joining_date' => '2022-07-15',
                'designation_id' => $helper,
                'district_id' => $ctg,
                'blood_group' => 'B-',
                'religion' => 'Islam',
                'marital_status' => 'Single',
            ],
            [
                'name' => 'Sumon Khan',
                'contact_no' => '01711100007',
                'date_of_birth' => '1988-02-25',
                'job_type' => 'Full-time',
                'joining_date' => '2017-11-10',
                'designation_id' => $mechanic,
                'district_id' => $dhaka,
                'blood_group' => 'A-',
                'religion' => 'Islam',
                'marital_status' => 'Married',
            ],
            [
                'name' => 'Faruk Hasan',
                'contact_no' => '01711100008',
                'date_of_birth' => '1983-06-08',
                'job_type' => 'Full-time',
                'joining_date' => '2015-04-01',
                'designation_id' => $manager,
                'district_id' => $dhaka,
                'blood_group' => 'AB-',
                'religion' => 'Islam',
                'marital_status' => 'Married',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create(array_merge($employee, [
                'status' => 1,
                'created_by' => 1,
            ]));
        }
    }
}
