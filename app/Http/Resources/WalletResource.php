<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'balance'           => $this->balance,
            'frozen'            => $this->frozen,
            'available_balance' => $this->available_balance,
            'currency'          => $this->currency,
            'is_active'         => $this->is_active,

            'accounts' => $this->whenLoaded('accounts', fn() =>
                $this->accounts->map(fn($a) => [
                    'id'         => $a->id,
                    'name'       => $a->name,
                    'type'       => $a->type,
                    'balance'    => $a->balance,
                    'currency'   => $a->currency,
                    'is_default' => $a->is_default,
                ])
            ),
        ];
    }
}
