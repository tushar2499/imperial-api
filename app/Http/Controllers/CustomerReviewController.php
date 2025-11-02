<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerReviewController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all customer reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage    = min((int) $request->get('per_page', 15), 1000); // Cap at 1000
            $page       = max((int) $request->get('page', 1), 1); // Minimum page 1
            $searchTerm = $request->get('search');

            $query = CustomerReview::when($searchTerm, function ($q, $searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('comment', 'like', "%{$searchTerm}%")
                    ->orWhere('date', 'like', "%{$searchTerm}%");
            })
                ->orderBy('created_at', 'desc');

            $customerReviews = $query->paginate($perPage, ['*'], 'page', $page);

            foreach ($customerReviews as $customerReview) {
                $customerReview->image = $customerReview->image ? asset($customerReview->image) : null;
            }

            return $this->successResponse($customerReviews, 'Customer reviews retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve customer reviews: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created customer review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'date'    => 'required|date|date_format:Y-m-d',
            'comment' => 'required|string',
            'rating'  => 'nullable|integer|min:1|max:5',
            'image'   => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $image_path = file_uploaded($request->file('image'), 'offer-and-promos');

        try {
            DB::beginTransaction();

            $data = [
                'name'       => $request->input('name'),
                'date'       => $request->input('date'),
                'comment'    => $request->input('comment'),
                'rating'     => $request->input('rating') ?? null,
                'image'      => $image_path,
                'created_by' => auth()->user()->id,
            ];
            $customerReview = CustomerReview::create($data);

            DB::commit();

            $customerReview->image = $customerReview->image ? asset($customerReview->image) : null;

            return $this->successResponse(['data' => $customerReview], 'Customer review created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create customer review: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified customer review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $customerReview        = CustomerReview::where('id', $id)->firstOrFail();
            $customerReview->image = $customerReview->image ? asset($customerReview->image) : null;

            return $this->successResponse($customerReview, 'Customer review retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve customer review: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified customer review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $customerReview = CustomerReview::where('id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'date'    => 'required|date|date_format:Y-m-d',
            'comment' => 'required|string',
            'rating'  => 'nullable|integer|min:1|max:5',
            'image'   => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $image_path = $customerReview->image;

            if ($request->hasFile('image')) {
                $image_path = file_uploaded($request->file('image'), 'offer-and-promos');

                if ($image_path) {
                    delete_uploaded_file($customerReview->image);
                }

            }

            $data = [
                'name'       => $request->input('name'),
                'date'       => $request->input('date'),
                'comment'    => $request->input('comment'),
                'rating'     => $request->input('rating') ?? null,
                'image'      => $image_path,
                'created_by' => auth()->user()->id,
            ];
            $customerReview->update($data);
            $customerReview->refresh();
            $customerReview->image = $customerReview->image ? asset($customerReview->image) : null;

            DB::commit();

            return $this->successResponse($customerReview, 'Customer review updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update customer review: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified customer review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $customerReview = CustomerReview::where('id', $id)->firstOrFail();

            delete_uploaded_file($customerReview->image);

            $customerReview->delete();

            DB::commit();

            return $this->successResponse(null, 'Customer review deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete customer review: ' . $e->getMessage(), 500);
        }

    }

}
