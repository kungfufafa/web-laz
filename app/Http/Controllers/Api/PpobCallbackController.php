<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PpobCallbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PpobCallbackController extends Controller
{
    public function midtrans(Request $request, PpobCallbackService $callbackService): JsonResponse
    {
        $result = $callbackService->handleMidtransCallback($request);

        return response()->json($result['body'], $result['status']);
    }

    public function tripay(Request $request, PpobCallbackService $callbackService): JsonResponse
    {
        $result = $callbackService->handleTripayCallback($request);

        return response()->json($result['body'], $result['status']);
    }

    public function digiflazz(Request $request, PpobCallbackService $callbackService): JsonResponse
    {
        $result = $callbackService->handleDigiflazzCallback($request);

        return response()->json($result['body'], $result['status']);
    }
}
