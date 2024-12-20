<?php

namespace Modules\BidModule\Http\Controllers\APi\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BidModule\Entities\PostBid;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\CustomerModule\Traits\CustomerAddressTrait;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\User;
use function config;
use function response;
use function response_formatter;

class PostBidController extends Controller
{
    use BookingTrait, CustomerAddressTrait;

    public function __construct(
        private PostBid $post_bid,
    )
    {
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'post_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $postBids = $this->post_bid
            ->with(['provider.owner.reviews'])
            ->where('post_id', $request['post_id'])
            ->where('status', 'pending')
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        if ($postBids->count() < 1) {
            return response()->json(response_formatter(DEFAULT_404, null), 404);
        }

        $postBids->getCollection()->transform(function ($postBid) {
            $postBid->provider->nextBookingEligibility = nextBookingEligibility($postBid->provider_id);
            $postBid->provider->scheduleBookingEligibility = scheduleBookingEligibility($postBid->provider_id);
            return $postBid;
        });

        return response()->json(response_formatter(DEFAULT_200, $postBids), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|uuid',
            'provider_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $postBid = $this->post_bid
            ->with(['provider.owner.reviews'])
            ->where('post_id', $request['post_id'])
            ->where('provider_id', $request['provider_id'])
            ->first();

        if (!isset($postBid)) {
            return response()->json(response_formatter(DEFAULT_404, null), 404);
        }

        $postBid->provider->nextBookingEligibility = nextBookingEligibility($postBid->provider_id);
        $postBid->provider->scheduleBookingEligibility = scheduleBookingEligibility($postBid->provider_id);

        $walletBalance = (float)User::find($request->user()?->id)?->wallet_balance;
        return response()->json(response_formatter(DEFAULT_200, ['post_bid' => $postBid, 'wallet_balance' => $walletBalance]), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|uuid',
            'provider_id' => 'required|uuid',
            'status' => 'required|in:accept,deny',
            'booking_schedule' => 'date',
            'service_address_id' => $request['status'] != 'deny' && is_null($request['service_address']) ? 'required' : 'nullable',
            'service_address' => $request['status'] != 'deny' && is_null($request['service_address_id']) ? [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail($attribute . ' must be a valid JSON string.');
                        return;
                    }

                    if (is_null($decoded['lat']) || $decoded['lat'] == '') $fail($attribute . ' must contain "lat" properties.');
                    if (is_null($decoded['lon']) || $decoded['lon'] == '') $fail($attribute . ' must contain "lon" properties.');
                    if (is_null($decoded['address']) || $decoded['address'] == '') $fail($attribute . ' must contain "address" properties.');
                    if (is_null($decoded['contact_person_name']) || $decoded['contact_person_name'] == '') $fail($attribute . ' must contain "contact_person_name" properties.');
                    if (is_null($decoded['contact_person_number']) || $decoded['contact_person_number'] == '') $fail($attribute . ' must contain "contact_person_number" properties.');
                    if (is_null($decoded['address_label']) || $decoded['address_label'] == '') $fail($attribute . ' must contain "address_label" properties.');
                },
            ] : '',

            'is_partial' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $postBid = $this->post_bid
            ->whereHas('post', function ($query) {
                $query->where('is_booked', '!=', 1);
            })
            ->where('post_id', $request['post_id'])
            ->where('provider_id', $request['provider_id'])
            ->where('status', 'pending')
            ->first();

        if (is_null($postBid))
            return response()->json(response_formatter(DEFAULT_404, null), 200);

        if ($request['status'] == 'deny') {
            $postBid->status = 'denied';
            $postBid->save();

            $provider = Provider::with('owner')->find($request['provider_id']);

            if ($provider) {
                $data_info = [
                    'user_name' => $request->user()->first_name . ' ' . $request->user()->last_name,
                    'provider_name' => $provider?->company_name
                ];
                $title = get_push_notification_message('provider_bid_request_denied', 'provider_notification', $provider?->owner?->current_language_key);
                if ($title && $provider?->owner?->fcm_token) {
                    device_notification_for_bidding($provider->owner->fcm_token, $title, null, null, 'bidding', null, null, $request['provider_id'], $data_info);
                }
            }

            return response()->json(response_formatter(DEFAULT_UPDATE_200, null), 200);
        }

        if (is_null($request['service_address_id'])) {
            $request['service_address_id'] = $this->add_address(json_decode($request['service_address']), $request->user()->id);
        }

        $data = [
            'payment_method' => 'cash_after_service',
            'zone_id' => config('zone_id'),
            'service_tax' => $postBid?->post?->service?->tax,
            'provider_id' => $postBid->provider_id,
            'price' => $postBid->offered_price,
            'service_schedule' => !is_null($request['booking_schedule']) ? $request['booking_schedule'] : $postBid->post->booking_schedule,
            'service_id' => $postBid->post->service_id,
            'category_id' => $postBid->post->category_id,
            'sub_category_id' => $postBid->post->category_id,
            'service_address_id' => !is_null($request['service_address_id']) ? $request['service_address_id'] : $postBid->post->service_address_id,
            'is_partial' => $request['is_partial']
        ];

        $response = $this->placeBookingRequestForBidding($request->user()->id, $request, 'cash-payment', $data);

        if ($response['flag'] == 'success') {
            self::acceptPostBidOffer($postBid->id, $response['booking_id']);
        } else {
            return response()->json(response_formatter(DEFAULT_FAIL_200, null), 200);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200, null), 200);
    }

    public static function acceptPostBidOffer($postBidId, $booking_id): void
    {
        DB::transaction(function () use ($postBidId, $booking_id) {
            $post_bid = PostBid::find($postBidId);
            $post_bid->post->is_booked = 1;
            $post_bid->post->booking_id = $booking_id;
            $post_bid->post->save();

            $post_bid->status = 'accepted';
            $post_bid->save();
        });
    }
}
