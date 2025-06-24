<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse; // Make sure the trait is imported

class DistrictController extends Controller
{
    use ApiResponse;  // Use the ApiResponse trait

    /**
     * Display a listing of districts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Get all districts from the database
            $districts = DB::table('districts')->select('id', 'name', 'code')->get();

            // Commit transaction
            DB::commit();

            return $this->successResponse($districts, 'Districts retrieved successfully');
        } catch (\Exception $e) {
            // Rollback transaction if anything goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to retrieve districts: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created district.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Insert district into database
            $districtId = DB::table('districts')->insertGetId([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'status' => $request->input('status', 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $district = DB::table('districts')
            ->select('id', 'name', 'code', 'status', 'created_at', 'updated_at')
            ->where('id', $districtId)
            ->first();

            // Commit transaction
            DB::commit();

            return $this->successResponse(['data' => $district], 'District created successfully', 201);
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to create district: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified district.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Get the district by id
            $district = DB::table('districts')
                        ->select('id', 'name', 'code')
                        ->where('id', $id)
                        ->first();  // Use first() for single result

            if (!$district) {
                return $this->errorResponse('District not found', 404);
            }

            // Commit transaction
            DB::commit();

            return $this->successResponse($district, 'District retrieved successfully');
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to retrieve district: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified district.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Update the district
            $updated = DB::table('districts')
                ->where('id', $id)
                ->update([
                    'name' => $request->input('name'),
                    'code' => $request->input('code'),
                    'updated_at' => now(),
                ]);
            $district = DB::table('districts')
            ->select('id', 'name', 'code', 'status', 'created_at', 'updated_at')
            ->where('id', $id)
            ->first();

            if ($updated === 0) {
                return $this->errorResponse('District not found', 404);
            }

            // Commit transaction
            DB::commit();

            return $this->successResponse($district, 'District updated successfully','200');
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to update district: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified district.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Begin DB transaction
            DB::beginTransaction();

            // Soft delete district by ID
            $deleted = DB::table('districts')->where('id', $id)->delete();

            if ($deleted === 0) {
                return $this->errorResponse('District not found', 404);
            }

            // Commit transaction
            DB::commit();

            return $this->successResponse(null, 'District deleted successfully');
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollback();
            return $this->errorResponse('Failed to delete district: ' . $e->getMessage(), 500);
        }
    }
}
