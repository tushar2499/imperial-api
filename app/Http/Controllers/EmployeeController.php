<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Employee\EmployeeDestroyRequest;
use App\Http\Requests\Api\Employee\EmployeeIndexRequest;
use App\Http\Requests\Api\Employee\EmployeeShowRequest;
use App\Http\Requests\Api\Employee\EmployeeStoreRequest;
use App\Http\Requests\Api\Employee\EmployeeUpdateRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EmployeeService $employeeService) {}

    /**
     * Display a paginated listing of all employees.
     *
     * @param  EmployeeIndexRequest  $request
     * @return JsonResponse
     */
    public function index(EmployeeIndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $employees = $this->employeeService->pagination($attributes);

        return $this->successResponse(
            EmployeeResource::collection($employees)->resolve(),
            'Employees retrieved successfully',
            $this->preparePaginator($employees)
        );
    }

    /**
     * Store a newly created employee.
     *
     * @param  EmployeeStoreRequest  $request
     * @return JsonResponse
     */
    public function store(EmployeeStoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $employee = $this->employeeService->store($attributes);

        return $this->createdResponse(new EmployeeResource($employee), 'Employee created successfully');
    }

    /**
     * Display the specified employee with academics and experiences.
     *
     * @param  EmployeeShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(EmployeeShowRequest $request, int $id): JsonResponse
    {
        $employee = $this->employeeService->findById($id);

        return $this->successResponse(new EmployeeResource($employee), 'Employee retrieved successfully');
    }

    /**
     * Update the specified employee.
     *
     * @param  EmployeeUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(EmployeeUpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $employee = $this->employeeService->update($id, $attributes);

        return $this->successResponse(new EmployeeResource($employee), 'Employee updated successfully');
    }

    /**
     * Remove the specified employee.
     *
     * @param  EmployeeDestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(EmployeeDestroyRequest $request, int $id): JsonResponse
    {
        $this->employeeService->destroy($id);

        return $this->successResponse([], 'Employee deleted successfully');
    }
}
