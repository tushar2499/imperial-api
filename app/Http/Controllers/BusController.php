<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Ensure the ApiResponse trait is imported

class BusController extends Controller
{
    use ApiResponse;

// Use the ApiResponse trait

    /**
     * Display a listing of all buses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            DB::beginTransaction();

            // Get all buses from the database
            $buses = DB::table('buses')->get();

            DB::commit();

            return $this->successResponse($buses, 'buses retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve buses: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created coach.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'registration_number'  => 'required|string|max:255',
            'manufacturer_company' => 'required|string|max:255',
            'model_year'           => 'required|integer',
            'chasis_no'            => 'required|string|max:255',
            'engine_number'        => 'required|string|max:255',
            'country_of_origin'    => 'nullable|string|max:255',
            'lc_code_number'       => 'nullable|string|max:255',
            'delivery_to_dipo'     => 'nullable|string|max:255',
            'delivery_date'        => 'nullable|date',
            'color'                => 'nullable|string|max:255',
            'financed_by'          => 'nullable|string|max:255',
            'tennure_of_the_terms' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Insert coach into the database
            $busId = DB::table('buses')->insertGetId([
                'registration_number'  => $request->input('registration_number'),
                'manufacturer_company' => $request->input('manufacturer_company'),
                'model_year'           => $request->input('model_year'),
                'chasis_no'            => $request->input('chasis_no'),
                'engine_number'        => $request->input('engine_number'),
                'country_of_origin'    => $request->input('country_of_origin'),
                'lc_code_number'       => $request->input('lc_code_number'),
                'delivery_to_dipo'     => $request->input('delivery_to_dipo'),
                'delivery_date'        => $request->input('delivery_date'),
                'color'                => $request->input('color'),
                'financed_by'          => $request->input('financed_by'),
                'tennure_of_the_terms' => $request->input('tennure_of_the_terms'),
                'created_by'           => auth()->user()->id,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            $bus = DB::table('buses')->where('id', $busId)->first();

            DB::commit();

            return $this->successResponse(['data' => $bus], 'Bus created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create coach: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified coach.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            // Get the coach by id
            $bus = DB::table('buses')->where('id', $id)->first();

            if (!$bus) {
                return $this->errorResponse('Bus not found', 404);
            }

            DB::commit();

            return $this->successResponse($bus, 'Bus retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve coach: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified coach.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'registration_number'  => 'required|string|max:255',
            'manufacturer_company' => 'required|string|max:255',
            'model_year'           => 'required|integer',
            'chasis_no'            => 'required|string|max:255',
            'engine_number'        => 'required|string|max:255',
            'country_of_origin'    => 'nullable|string|max:255',
            'lc_code_number'       => 'nullable|string|max:255',
            'delivery_to_dipo'     => 'nullable|string|max:255',
            'delivery_date'        => 'nullable|date',
            'color'                => 'nullable|string|max:255',
            'financed_by'          => 'nullable|string|max:255',
            'tennure_of_the_terms' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update coach details
            $updated = DB::table('buses')->where('id', $id)->update([
                'registration_number'  => $request->input('registration_number'),
                'manufacturer_company' => $request->input('manufacturer_company'),
                'model_year'           => $request->input('model_year'),
                'chasis_no'            => $request->input('chasis_no'),
                'engine_number'        => $request->input('engine_number'),
                'country_of_origin'    => $request->input('country_of_origin'),
                'lc_code_number'       => $request->input('lc_code_number'),
                'delivery_to_dipo'     => $request->input('delivery_to_dipo'),
                'delivery_date'        => $request->input('delivery_date'),
                'color'                => $request->input('color'),
                'financed_by'          => $request->input('financed_by'),
                'tennure_of_the_terms' => $request->input('tennure_of_the_terms'),
                'updated_by'           => auth()->user()->id,
                'updated_at'           => now(),
            ]);

            if ($updated === 0) {
                return $this->errorResponse('Bus not found', 404);
            }

            $bus = DB::table('buses')->where('id', $id)->first();

            DB::commit();

            return $this->successResponse($bus, 'Bus updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update coach: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified coach.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Soft delete the coach
            $deleted = DB::table('buses')->where('id', $id)->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Bus not found', 404);
            }

            DB::commit();

            return $this->successResponse(null, 'Bus deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete coach: ' . $e->getMessage(), 500);
        }

    }

}
