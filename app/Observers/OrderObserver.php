<?php

namespace App\Observers;

use App\Mail\OrderStatusMail;
use App\Models\OrderModel;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    /**
     * Handle the OrderModel "created" event.
     */
    public function created(OrderModel $orderModel): void
    {
        //
    }

    /**
     * Handle the OrderModel "updated" event.
     */
    public function updated(OrderModel $order)
    {
        // ✅ Harusnya
        if ($order->wasChanged('status') && in_array($order->status, ['confirmed', 'rejected'])) {
            $order->load(['items.book', 'paymentMethod', 'user']);
            Mail::to($order->user->email)
                ->queue(new OrderStatusMail($order));
        }
    }

    /**
     * Handle the OrderModel "deleted" event.
     */
    public function deleted(OrderModel $orderModel): void
    {
        //
    }

    /**
     * Handle the OrderModel "restored" event.
     */
    public function restored(OrderModel $orderModel): void
    {
        //
    }

    /**
     * Handle the OrderModel "force deleted" event.
     */
    public function forceDeleted(OrderModel $orderModel): void
    {
        //
    }
}
