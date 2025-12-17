<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Services\DoorDashService;
use App\Models\OrderMaster;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Business;

class DoorDashController extends Controller
{
    protected DoorDashService $doorDashService;

    public function __construct(DoorDashService $doorDashService)
    {
        $this->doorDashService = $doorDashService;
    }


    /**
     * 1. Get Delivery Quote
     * POST /api/doordash/quotes
     */
    public function createQuote(Request $request)
    {
        Log::info('DoorDash Create Quote API called', [
            'request' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'external_delivery_id' => 'required|string',
            'pickup_address' => 'required|string',
            'pickup_phone_number' => 'required|string',
            'dropoff_address' => 'required|string',
            'dropoff_phone_number' => 'required|string',
            'order_value' => 'required|integer|min:0',
            'locale' => 'nullable|string',
            'pickup_business_name' => 'nullable|string',
            'pickup_instructions' => 'nullable|string',
            'pickup_reference_tag' => 'nullable|string',
            'pickup_external_business_id' => 'nullable|string',
            'pickup_external_store_id' => 'nullable|string',
            'dropoff_business_name' => 'nullable|string',
            'dropoff_instructions' => 'nullable|string',
            'dropoff_contact_given_name' => 'nullable|string',
            'dropoff_contact_family_name' => 'nullable|string',
            'dropoff_contact_send_notifications' => 'nullable|boolean',
            'currency' => 'nullable|string',
            'pickup_time' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'dropoff_time' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'pickup_window.start_time' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'pickup_window.end_time' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'dropoff_window.start_time' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'dropoff_window.end_time' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'contactless_dropoff' => 'nullable|boolean',
            'action_if_undeliverable' => 'nullable|in:return_to_pickup,leave_at_door',
            'tip' => 'nullable|integer|min:0',
            'order_contains.alcohol' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            Log::warning('Create Quote validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $result = $this->doorDashService
                ->makeRequest('post', '/quotes', $request->all());

            Log::info('DoorDash Create Quote response', $result);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? 'Quote created successfully'
                    : 'Failed to create quote',
                'data' => $result['data'] ?? null,
                'mode' => $result['mode'] ?? 'unknown'
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Create Quote exception', [
                'error' => $ex->getMessage()
            ]);

            return response()->json([
                'message' => $ex->getMessage()
            ], 500);
        }
    }


