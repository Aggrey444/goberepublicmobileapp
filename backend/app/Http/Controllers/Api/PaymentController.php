<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitializePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaystackService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private PaystackService $paystack)
    {
    }

    public function initialize(InitializePaymentRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('id', $request->validated()['order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return ApiResponse::error('Order not found.', null, 404);
        }

        if ($order->payment_status === Order::PAYMENT_STATUS_SUCCESSFUL) {
            return ApiResponse::error('This order has already been paid.', null, 422);
        }

        $reference = 'GOB-' . strtoupper(Str::random(16));
        $email = $request->validated()['email'] ?? $user->email;

        if (!$email) {
            return ApiResponse::error('An email address is required to initialize payment.', null, 422);
        }

        $amountInKobo = (int) round($order->total * 100);

        $paystackResponse = $this->paystack->initializeTransaction(
            $email,
            $amountInKobo,
            $reference,
            ['order_id' => $order->id, 'order_number' => $order->order_number]
        );

        if (empty($paystackResponse['status']) || $paystackResponse['status'] !== true) {
            Log::error('Paystack initialization failed', [
                'order_id' => $order->id,
                'reference' => $reference,
                'response' => $paystackResponse,
            ]);

            return ApiResponse::error('Payment initialization failed.', null, 502);
        }

        $data = $paystackResponse['data'];

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'provider' => 'paystack',
            'reference' => $reference,
            'access_code' => $data['access_code'] ?? null,
            'authorization_url' => $data['authorization_url'] ?? null,
            'amount' => $order->total,
            'currency' => 'NGN',
            'status' => Payment::STATUS_PENDING,
            'metadata' => ['email' => $email],
        ]);

        $order->update(['order_status' => Order::ORDER_STATUS_PAYMENT_PENDING]);

        return ApiResponse::success('Payment initialized successfully.', [
            'payment' => $payment,
            'authorization_url' => $data['authorization_url'] ?? null,
            'access_code' => $data['access_code'] ?? null,
            'reference' => $reference,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate(['reference' => ['required', 'string']]);
        $reference = $request->string('reference');

        $payment = Payment::where('reference', $reference)
            ->where('user_id', $request->user()->id)
            ->with('order')
            ->first();

        if (!$payment) {
            return ApiResponse::error('Payment not found.', null, 404);
        }

        $result = $this->verifyAndSettle($payment);

        if (!$result['success']) {
            return ApiResponse::error($result['message'], null, 422);
        }

        return ApiResponse::success('Payment verified successfully.', [
            'payment' => $result['payment'],
            'order' => $result['order'],
        ]);
    }

    public function handleWebhook(Request $request): JsonResponse
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();
        $expected = hash_hmac('sha512', $payload, (string) config('services.paystack.secret_key'));

        if (!hash_equals($expected, (string) $signature)) {
            Log::warning('Invalid Paystack webhook signature');
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'charge.success' && isset($data['reference'])) {
            $payment = Payment::where('reference', $data['reference'])->with('order')->first();

            if ($payment) {
                $this->confirmPayment($payment, $data);
            }
        }

        return response()->json(['status' => 'success']);
    }

    private function verifyAndSettle(Payment $payment): array
    {
        if ($payment->status === Payment::STATUS_SUCCESSFUL) {
            return [
                'success' => true,
                'message' => 'Payment already verified.',
                'payment' => $payment,
                'order' => $payment->order,
            ];
        }

        $result = $this->paystack->verifyTransaction($payment->reference);

        if (empty($result['status']) || $result['status'] !== true || ($result['data']['status'] ?? '') !== 'success') {
            Log::warning('Payment verification failed', [
                'reference' => $payment->reference,
                'response' => $result,
            ]);
            $payment->update(['status' => Payment::STATUS_FAILED]);

            return ['success' => false, 'message' => 'Payment verification failed.', 'payment' => $payment->refresh(), 'order' => $payment->order];
        }

        $this->confirmPayment($payment, $result['data']);

        return [
            'success' => true,
            'message' => 'Payment verified.',
            'payment' => $payment->refresh(),
            'order' => $payment->order,
        ];
    }

    private function confirmPayment(Payment $payment, array $data): void
    {
        $paidAmountKobo = (int) ($data['amount'] ?? 0);
        $expectedKobo = (int) round($payment->amount * 100);

        if ($paidAmountKobo !== $expectedKobo) {
            Log::error('Payment amount mismatch', [
                'reference' => $payment->reference,
                'expected' => $expectedKobo,
                'received' => $paidAmountKobo,
            ]);
            $payment->update(['status' => Payment::STATUS_FAILED]);

            return;
        }

        if ($payment->status === Payment::STATUS_SUCCESSFUL) {
            return;
        }

        $payment->update([
            'status' => Payment::STATUS_SUCCESSFUL,
            'paid_at' => now(),
        ]);

        $order = $payment->order;
        $order->update([
            'payment_status' => Order::PAYMENT_STATUS_SUCCESSFUL,
            'order_status' => $payment->order->order_status === Order::ORDER_STATUS_PENDING
                ? Order::ORDER_STATUS_PAID
                : $order->order_status,
        ]);
    }
}
