<?php

namespace App\Http\Controllers\API;

use App\Exceptions\MachineAuthProtocolException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMachineEventRequest;
use App\Http\Requests\Api\ValidateMachineAccessRequest;
use App\Http\Resources\MachineAuthorizationResource;
use App\Services\MachineAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MachineAuthorizationController extends Controller
{
    public function validateAccess(
        ValidateMachineAccessRequest $request,
        MachineAuthorizationService $service
    ) {
        try {
            $result = $service->authorize(
                $request->user(),
                $request->validated('card_uid'),
                $request->validated('machine_code'),
                $request->validated('request_id')
            );
        } catch (MachineAuthProtocolException $exception) {
            return $this->protocolError($exception);
        }

        return (new MachineAuthorizationResource($result))
            ->response()
            ->setStatusCode($result['http_status']);
    }

    public function storeEvent(
        StoreMachineEventRequest $request,
        MachineAuthorizationService $service
    ): JsonResponse {
        try {
            $result = $service->recordEvent($request->user(), $request->validated());
        } catch (MachineAuthProtocolException $exception) {
            return $this->protocolError($exception);
        }

        return response()->json([
            'accepted' => true,
            'duplicate' => $result['duplicate'],
            'event_id' => $result['event']->event_id,
            'transaction_id' => $result['event']->transaction_id,
        ], $result['duplicate'] ? 200 : 201);
    }

    public function sync(Request $request, MachineAuthorizationService $service): JsonResponse
    {
        return response()->json($service->syncPayload($request->user()));
    }

    private function protocolError(MachineAuthProtocolException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'reason' => $exception->reason(),
        ], $exception->status());
    }
}
