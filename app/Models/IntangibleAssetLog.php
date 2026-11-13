<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntangibleAssetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'intangible_asset_id',
        'user_id',
        'action',
        'changes',
        'description',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function asset()
    {
        return $this->belongsTo(IntangibleAsset::class, 'intangible_asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Warna badge untuk setiap action.
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created' => '#10b981',      // Hijau
            'updated' => '#3b82f6',      // Biru
            'deleted' => '#ef4444',      // Merah
            'restored' => '#f59e0b',     // Kuning
            default => '#6b7280',
        };
    }

    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'created' => '✚',
            'updated' => '✎',
            'deleted' => '🗑️',
            'restored' => '↺',
            default => '•',
        };
    }
}
