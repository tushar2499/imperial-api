<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all faqs.
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

            $query = Faq::when($searchTerm, function ($q, $searchTerm) {
                $q->where('question', 'like', "%{$searchTerm}%")
                    ->orWhere('answer', 'like', "%{$searchTerm}%");
            })
                ->orderBy('created_at', 'desc');

            $faqs = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->successResponse($faqs, 'Faqs retrieved successfully');
        } catch (\Exception $e) {

            return $this->errorResponse('Failed to retrieve faqs: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display a listing of all active faqs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allActive(Request $request)
    {
        try {
            $faqs = Faq::where('status', 1)->orderBy('created_at', 'desc')->get();

            return $this->successResponse($faqs, 'All active faqs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve faqs: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created faq.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $data = [
                'question'   => $request->input('question'),
                'answer'     => $request->input('answer') ?? null,
                'created_by' => auth()->user()->id,
            ];
            $faq = Faq::create($data);

            DB::commit();

            return $this->successResponse($faq, 'Faq created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create faq: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified faq.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $faq = Faq::where('id', $id)->firstOrFail();

            return $this->successResponse($faq, 'Faq retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve faq: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified faq.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $faq = Faq::where('id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $image_path = $faq->image;

            if ($request->hasFile('image')) {
                $image_path = file_uploaded($request->file('image'), 'offer-and-promos');

                if ($image_path) {
                    delete_uploaded_file($faq->image);
                }

            }

            $data = [
                'question'   => $request->input('question'),
                'answer'     => $request->input('answer') ?? null,
                'updated_by' => auth()->user()->id,
            ];
            $faq->update($data);
            $faq->refresh();

            DB::commit();

            return $this->successResponse($faq, 'Faq updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update faq: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified faq.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $faq = Faq::where('id', $id)->firstOrFail();

            $faq->delete();

            DB::commit();

            return $this->successResponse(null, 'Faq deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete faq: ' . $e->getMessage(), 500);
        }

    }

}
