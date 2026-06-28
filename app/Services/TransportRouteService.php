<?php

namespace App\Services;

use App\Models\Station;
use App\Models\TransportRoute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransportRouteService
{
    /**
     * Get a paginated listing of transport routes with optional search by district name.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page = $attributes['page'] ?? 1;
        $search = $attributes['search'] ?? null;

        $query = TransportRoute::query()->with(['startDistrict', 'endDistrict']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('startDistrict', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('endDistrict', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all popular transport routes ordered by popular position.
     *
     * @return Collection
     */
    public function allPopular(): Collection
    {
        return TransportRoute::query()
            ->with(['startDistrict', 'endDistrict'])
            ->where('is_popular', true)
            ->orderBy('popular_position')
            ->get();
    }

    /**
     * Create a new transport route and its stations inside a database transaction.
     *
     * @param  array  $attributes
     * @return TransportRoute
     */
    public function store(array $attributes): TransportRoute
    {
        return DB::transaction(function () use ($attributes) {
            $transportRoute = TransportRoute::create([
                'start_id' => $attributes['start_id'],
                'end_id' => $attributes['end_id'],
                'distance' => $attributes['distance'],
                'duration' => ($attributes['duration_hours'] * 60) + $attributes['duration_minutes'],
                'is_popular' => $attributes['is_popular'] ?? false,
                'status' => 1,
                'created_by' => auth()->id(),
            ]);

            foreach ($attributes['station_ids'] ?? [] as $districtId) {
                Station::create([
                    'transport_route_id' => $transportRoute->id,
                    'district_id' => $districtId,
                    'status' => 1,
                    'created_by' => auth()->id(),
                ]);
            }

            return $transportRoute->load([
                'startDistrict',
                'endDistrict',
                'stations' => fn ($q) => $q->where('status', 1)->with('district'),
            ]);
        });
    }

    /**
     * Find a transport route by ID with relationships or throw ModelNotFoundException.
     *
     * @param  int  $id
     * @return TransportRoute
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): TransportRoute
    {
        $route = TransportRoute::with([
            'startDistrict',
            'endDistrict',
            'stations' => fn ($q) => $q->where('status', 1)->with('district'),
        ])->find($id);

        if (! $route) {
            throw new ModelNotFoundException("Transport route with ID {$id} not found.");
        }

        return $route;
    }

    /**
     * Update the specified transport route and replace its stations inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return TransportRoute
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): TransportRoute
    {
        return DB::transaction(function () use ($id, $attributes) {
            $transportRoute = $this->findById($id);

            $transportRouteData = [
                'start_id' => $attributes['start_id'],
                'end_id' => $attributes['end_id'],
                'distance' => $attributes['distance'],
                'duration' => ($attributes['duration_hours'] * 60) + $attributes['duration_minutes'],
                'updated_by' => auth()->id(),
            ];

            if (array_key_exists('is_popular', $attributes)) {
                $transportRouteData['is_popular'] = $attributes['is_popular'];
            }

            $transportRoute->update($transportRouteData);

            $transportRoute->stations()->delete();

            foreach ($attributes['station_ids'] ?? [] as $districtId) {
                Station::create([
                    'transport_route_id' => $transportRoute->id,
                    'district_id' => $districtId,
                    'status' => 1,
                    'created_by' => auth()->id(),
                ]);
            }

            return $transportRoute->load([
                'startDistrict',
                'endDistrict',
                'stations' => fn ($q) => $q->where('status', 1)->with('district'),
            ]);
        });
    }

    /**
     * Update popular_position for multiple transport routes inside a database transaction.
     *
     * @param  array  $popularPositions
     * @return void
     */
    public function updatePopularPositions(array $popularPositions): void
    {
        DB::transaction(function () use ($popularPositions) {
            foreach ($popularPositions as $item) {
                TransportRoute::find($item['id'])?->update(['popular_position' => $item['popular_position']]);
            }
        });
    }

    /**
     * Soft-delete the specified transport route inside a database transaction.
     *
     * @param  int  $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->findById($id)->delete();
        });
    }

    /**
     * Get all active counters associated with the specified transport route.
     *
     * @param  int  $id
     * @return \Illuminate\Support\Collection
     *
     * @throws ModelNotFoundException
     */
    public function routeWiseCounters(int $id): \Illuminate\Support\Collection
    {
        $this->findById($id);

        return DB::table('counters')
            ->leftJoin('transport_routes as route', function ($join) {
                $join->on('counters.district_id', '=', 'route.start_id')
                    ->orOn('counters.district_id', '=', 'route.end_id');
            })
            ->leftJoin('stations', function ($join) {
                $join->on('counters.district_id', '=', 'stations.district_id');
            })
            ->where('counters.status', 1)
            ->where(function ($query) use ($id) {
                $query->where('route.id', $id)
                    ->orWhere('stations.transport_route_id', $id);
            })
            ->select('counters.*')
            ->distinct()
            ->get();
    }
}
