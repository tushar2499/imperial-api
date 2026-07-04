<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Customer\CustomerDestroyRequest;
use App\Http\Requests\Api\Customer\CustomerIndexRequest;
use App\Http\Requests\Api\Customer\CustomerShowRequest;
use App\Http\Requests\Api\Customer\CustomerStoreRequest;
use App\Http\Requests\Api\Customer\CustomerUpdateByMobileRequest;
use App\Http\Requests\Api\Customer\CustomerUpdateRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CustomerService $customerService) {}

    /**
     * Display a paginated listing of all customers.
     */
    public function index(CustomerIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $customers = $this->customerService->pagination($attributes);

        return $this->successResponse(
            CustomerResource::collection($customers)->resolve(),
            'Customers retrieved successfully',
            $this->preparePaginator($customers)
        );
    }

    /**
     * Display all active customers without pagination.
     */
    public function allActive(): JsonResponse
    {
        $customers = $this->customerService->allActive();

        return $this->successResponse(
            CustomerResource::collection($customers)->resolve(),
            'All active customers retrieved successfully'
        );
    }

    /**
     * Store a newly created customer.
     */
    public function store(CustomerStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $customer = $this->customerService->store($attributes);

        return $this->createdResponse(new CustomerResource($customer), 'Customer created successfully');
    }

    /**
     * Display the specified customer.
     */
    public function show(CustomerShowRequest $request, int $id): JsonResponse
    {
        $customer = $this->customerService->findById($id);

        return $this->successResponse(new CustomerResource($customer), 'Customer retrieved successfully');
    }

    /**
     * Display the specified customer by mobile number.
     */
    public function customerByMobile(string $mobile): JsonResponse
    {
        $customer = $this->customerService->findByMobile($mobile);

        return $this->successResponse(new CustomerResource($customer), 'Customer retrieved successfully');
    }

    /**
     * Update the specified customer.
     */
    public function update(CustomerUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $customer = $this->customerService->update($id, $attributes);

        return $this->successResponse(new CustomerResource($customer), 'Customer updated successfully');
    }

    /**
     * Update the specified customer by mobile number.
     */
    public function updateByMobile(CustomerUpdateByMobileRequest $request, string $mobile): JsonResponse
    {
        $attributes = $request->validated();
        $customer = $this->customerService->updateByMobile($mobile, $attributes);

        return $this->successResponse(new CustomerResource($customer), 'Customer updated successfully');
    }

    /**
     * Set the specified customer to active.
     */
    public function active(int $id): JsonResponse
    {
        $customer = $this->customerService->activeById($id);

        return $this->successResponse(new CustomerResource($customer), 'Customer activated successfully');
    }

    /**
     * Set the specified customer to inactive.
     */
    public function inactive(int $id): JsonResponse
    {
        $customer = $this->customerService->inactiveById($id);

        return $this->successResponse(new CustomerResource($customer), 'Customer deactivated successfully');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(CustomerDestroyRequest $request, int $id): JsonResponse
    {
        $this->customerService->destroy($id);

        return $this->successResponse([], 'Customer deleted successfully');
    }
}
