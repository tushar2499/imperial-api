<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Coach;
use App\Models\CoachConfiguration;
use App\Models\District;
use App\Models\Schedule;
use App\Models\SeatPlan;
use App\Models\TransportRoute;
use Illuminate\Database\Seeder;

class CoachConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $d = District::pluck('id', 'name');

        $routeId = function (string $from, string $to) use ($d): int {
            return TransportRoute::where('start_id', $d[$from])
                ->where('end_id', $d[$to])
                ->firstOrFail()
                ->id;
        };

        $scheduleId = fn (string $name): int => Schedule::where('name', $name)->firstOrFail()->id;
        $planA = SeatPlan::where('name', 'Standard 2+2 Single Deck')->firstOrFail()->id;
        $planB = SeatPlan::where('name', 'Business 2+1 Single Deck')->firstOrFail()->id;
        $coaches = Coach::orderBy('id')->pluck('id')->toArray();
        $buses = Bus::orderBy('id')->pluck('id')->toArray();

        $configs = [
            // Dhaka → Chittagong: Morning (Economy, Non-AC) and Night (Business, AC)
            [
                'coach_id' => $coaches[0],
                'schedule_id' => $scheduleId('06:00 AM'),
                'bus_id' => $buses[0],
                'seat_plan_id' => $planA,
                'route_id' => $routeId('Dhaka', 'Chittagong'),
                'coach_type' => CoachConfiguration::COACH_TYPE_NON_AC,
            ],
            [
                'coach_id' => $coaches[3],
                'schedule_id' => $scheduleId('09:00 PM'),
                'bus_id' => $buses[1],
                'seat_plan_id' => $planB,
                'route_id' => $routeId('Dhaka', 'Chittagong'),
                'coach_type' => CoachConfiguration::COACH_TYPE_AC,
            ],
            // Chittagong → Dhaka: Evening (Economy, Non-AC)
            [
                'coach_id' => $coaches[1],
                'schedule_id' => $scheduleId('06:00 PM'),
                'bus_id' => $buses[2],
                'seat_plan_id' => $planA,
                'route_id' => $routeId('Chittagong', 'Dhaka'),
                'coach_type' => CoachConfiguration::COACH_TYPE_NON_AC,
            ],
            // Dhaka → Sylhet: Morning (Economy, Non-AC) and Night (Business, AC)
            [
                'coach_id' => $coaches[2],
                'schedule_id' => $scheduleId('06:00 AM'),
                'bus_id' => $buses[3],
                'seat_plan_id' => $planA,
                'route_id' => $routeId('Dhaka', 'Sylhet'),
                'coach_type' => CoachConfiguration::COACH_TYPE_NON_AC,
            ],
            [
                'coach_id' => $coaches[4],
                'schedule_id' => $scheduleId('09:00 PM'),
                'bus_id' => $buses[4],
                'seat_plan_id' => $planB,
                'route_id' => $routeId('Dhaka', 'Sylhet'),
                'coach_type' => CoachConfiguration::COACH_TYPE_AC,
            ],
            // Dhaka → Rajshahi: Afternoon (Economy, Non-AC)
            [
                'coach_id' => $coaches[0],
                'schedule_id' => $scheduleId('12:00 PM'),
                'bus_id' => $buses[0],
                'seat_plan_id' => $planA,
                'route_id' => $routeId('Dhaka', 'Rajshahi'),
                'coach_type' => CoachConfiguration::COACH_TYPE_NON_AC,
            ],
            // Dhaka → Khulna: Morning (Economy, Non-AC)
            [
                'coach_id' => $coaches[1],
                'schedule_id' => $scheduleId('06:00 AM'),
                'bus_id' => $buses[1],
                'seat_plan_id' => $planA,
                'route_id' => $routeId('Dhaka', 'Khulna'),
                'coach_type' => CoachConfiguration::COACH_TYPE_NON_AC,
            ],
        ];

        foreach ($configs as $config) {
            CoachConfiguration::create(array_merge($config, [
                'status' => CoachConfiguration::STATUS_ACTIVE,
                'created_by' => 1,
            ]));
        }
    }
}
