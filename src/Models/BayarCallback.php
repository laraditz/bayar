<?php

namespace Laraditz\Bayar\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BayarCallback extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected $casts = [
        'data' => 'json',
    ];
}
