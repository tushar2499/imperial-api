<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\District;
use Illuminate\Database\Seeder;

class CounterSeeder extends Seeder
{
    public function run(): void
    {
        $counters = [
            [
                'district' => 'Dhaka',
                'type' => 3,
                'address' => 'Sayedabad Bus Terminal, Dhaka-1204',
                'phone' => '02-7550123',
                'mobile' => '01711000001',
                'email' => 'dhaka@imperial.com',
            ],
            [
                'district' => 'Chittagong',
                'type' => 1,
                'address' => 'Oxygen More Bus Stand, Chittagong',
                'phone' => '031-621234',
                'mobile' => '01711000002',
                'email' => 'chittagong@imperial.com',
            ],
            [
                'district' => 'Sylhet',
                'type' => 1,
                'address' => 'Kadamtali Bus Terminal, Sylhet-3100',
                'phone' => '0821-712345',
                'mobile' => '01711000003',
                'email' => 'sylhet@imperial.com',
            ],
            [
                'district' => 'Rajshahi',
                'type' => 1,
                'address' => 'Bimanbandor Bus Stand, Rajshahi-6000',
                'phone' => '0721-775123',
                'mobile' => '01711000004',
                'email' => 'rajshahi@imperial.com',
            ],
            [
                'district' => 'Khulna',
                'type' => 1,
                'address' => 'Sonadanga Bus Terminal, Khulna-9100',
                'phone' => '041-721234',
                'mobile' => '01711000005',
                'email' => 'khulna@imperial.com',
            ],
            [
                'district' => 'Barisal',
                'type' => 1,
                'address' => 'Nathullabad Bus Stand, Barisal-8200',
                'phone' => '0431-621234',
                'mobile' => '01711000006',
                'email' => 'barisal@imperial.com',
            ],
            [
                'district' => 'Mymensingh',
                'type' => 2,
                'address' => 'Maskanda Bus Terminal, Mymensingh-2200',
                'phone' => '091-665123',
                'mobile' => '01711000007',
                'email' => 'mymensingh@imperial.com',
            ],
            [
                'district' => 'Rangpur',
                'type' => 2,
                'address' => 'Modern Bus Terminal, Rangpur-5400',
                'phone' => '0521-632123',
                'mobile' => '01711000008',
                'email' => 'rangpur@imperial.com',
            ],
        ];

        foreach ($counters as $data) {
            $district = District::where('name', $data['district'])->first();

            Counter::create([
                'type' => $data['type'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'mobile' => $data['mobile'],
                'email' => $data['email'],
                'country' => 'Bangladesh',
                'district_id' => $district->id,
                'booking_allowed_status' => 1,
                'booking_allowed_class' => 3,
                'no_of_boarding_allowed' => 5,
                'sms_status' => 1,
                'status' => 1,
                'created_by' => 1,
            ]);
        }
    }
}
