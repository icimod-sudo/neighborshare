<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;


class Exchange extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'from_user_id',
        'to_user_id',
        'status',
        'type',
        'agreed_price',
        'message',
        'exchange_date'
    ];

    protected $casts = [
        'exchange_date' => 'datetime',
        'agreed_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
