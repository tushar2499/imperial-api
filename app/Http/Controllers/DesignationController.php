<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all designations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            DB::beginTransaction();

            $designations = Designation::get();

            DB::commit();

            return $this->successResponse($designations, 'designations retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve designations: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created designation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:designations,name',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $designation = Designation::create([
                'name'       => $request->input('name'),
                'created_by' => auth()->user()->id,
            ]);

            DB::commit();

            return $this->successResponse(['data' => $designation], 'Designation created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create designation: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified designation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $designation = Designation::where('id', $id)->firstOrFail();

            return $this->successResponse($designation, 'Designation retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve designation: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified designation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:designations,name,' . $id . ',id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $designation = Designation::where('id', $id)->firstOrFail();

            $designation->update([
                'name'       => $request->input('name'),
                'updated_by' => auth()->user()->id,
                'updated_at' => now(),
            ]);

            $designation = $designation->refresh();

            DB::commit();

            return $this->successResponse($designation, 'Designation updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update designation: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified designation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $deleted = Designation::where('id', $id)->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Designation not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Designation deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete designation: ' . $e->getMessage(), 500);
        }

    }

}
