<?php

use App\Http\Controllers\Api\{
    AuthController,
    AuthorController,
    BookController,
    BookSummaryController,
    BorrowController,
    StripeWebhookController,
    SubscriptionController
};
use App\Http\Middleware\CheckBorrowLimit;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Public
    Route::post('books/smart-search', [BookSummaryController::class, 'smartSearch']);
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{id}', [BookController::class, 'show']);
    Route::get('books/{id}/summary', [BookSummaryController::class, 'show']);
    Route::get('authors', [AuthorController::class, 'index']);
    Route::get('authors/{id}', [AuthorController::class, 'show']);

    // Webhook Stripe : public, authentifié par signature (pas de JWT/Sanctum)
    Route::post('webhooks/stripe', [StripeWebhookController::class, 'handle']);

    // Protégé
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::post('books', [BookController::class, 'store']);
        Route::put('books/{id}', [BookController::class, 'update']);
        Route::delete('books/{id}', [BookController::class, 'destroy']);
        Route::post('books/{id}/summary', [BookSummaryController::class, 'store']);

        Route::post('authors', [AuthorController::class, 'store']);
        Route::put('authors/{id}', [AuthorController::class, 'update']);
        Route::delete('authors/{id}', [AuthorController::class, 'destroy']);

        Route::get('borrows', [BorrowController::class, 'index']);
        Route::post('borrows', [BorrowController::class, 'store'])->middleware(CheckBorrowLimit::class);
        Route::put('borrows/{id}/return', [BorrowController::class, 'return']);

        Route::post('subscriptions/payment-intent', [SubscriptionController::class, 'paymentIntent']);
        Route::get('subscriptions/status', [SubscriptionController::class, 'status']);
    });
});
