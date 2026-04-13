<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePpobInquiryRequest;
use App\Http\Requests\Api\StorePpobTransactionRequest;
use App\Http\Resources\PpobProductResource;
use App\Http\Resources\PpobTransactionResource;
use App\Models\PpobTransaction;
use App\Services\PpobCatalogService;
use App\Services\PpobTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PpobController extends Controller
{
    public function products(Request $request, PpobCatalogService $catalogService): AnonymousResourceCollection
    {
        $products = $catalogService->paginateProducts([
            'service_type' => $request->query('service_type'),
            'category' => $request->query('category'),
            'brand' => $request->query('brand'),
            'type' => $request->query('type'),
            'search' => $request->query('search'),
        ], min(100, max(1, $request->integer('per_page', 20))));

        return PpobProductResource::collection($products);
    }

    public function paymentChannels(PpobTransactionService $transactionService): JsonResponse
    {
        return response()->json([
            'data' => $transactionService->paymentChannels(),
        ]);
    }

    public function inquiry(
        StorePpobInquiryRequest $request,
        PpobTransactionService $transactionService,
    ): JsonResponse {
        return response()->json([
            'data' => $transactionService->createInquiry($request->validated(), $request->user()),
        ]);
    }

    public function store(
        StorePpobTransactionRequest $request,
        PpobTransactionService $transactionService,
    ): JsonResponse {
        $transaction = $transactionService->createTransaction($request->validated(), $request->user());

        return (new PpobTransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    public function history(Request $request, PpobTransactionService $transactionService): AnonymousResourceCollection
    {
        $transactions = $transactionService->history(
            $request->user(),
            min(100, max(1, $request->integer('per_page', 15))),
        );

        return PpobTransactionResource::collection($transactions);
    }

    public function show(Request $request, PpobTransaction $ppobTransaction): PpobTransactionResource
    {
        abort_unless($ppobTransaction->user_id === $request->user()->id, 404);

        return new PpobTransactionResource($ppobTransaction);
    }
}
