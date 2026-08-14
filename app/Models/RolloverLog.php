<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolloverLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_item_id',
        'new_item_id',
        'source_month',
        'source_year',
        'target_month',
        'target_year',
        'reason',
    ];

    public function originalItem()
    {
        return $this->belongsTo(AssetRequestItem::class, 'original_item_id');
    }

    public function newItem()
    {
        return $this->belongsTo(AssetRequestItem::class, 'new_item_id');
    }
}