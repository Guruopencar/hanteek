<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'uuid'           => $this->uuid,
            'type'           => $this->type,
            'amount'         => $this->amount,
            'commission'     => $this->commission,
            'net_amount'     => $this->net_amount,
            'currency'       => $this->currency,
            'status'         => $this->status,
            'description'    => $this->description,
            'payment_method' => $this->payment_method,
            'processed_at'   => $this->processed_at?->toISOString(),
            'created_at'     => $this->created_at->toISOString(),
        ];
    }
}
