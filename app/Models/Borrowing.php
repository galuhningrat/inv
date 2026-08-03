<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrowing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'borrowing_id',
        'asset_id',
        'borrower_name',
        'borrower_role',
        'borrow_date',
        'return_date',
        'actual_return_date',
        'purpose',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($borrowing) {
            if (empty($borrowing->borrowing_id)) {
                $borrowing->borrowing_id = self::generateBorrowingId();
            }
        });
    }

    public static function generateBorrowingId()
    {
        $lastBorrowing = self::orderBy('borrowing_id', 'desc')->first();

        if ($lastBorrowing) {
            $lastNumber = (int) substr($lastBorrowing->borrowing_id, 4);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "BRW-$newNumber";
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}