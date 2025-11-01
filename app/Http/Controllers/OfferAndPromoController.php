<?php

namespace App\Http\Controllers;

use App\Models\OfferAndPromo;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfferAndPromoController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all offer and promos.
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

            $query = OfferAndPromo::when($searchTerm, function ($q, $searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('expired_date', 'like', "%{$searchTerm}%");
            })
                ->orderBy('created_at', 'desc');

            $offerAndPromos = $query->paginate($perPage, ['*'], 'page', $page);

            foreach ($offerAndPromos as $offerAndPromo) {
                $offerAndPromo->image = $offerAndPromo->image ? asset($offerAndPromo->image) : null;
            }

            return $this->successResponse($offerAndPromos, 'Offer and promos retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve offer and promos: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Store a newly created offer and promo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'        => 'required|string|max:255',
            'expired_date' => 'nullable|date|date_format:Y-m-d',
            'description'  => 'nullable|string',
            'link'         => 'nullable|string|max:255',
            'image'        => 'required|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $image_path = file_uploaded($request->file('image'), 'offer-and-promos');

        try {
            DB::beginTransaction();

            $data = [
                'title'        => $request->input('title'),
                'expired_date' => $request->input('expired_date') ?? null,
                'description'  => $request->input('description') ?? null,
                'link'         => $request->input('link') ?? null,
                'image'        => $image_path,
                'created_by'   => auth()->user()->id,
            ];
            $offerAndPromo = OfferAndPromo::create($data);

            DB::commit();

            $offerAndPromo->image = $offerAndPromo->image ? asset($offerAndPromo->image) : null;

            return $this->successResponse(['data' => $offerAndPromo], 'Offer and promo created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to create offer and promo: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified offer and promo.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $offerAndPromo        = OfferAndPromo::where('id', $id)->firstOrFail();
            $offerAndPromo->image = $offerAndPromo->image ? asset($offerAndPromo->image) : null;

            return $this->successResponse($offerAndPromo, 'Offer and promo retrieved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to retrieve offer and promo: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Update the specified offer and promo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $offerAndPromo = OfferAndPromo::where('id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title'        => 'required|string|max:255',
            'expired_date' => 'nullable|date|date_format:Y-m-d',
            'description'  => 'nullable|string',
            'link'         => 'nullable|string|max:255',
            'image'        => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $image_path = $offerAndPromo->image;

            if ($request->hasFile('image')) {
                $image_path = file_uploaded($request->file('image'), 'offer-and-promos');

                if ($image_path) {
                    delete_uploaded_file($offerAndPromo->image);
                }

            }

            $data = [
                'title'        => $request->input('title'),
                'expired_date' => $request->input('expired_date') ?? null,
                'description'  => $request->input('description') ?? null,
                'link'         => $request->input('link') ?? null,
                'image'        => $image_path,
                'created_by'   => auth()->user()->id,
            ];
            $offerAndPromo->update($data);
            $offerAndPromo->refresh();
            $offerAndPromo->image = $offerAndPromo->image ? asset($offerAndPromo->image) : null;

            DB::commit();

            return $this->successResponse($offerAndPromo, 'Offer and promo updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to update offer and promo: ' . $e->getMessage(), 500);
        }

    }

    /**
     * Remove the specified offer and promo.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $offerAndPromo = OfferAndPromo::where('id', $id)->firstOrFail();

            delete_uploaded_file($offerAndPromo->image);

            $offerAndPromo->delete();

            DB::commit();

            return $this->successResponse(null, 'Offer and promo deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Failed to delete offer and promo: ' . $e->getMessage(), 500);
        }

    }

}
