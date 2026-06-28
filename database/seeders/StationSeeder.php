<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Station;
use App\Models\TransportRoute;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $d = District::pluck('id', 'name');

        // route_id lookup helper: find route by start+end district ids
        $routeId = function (string $from, string $to) use ($d): int {
            return TransportRoute::where('start_id', $d[$from])
                ->where('end_id', $d[$to])
                ->first()
                ->id;
        };

        $stations = [
            // Dhaka → Chittagong stops at Comilla and Narsingdi (midpoint areas)
            ['route' => ['Dhaka', 'Chittagong'], 'district' => 'Comilla'],
            ['route' => ['Chittagong', 'Dhaka'],  'district' => 'Comilla'],

            // Dhaka → Sylhet stops at Narsingdi
            ['route' => ['Dhaka', 'Sylhet'],  'district' => 'Narsingdi'],
            ['route' => ['Sylhet', 'Dhaka'],  'district' => 'Narsingdi'],

            // Dhaka → Rajshahi (no common intermediate district seeded, skip)

            // Dhaka → Khulna (no common intermediate district seeded, skip)
        ];

        foreach ($stations as $station) {
            Station::create([
                'transport_route_id' => $routeId($station['route'][0], $station['route'][1]),
                'district_id' => $d[$station['district']],
                'status' => 'active',
                'created_by' => 1,
            ]);
        }
    }
}
