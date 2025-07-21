<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'debt_id',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($payment) {
            if ($payment->debt) {
                $debt = $payment->debt;
                $debt->paid_amount = $debt->payments->sum('amount');
                $debt->updateStatus();
            }
        });

        static::deleted(function ($payment) {
            if ($payment->debt) {
                $debt = $payment->debt;
                $debt->paid_amount = $debt->payments->sum('amount');
                $debt->updateStatus();
            }
        });

        static::updated(function ($payment) {
            if ($payment->debt) {
                $debt = $payment->debt;
                $debt->paid_amount = $debt->payments->sum('amount');
                $debt->updateStatus();
            }
        });
    }
}