    /**
     * 2. Accept Quote
     * POST /api/doordash/quotes/{external_delivery_id}/accept
     */
    public function acceptQuote(Request $request, string $externalDeliveryId)
    {
        Log::info('DoorDash Accept Quote API called', [
            'external_delivery_id' => $externalDeliveryId,
            'request' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'tip' => 'nullable|integer|min:0',
            'locale' => 'nullable|string',
            'pickup_business_name' => 'nullable|string',
            'pickup_instructions' => 'nullable|string',
            'pickup_reference_tag' => 'nullable|string',
            'pickup_external_business_id' => 'nullable|string',
            'pickup_external_store_id' => 'nullable|string',
            'dropoff_business_name' => 'nullable|string',
            'dropoff_instructions' => 'nullable|string',
            'dropoff_contact_given_name' => 'nullable|string',
            'dropoff_contact_family_name' => 'nullable|string',
            'dropoff_contact_send_notifications' => 'nullable|boolean',
            'contactless_dropoff' => 'nullable|boolean',
            'action_if_undeliverable' => 'nullable|in:return_to_pickup,leave_at_door',
            'order_contains.alcohol' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            Log::warning('Accept Quote validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        $acceptParams = $request->only([
            'tip',
            'locale',
            'pickup_business_name',
            'pickup_instructions',
            'pickup_reference_tag',
            'pickup_external_business_id',
            'pickup_external_store_id',
            'dropoff_business_name',
            'dropoff_instructions',
            'dropoff_contact_given_name',
            'dropoff_contact_family_name',
            'dropoff_contact_send_notifications',
            'contactless_dropoff',
            'action_if_undeliverable',
            'order_contains'
        ]);

        Log::info('Accept Quote payload', $acceptParams);

        try {
            $result = $this->doorDashService
                ->acceptQuote($externalDeliveryId, $acceptParams);

            Log::info('DoorDash Accept Quote response', $result);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? 'Quote accepted successfully'
                    : 'Failed to accept quote',
                'data' => $result['data'] ?? null,
                'mode' => $result['mode'] ?? 'unknown'
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Accept Quote exception', [
                'external_delivery_id' => $externalDeliveryId,
                'error' => $ex->getMessage()
            ]);

            return response()->json([
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * 3. Create Delivery (Direct - without quote)
     * POST /api/doordash/deliveries
     */

    public function createDelivery(Request $request)
    {
        Log::info('DoorDash Create Delivery API called', [
            'request' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'external_delivery_id' => 'required|string',

            'pickup_address' => 'required|string',
            'pickup_phone_number' => 'required|string',
            'pickup_business_name' => 'nullable|string',
            'pickup_instructions' => 'nullable|string',
            'pickup_reference_tag' => 'nullable|string',

            'dropoff_address' => 'required|string',
            'dropoff_phone_number' => 'required|string',
            'dropoff_business_name' => 'nullable|string',
            'dropoff_instructions' => 'nullable|string',
            'dropoff_contact_given_name' => 'nullable|string',
            'dropoff_contact_family_name' => 'nullable|string',
            'dropoff_contact_send_notifications' => 'nullable|boolean',

            'order_value' => 'required|integer|min:0',
            'tip' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|size:3',

            'scheduling_model' => 'nullable|in:asap,scheduled',

            'contactless_dropoff' => 'nullable|boolean',
            'action_if_undeliverable' => 'nullable|in:return_to_pickup,leave_at_door',
        ]);

        if ($validator->fails()) {
            Log::warning('Create Delivery validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([

                'message' => $validator->errors()->first()
            ], 422);
        }

        $payload = $request->only([
            'external_delivery_id',
            'pickup_address',
            'pickup_phone_number',
            'pickup_business_name',
            'pickup_instructions',
            'pickup_reference_tag',
            'dropoff_address',
            'dropoff_phone_number',
            'dropoff_business_name',
            'dropoff_instructions',
            'dropoff_contact_given_name',
            'dropoff_contact_family_name',
            'dropoff_contact_send_notifications',
            'order_value',
            'tip',
            'currency',
            'scheduling_model',
            'contactless_dropoff',
            'action_if_undeliverable'
        ]);

        Log::info('Create Delivery payload', $payload);

        try {
            $result = $this->doorDashService->createDelivery($payload);

            Log::info('DoorDash Create Delivery response', $result);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? 'Delivery created successfully'
                    : 'Failed to create delivery',
                'data' => $result['data'] ?? null,
                'mode' => $result['mode'] ?? 'unknown'
            ], $result['success'] ? 200 : 200);
        } catch (\Exception $e) {
            Log::error('Create Delivery exception', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * 4. Get Delivery Status
     * GET /api/doordash/deliveries/{external_delivery_id}
     */
    public function getDelivery(string $externalDeliveryId)
    {
        Log::info('DoorDash Get Delivery API called', [
            'external_delivery_id' => $externalDeliveryId
        ]);

        try {
            $result = $this->doorDashService->getDelivery($externalDeliveryId);

            Log::info('DoorDash Get Delivery response', $result);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? 'Delivery found'
                    : 'Delivery not found',
                'data' => $result['data'] ?? null,
                'mode' => $result['mode'] ?? 'unknown'
            ], $result['success'] ? 200 : 404);
        } catch (\Exception $e) {
            Log::error('Get Delivery exception', [
                'external_delivery_id' => $externalDeliveryId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * 5. Update Delivery
     * PUT /api/doordash/deliveries/{external_delivery_id}
     */
    public function updateDelivery(Request $request, string $externalDeliveryId)
    {
        Log::info('DoorDash Update Delivery API called', [
            'external_delivery_id' => $externalDeliveryId,
            'request_payload' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'pickup_address' => 'nullable|string',
            'pickup_business_name' => 'nullable|string',
            'pickup_phone_number' => 'nullable|string',
            'pickup_instructions' => 'nullable|string',
            'pickup_reference_tag' => 'nullable|string',
            'pickup_external_business_id' => 'nullable|string',
            'pickup_external_store_id' => 'nullable|string',

            'dropoff_address' => 'nullable|string',
            'dropoff_business_name' => 'nullable|string',
            'dropoff_phone_number' => 'nullable|string',
            'dropoff_instructions' => 'nullable|string',
            'dropoff_contact_given_name' => 'nullable|string',
            'dropoff_contact_family_name' => 'nullable|string',
            'dropoff_contact_send_notifications' => 'nullable|boolean',

            'contactless_dropoff' => 'nullable|boolean',
            'action_if_undeliverable' => 'nullable|in:return_to_pickup,leave_at_door',
            'tip' => 'nullable|integer|min:0',

            'order_contains' => 'nullable|array',
            'order_contains.alcohol' => 'nullable|boolean',

            'dasher_allowed_vehicles' => 'nullable|array',
            'order_value' => 'nullable|integer|min:0',
            'currency' => 'nullable|string',

            'pickup_time' => 'nullable|date',
            'dropoff_time' => 'nullable|date',

            'pickup_window' => 'nullable|array',
            'pickup_window.start_time' => 'nullable|date',
            'pickup_window.end_time' => 'nullable|date',

            'dropoff_window' => 'nullable|array',
            'dropoff_window.start_time' => 'nullable|date',
            'dropoff_window.end_time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            Log::warning('DoorDash Update Delivery Validation Failed', [
                'external_delivery_id' => $externalDeliveryId,
                'error' => $validator->errors()->first()
            ]);

            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $updateParams = $request->only([
                'pickup_address',
                'pickup_business_name',
                'pickup_phone_number',
                'pickup_instructions',
                'pickup_reference_tag',
                'pickup_external_business_id',
                'pickup_external_store_id',

                'dropoff_address',
                'dropoff_business_name',
                'dropoff_phone_number',
                'dropoff_instructions',
                'dropoff_contact_given_name',
                'dropoff_contact_family_name',
                'dropoff_contact_send_notifications',

                'contactless_dropoff',
                'action_if_undeliverable',
                'tip',
                'order_contains',
                'dasher_allowed_vehicles',
                'order_value',
                'currency',
                'pickup_time',
                'dropoff_time',
                'pickup_window',
                'dropoff_window'
            ]);

            $updateParams = array_filter($updateParams, fn($value) => $value !== null);

            Log::info('DoorDash Update Delivery Payload Prepared', [
                'external_delivery_id' => $externalDeliveryId,
                'payload' => $updateParams
            ]);

            $result = $this->doorDashService->updateDelivery($externalDeliveryId, $updateParams);

            Log::info('DoorDash Update Delivery Response', [
                'external_delivery_id' => $externalDeliveryId,
                'response' => $result
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? 'Delivery updated successfully'
                    : ($result['message'] ?? 'Failed to update delivery'),
                'data' => $result['data'] ?? null,
                'mode' => $result['mode'] ?? 'unknown'
            ], $result['success'] ? 200 : 200);
        } catch (\Throwable $e) {
            Log::error('DoorDash Update Delivery Exception', [
                'external_delivery_id' => $externalDeliveryId,
                'exception' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while updating delivery'
            ], 500);
        }
    }


    /**
     * 6. Cancel Delivery
     * DELETE /api/doordash/deliveries/{external_delivery_id}
     */
    public function cancelDelivery(string $externalDeliveryId)
    {
        Log::info('DoorDash Cancel Delivery API called', [
            'external_delivery_id' => $externalDeliveryId
        ]);

        try {
            if (empty($externalDeliveryId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'External delivery ID is required'
                ], 422);
            }

            $result = $this->doorDashService->cancelDelivery($externalDeliveryId);

            Log::info('DoorDash Cancel Delivery Response', [
                'external_delivery_id' => $externalDeliveryId,
                'response' => $result
            ]);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to cancel delivery',
                    'data' => $result['data'] ?? null,
                    'mode' => $result['mode'] ?? 'unknown'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Delivery cancelled successfully',
                'data' => $result['data'] ?? null,
                'mode' => $result['mode'] ?? 'unknown'
            ], 200);
        } catch (\Throwable $e) {
            Log::error('DoorDash Cancel Delivery Exception', [
                'external_delivery_id' => $externalDeliveryId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while cancelling delivery'
            ], 500);
        }
    }



    /**
     * Create a new business
     * POST /api/doordash/businesses
     */
    public function createBusiness(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_business_id' => 'required|string',
            'name' => 'required|string',
            'phone_number' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::warning('DoorDash Create Business Validation Errors', [
                'errors' => $validator->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->doorDashService->createBusiness($request->all());

            if (isset($result['success']) && $result['success'] === true) {
                // Save to local database
                $business = Business::updateOrCreate(
                    ['external_business_id' => $request->external_business_id],
                    [
                        'name' => $request->name,
                        'phone_number' => $request->phone_number,
                        'description' => $request->description,
                        'activation_status' => $request->activation_status ?? 'active'
                    ]
                );

                Log::info('DoorDash Business Saved Locally', [
                    'business_id' => $business->id,
                    'external_business_id' => $business->external_business_id
                ]);
            }

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $ex) {
            Log::error('DoorDash Create Business Exception', [
                'error' => $ex->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }


    /**
     * Create Store for a Business
     * POST /api/doordash/stores
     */
    public function createStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_business_id' => 'required|string',
            'external_store_id'    => 'required',
            'name'                 => 'required',
            'phone_number'         => 'required',
            'address'              => 'required'
        ]);

        if ($validator->fails()) {
            Log::warning('DoorDash Create Store Validation Errors', [
                'errors' => $validator->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->doorDashService->createStore($request->all());


            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $ex) {
            Log::error('DoorDash Create Store Exception', [
                'error' => $ex->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }


    /**
     * update Store for a Business
     * POST /api/doordash/stores
     */
    public function updateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_business_id' => 'required|string',
            'external_store_id'    => 'required',
            'name'                 => 'required',
            'phone_number'         => 'required',
            'address'              => 'required'
        ]);

        if ($validator->fails()) {
            Log::warning('DoorDash Update Store Validation Errors', [
                'errors' => $validator->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->doorDashService->updateStore($request->all());


            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $ex) {
            Log::error('DoorDash Create Update Exception', [
                'error' => $ex->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }


    /**
     * Get Store details for a Business
     * GET /api/doordash/stores/{external_business_id}/{external_store_id}
     */
    public function getStore(string $externalBusinessId, string $externalStoreId)
    {
        Log::info('DoorDash Get Store API called', [
            'external_business_id' => $externalBusinessId,
            'external_store_id' => $externalStoreId
        ]);

        try {
            if (empty($externalBusinessId) || empty($externalStoreId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Both external_business_id and external_store_id are required'
                ], 422);
            }

            $result = $this->doorDashService->getStore($externalBusinessId, $externalStoreId);

            Log::info('DoorDash Get Store Response', [
                'external_business_id' => $externalBusinessId,
                'external_store_id' => $externalStoreId,
                'response' => $result
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? 'Store details retrieved successfully'
                    : ($result['message'] ?? 'Failed to retrieve store details'),
                'data' => $result['data'] ?? null,
                'mode' => $result['mode'] ?? 'unknown'
            ], $result['success'] ? 200 : 404);
        } catch (\Throwable $e) {
            Log::error('DoorDash Get Store Exception', [
                'external_business_id' => $externalBusinessId,
                'external_store_id' => $externalStoreId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while retrieving store details'
            ], 500);
        }
    }
}
