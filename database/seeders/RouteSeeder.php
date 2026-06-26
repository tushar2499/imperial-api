<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $d = District::pluck('id', 'name');

        $routes = [
            [
                'start_id' => $d['Dhaka'],
                'end_id' => $d['Chittagong'],
                'distance' => 244,
                'duration' => '360',
                'is_popular' => 1,
                'popular_position' => 1,
            ],
            [
                'start_id' => $d['Chittagong'],
                'end_id' => $d['Dhaka'],
                'distance' => 244,
                'duration' => '360',
                'is_popular' => 1,
                'popular_position' => 2,
            ],
            [
                'start_id' => $d['Dhaka'],
                'end_id' => $d['Sylhet'],
                'distance' => 240,
                'duration' => '330',
                'is_popular' => 1,
                'popular_position' => 3,
            ],
            [
                'start_id' => $d['Sylhet'],
                'end_id' => $d['Dhaka'],
                'distance' => 240,
                'duration' => '330',
                'is_popular' => 0,
                'popular_position' => null,
            ],
            [
                'start_id' => $d['Dhaka'],
                'end_id' => $d['Rajshahi'],
                'distance' => 262,
                'duration' => '360',
                'is_popular' => 0,
                'popular_position' => null,
            ],
            [
                'start_id' => $d['Rajshahi'],
                'end_id' => $d['Dhaka'],
                'distance' => 262,
                'duration' => '360',
                'is_popular' => 0,
                'popular_position' => null,
            ],
            [
                'start_id' => $d['Dhaka'],
                'end_id' => $d['Khulna'],
                'distance' => 286,
                'duration' => '420',
                'is_popular' => 0,
                'popular_position' => null,
            ],
            [
                'start_id' => $d['Khulna'],
                'end_id' => $d['Dhaka'],
                'distance' => 286,
                'duration' => '420',
                'is_popular' => 0,
                'popular_position' => null,
            ],
            [
                'start_id' => $d['Dhaka'],
                'end_id' => $d['Barisal'],
                'distance' => 180,
                'duration' => '300',
                'is_popular' => 0,
                'popular_position' => null,
            ],
            [
                'start_id' => $d['Barisal'],
                'end_id' => $d['Dhaka'],
                'distance' => 180,
                'duration' => '300',
                'is_popular' => 0,
                'popular_position' => null,
            ],
            [
                'start_id' => $d['Chittagong'],
                'end_id' => $d['Sylhet'],
                'distance' => 335,
                'duration' => '480',
                'is_popular' => 0,
                'popular_position' => null,
            ],
            [
                'start_id' => $d['Sylhet'],
                'end_id' => $d['Chittagong'],
                'distance' => 335,
                'duration' => '480',
                'is_popular' => 0,
                'popular_position' => null,
            ],
        ];

        foreach ($routes as $route) {
            Route::create(array_merge($route, [
                'status' => 1,
                'created_by' => 1,
            ]));
        }
    }
}
