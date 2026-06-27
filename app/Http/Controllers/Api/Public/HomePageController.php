<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\CustomerReview;
use App\Models\District;
use App\Models\OfferAndPromo;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HomePageController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all active districts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $data = [];
            $data['districts'] = District::select('id', 'name', 'code')->where('status', 1)->get();
            $customerReviews = CustomerReview::where('status', 1)->orderBy('created_at', 'desc')->get();

            foreach ($customerReviews as $customerReview) {
                $customerReview->image = $customerReview->image ? asset($customerReview->image) : null;
            }
            $data['customer_reviews'] = $customerReviews;

            $offerAndPromos = OfferAndPromo::where('status', 1)->orderBy('created_at', 'desc')->get();

            foreach ($offerAndPromos as $offerAndPromo) {
                $offerAndPromo->image = $offerAndPromo->image ? asset($offerAndPromo->image) : null;
            }
            $data['offer_and_promos'] = $offerAndPromos;

            // Get all routes from the database
            $data['popular_routes'] = DB::table('transport_routes')
                ->join('districts as start', 'transport_routes.start_id', '=', 'start.id')
                ->join('districts as end', 'transport_routes.end_id', '=', 'end.id')
                ->select('transport_routes.id', 'transport_routes.start_id', 'transport_routes.end_id', 'start.name as start_name', 'end.name as end_name', 'transport_routes.distance', 'transport_routes.duration', 'transport_routes.is_popular', 'transport_routes.popular_position', 'transport_routes.status', 'transport_routes.created_at', 'transport_routes.updated_at')
                ->where('transport_routes.is_popular', true)
                ->orderBy('transport_routes.popular_position', 'asc')
                ->get();

            return $this->successResponse($data, 'Home Page Data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve Home Page Data: '.$e->getMessage(), 500);
        }

    }
}
