<?php

namespace Laraditz\Bayar\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laraditz\Bayar\Enums\Status;
use Laraditz\Bayar\Enums\PaymentStatus;

class BayarPayment extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected $casts = [
        'extra' => 'json',
        'expires_at' => 'datetime',
        'status' => Status::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function getCanPayAttribute(): bool
    {
        return $this->hit <= config('bayar.link_visit_limit');
    }
}
