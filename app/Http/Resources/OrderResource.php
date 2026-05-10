<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id'             => $this->id,
            'order_code'     => $this->order_code ?? null,
            'status'         => $this->status,
            'proof_status'   => $this->proof_status ?? 'not_uploaded',
            'proof_note'     => $this->proof_note ?? null,
            'payment_proof'  => $this->payment_proof
                ? asset('storage/' . $this->payment_proof)
                : null,
            'total'          => $this->total,
            'created_at'     => $this->created_at,

            'payment_method' => $this->whenLoaded('paymentMethod', fn() => [
                'id'             => $this->paymentMethod->id,
                'name'           => $this->paymentMethod->name,
                'code'           => $this->paymentMethod->code,
                'bank_name'      => $this->paymentMethod->bank_name ?? null,
                'account_name'   => $this->paymentMethod->account_name ?? null,
                'account_number' => $this->paymentMethod->account_number ?? null,
                'description'    => $this->paymentMethod->description ?? null,
            ]),

            'items' => $this->whenLoaded('items', function () use ($user) {
                return $this->items->map(function ($item) use ($user) {

                    $canDownload =
                        $item->type === 'pdf'
                        && in_array($this->status, ['verified', 'completed', 'confirmed'])
                        && (
                            $this->paymentMethod?->code === 'cash'
                            || $this->proof_status === 'verified'
                        )
                        && $user?->id === $this->user_id
                        && $item->book?->file_path;

                    $downloadUrl = $canDownload
                        ? url("/api/orders/{$this->id}/download/{$item->book_id}")
                        : null;

                    return [
                        'id'           => $item->id,
                        'type'         => $item->type,
                        'qty'          => $item->qty,
                        'price'        => $item->price,
                        'download_url' => $downloadUrl,
                        'book'         => $item->book ? [
                            'id'     => $item->book->id,
                            'title'  => $item->book->title,
                            'author' => $item->book->author,
                            'cover'  => $item->book->cover
                                ? asset('storage/' . $item->book->cover)
                                : null,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
