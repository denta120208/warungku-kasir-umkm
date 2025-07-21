<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Debt extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($debt) {
            if ($debt->isDirty('paid_amount')) {
                $debt->updateStatus();
            }
        });
    }

    protected $with = ['payments'];
    
    protected $fillable = [
        'customer_name',
        'amount',
        'paid_amount',
        'due_date',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', '!=', 'paid');
    }

    public function canAcceptPayment($amount)
    {
        return $amount <= $this->remaining_amount;
    }

    public function updateStatus()
    {
        $totalPaid = $this->paid_amount;
        
        if ($totalPaid >= $this->amount) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }
        
        $this->save();
    }
}