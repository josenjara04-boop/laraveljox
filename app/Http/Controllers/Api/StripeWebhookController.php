<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();           // brut, tsy ovaina
        $signature = $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook')
            );
        } catch (SignatureVerificationException | UnexpectedValueException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Signature invalide',
            ], 401);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $object = $event->data->object;
            $userId = $object->metadata->user_id ?? null;

            if ($userId && ($user = User::find($userId))) {
                $user->update(['subscription_status' => 'premium']);
            }
        }

        return response()->json(['received' => true]);
    }
}