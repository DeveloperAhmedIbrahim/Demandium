<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\PromotionManagement\Entities\Banner;
use Modules\PromotionManagement\Entities\Campaign;
use Modules\PromotionManagement\Entities\DiscountType;

class CampaignController extends Controller
{
    private Campaign $campaign;
    private DiscountType $discountType;

    public function __construct(Campaign $campaign, DiscountType $discountType)
    {
        $this->campaign = $campaign;
        $this->discountType = $discountType;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $campaigns = $this->campaign
            ->with(['discount', 'discount.category_types.category', 'discount.service_types.service.category', 'discount.service_types.service.subCategory'])
            ->where(function ($query) {
                $query->whereDoesntHave('discount.category_types')
                    ->orWhereHas('discount.category_types', function ($query) {
                        $query->whereHas('category', function ($query) {
                            $query->where('is_active', '!=', 0);
                        });
                    });
            })
            ->where(function ($query) {
                $query->whereDoesntHave('discount.service_types')
                    ->orWhereHas('discount.service_types', function ($query) {
                        $query->whereHas('service.category', function ($query) {
                            $query->where('is_active', '!=', 0);
                        })
                            ->orWhereHas('service.subCategory', function ($query) {
                                $query->where('is_active', '!=', 0);
                            });
                    });
            })
            ->ofStatus(1)
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $campaigns), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function campaignItems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|uuid',
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $campaign = $this->campaign
            ->whereHas('discount', function ($query) {
                $query->where('promotion_type', 'campaign')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('is_active', 1);
            })
            ->whereHas('discount.discount_types', function ($query) {
                $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
            })
            ->where('id', $request['campaign_id'])
            ->first();

        if (isset($campaign)) {
            $items = $this->discountType->where(['discount_id' => $campaign->discount->id])
                ->with(['category' => function ($query) {
                    $query->where('is_active', 1);
                }])
                ->with(['service' => function ($query) {
                    $query->where('is_active', 1)->with(['variations']);
                }])
                ->with(['discount'])
                ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');
            return response()->json(response_formatter(DEFAULT_200, $items), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }
}
