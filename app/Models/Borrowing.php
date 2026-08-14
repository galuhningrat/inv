<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Borrowing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'borrowing_id',
        'asset_id',
        'borrower_name',
        'borrower_role',
        'borrower_user_id',
        'borrow_date',
        'return_date',
        'actual_return_date',
        'purpose',
        'status',
        'approved_by',
        'kalab_approved_by',
        'kalab_approved_at',
        'kalab_rejection_notes',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
        'actual_return_date' => 'date',
        'kalab_approved_at' => 'datetime',
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
        return DB::transaction(function () {
            $lastBorrowing = self::where('borrowing_id', 'LIKE', 'BRW-%')
                ->lockForUpdate()
                ->orderByRaw("CAST(SUBSTRING(borrowing_id FROM '[0-9]+$') AS INTEGER) DESC")
                ->first();

            $lastNumber = 0;
            if ($lastBorrowing) {
                preg_match('/(\d+)$/', $lastBorrowing->borrowing_id, $matches);
                $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
            }

            return sprintf('BRW-%03d', $lastNumber + 1);
        });
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function kalabApprover()
    {
        return $this->belongsTo(User::class, 'kalab_approved_by');
    }
}
