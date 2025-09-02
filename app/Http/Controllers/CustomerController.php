<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of customers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $customers = Customer::select([
                'id',
                'mobile',
                'name',
                'gender',
                'age',
                'address',
                'passport_no',
                'nid',
                'nationality',
                'email',
                'total_trips',
                'total_tickets',
                'total_cancelled_tickets',
            ])->get();

            return $this->successResponse($customers, 'Customers retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve customers: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display a listing of all active customers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allActive()
    {
        try {
            $customers = Customer::select([
                'id',
                'mobile',
                'name',
                'gender',
                'age',
                'address',
                'passport_no',
                'nid',
                'nationality',
                'email',
                'total_trips',
                'total_tickets',
                'total_cancelled_tickets',
            ])->where('status', 1)->get();

            return $this->successResponse($customers, 'All Active Customers retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve customers: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile'      => 'required|string|max:255|unique:customers,mobile',
            'name'        => 'required|string|max:255',
            'gender'      => 'nullable|string|max:255',
            'age'         => 'nullable|numeric',
            'address'     => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'email'       => 'nullable|string|max:255',
            'status'      => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $customer = Customer::create([
                'mobile'      => $request->input('mobile'),
                'name'        => $request->input('name'),
                'gender'      => $request->input('gender'),
                'age'         => $request->input('age'),
                'address'     => $request->input('address'),
                'passport_no' => $request->input('passport_no'),
                'nationality' => $request->input('nationality'),
                'email'       => $request->input('email'),
                'status'      => $request->input('status', 1),
            ]);

            DB::commit();

            $customer = $customer->makeHidden(['status', 'created_at', 'updated_at']);

            return $this->successResponse(['data' => $customer], 'Customer created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create customer: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::select([
                'id',
                'mobile',
                'name',
                'gender',
                'age',
                'address',
                'passport_no',
                'nid',
                'nationality',
                'email',
                'total_trips',
                'total_tickets',
                'total_cancelled_tickets',
            ])
                ->where('id', $id)
                ->first();

            if (!$customer) {
                return $this->errorResponse('Customer not found', 404);
            }

            DB::commit();

            return $this->successResponse($customer, 'Customer retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve customer: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'mobile'      => 'required|string|max:255|unique:customers,mobile,' . $id,
            'name'        => 'required|string|max:255',
            'gender'      => 'nullable|string|max:255',
            'age'         => 'nullable|numeric',
            'address'     => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'email'       => 'nullable|string|max:255',
            'status'      => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $customer = Customer::find($id);

            if (!$customer) {
                return $this->errorResponse('Customer not found', 404);
            }

            $customer->update([
                'mobile'      => $request->input('mobile'),
                'name'        => $request->input('name'),
                'gender'      => $request->input('gender'),
                'age'         => $request->input('age'),
                'address'     => $request->input('address'),
                'passport_no' => $request->input('passport_no'),
                'nationality' => $request->input('nationality'),
                'email'       => $request->input('email'),
                'status'      => $request->input('status', 1),
            ]);

            $customer->refresh();

            DB::commit();

            $customer = $customer->makeHidden(['status', 'created_at', 'updated_at']);

            return $this->successResponse($customer, 'Customer updated successfully', '200');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update customer: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::find($id);

            if (!$customer) {
                return $this->errorResponse('Customer not found', 404);
            }

            $customer->delete();

            DB::commit();

            return $this->successResponse(null, 'Customer deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete customer: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified customer.
     *
     * @param  string  $mobile
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerByMobile($mobile)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::select([
                'id',
                'mobile',
                'name',
                'gender',
                'age',
                'address',
                'passport_no',
                'nid',
                'nationality',
                'email',
                'total_trips',
                'total_tickets',
                'total_cancelled_tickets',
            ])
                ->where('mobile', $mobile)
                ->first();

            if (!$customer) {
                return $this->errorResponse('Customer not found', 404);
            }

            DB::commit();


            return $this->successResponse($customer, 'Customer retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve customer: ' . $e->getMessage(), 500);
        }

    }

}
