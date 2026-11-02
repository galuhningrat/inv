<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🔥 Helper untuk mencatat log dengan mudah di seluruh aplikasi 🔥
     */
    public static function record($action, $description)
    {
        if (auth()->check()) {
            self::create([
                'user_id' => auth()->id(),
                'action' => strtoupper($action),
                'description' => $description,
                'ip_address' => request()->ip(),
            ]);
        }
    }
}
