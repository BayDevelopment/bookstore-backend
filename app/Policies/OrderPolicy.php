<?php

namespace App\Policies;

use App\Models\OrderModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function view(User $user, OrderModel $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function downloadPdf(User $user, OrderModel $order): bool
    {
        // 1. Ownership
        if ($user->id !== $order->user_id) {
            return false;
        }

        // 2. Status order harus verified/completed
        if (!in_array($order->status, ['verified', 'completed'])) {
            return false;
        }

        // 3. Kalau bukan cash, proof harus verified
        if ($order->paymentMethod?->code !== 'cash' && $order->proof_status !== 'verified') {
            return false;
        }

        // 4. Minimal ada 1 item PDF
        $hasPdf = $order->items()->where('type', 'pdf')->exists();
        if (!$hasPdf) {
            return false;
        }

        return true;
    }
}
