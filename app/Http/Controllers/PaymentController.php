<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Subscription;
use App\Models\Payment;
use Stripe\Price;
use Stripe\Product;

class PaymentController extends Controller
{
    public function index()
    {
        return view('payment');
    }

    public function checkout(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
         // Get amount from form
        $amount = $request->amount;

        // Convert to cents (VERY IMPORTANT)
        $amountInCents = $amount * 100;
        try {

            $product = Product::create([
                'name' => 'Dynamic Product - Payment',
            ]);
            $price = Price::create([
                'unit_amount' => $amount,
                'currency' => 'inr',
                'product' => 'prod_XXXXXXXX', // 👈 your product_id from dashboard
            ]);
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'INR',
                        'product_data' => [
                            'name' => 'Laravel Stripe Payment',
                        ],
                        'unit_amount' => $amountInCents, // $10.00 in cents
                    ],
                    'quantity' => 1,
                ]],
                'payment_method_collection' => 'always',
                'metadata' => [
                    'type' => 'one_time_payment'
                ],
                'mode' => 'payment',
                'billing_address_collection' => 'required',
                'customer_creation' => 'always',
                'success_url' => route('payment.success'),
                'cancel_url' => route('payment.cancel'),
            ]);

            return redirect($session->url, 303);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Unable to create payment session: ' . $e->getMessage()]);
        }
    }
    public function subscription()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription', // 👈 important
            'line_items' => [[
                'price' => 'price_123456789', // from Stripe dashboard
                'quantity' => 1,
            ]],
            'success_url' => route('payment.success'),
            'cancel_url' => route('payment.cancel'),
        ]);

        return redirect($session->url);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);

            if ($event->type === 'checkout.session.completed') {

                $session = $event->data->object;

                // ✅ Save payment
                $payment = Payment::create([
                    'stripe_session_id' => $session->id,
                    'payment_intent_id' => $session->payment_intent,
                    'customer_id' => $session->customer,
                    'amount' => $session->amount_total,
                    'currency' => $session->currency,
                    'status' => 'paid',
                ]);

                // ✅ CREATE SUBSCRIPTION AFTER PAYMENT
                Stripe::setApiKey(env('STRIPE_SECRET'));

                Subscription::create([
                    'customer' => $session->customer,
                    'items' => [
                        [
                            'price' => 'price_123456789', // your recurring price
                        ],
                    ],
                ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Webhook failed'], 400);
        }
    }
    public function refund($id)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payment = Payment::findOrFail($id);

        // Refund::create([
        //     'payment_intent' => $payment->payment_intent_id,
        // ]);

        $payment->update(['status' => 'refunded']);

        return "Refund successful";
    }
}
