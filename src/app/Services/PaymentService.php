<?php

namespace App\Services;

use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Simulate processing a payment with an 80% success rate.
     *
     * @param int $bookingId
     * @param float $amount
     * @return array
     */
    public function processPayment(int $bookingId, float $amount): array
    {
        if ($amount <= 0) {
            return [
                'status' => 'failed',
                'transaction_id' => null,
                'message' => 'Amount must be positive.'
            ];
        }

        // 80% chance of success simulation
        if (rand(1, 10) <= 8) {
            return [
                'status' => 'success', 
                'transaction_id' => 'MOCK_TXN_' . Str::random(12),
                'message' => 'Payment successful.'
            ];
        } else {
            return [
                'status' => 'failed', 
                'transaction_id' => null,
                'message' => 'Payment failed due to gateway error.'
            ];
        }
    }
}