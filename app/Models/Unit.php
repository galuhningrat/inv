<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'category'];

<<<<<<< HEAD
    /**
     * 🔥 Accessor untuk mendapatkan Kode Unit secara otomatis
     */
    public function getCodeAttribute()
    {
        $name = strtolower(trim($this->name));

        // Pemetaan nama unit ke kode singkatan
        $map = [
            'teknik informatika' => 'TI',
            'informatika' => 'TI',
            'teknik elektro' => 'TE',
            'elektro' => 'TE',
            'teknik mesin' => 'TM',
            'mesin' => 'TM',
            'laboratorium komputer' => 'LABKOM',
            'lab komputer' => 'LABKOM',
            'laboratorium elektro' => 'LABEL',
            'laboratorium mesin' => 'LABMES',
            'rektorat' => 'REK',
            'umum' => 'UMUM',
            'perpustakaan' => 'PERPUS',
            'biro sarpras' => 'SARPRAS',
            'fasilitas umum' => 'FASUM',
            'unit it' => 'IT',
            'it & jaringan' => 'IT',
            'administrasi' => 'ADM',
        ];

        // Cek apakah nama unit mengandung salah satu kata kunci di atas
        foreach ($map as $key => $code) {
            if (str_contains($name, $key)) {
                return $code;
            }
        }

        // Fallback: Ambil huruf depan dari setiap kata (maksimal 4 huruf)
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return substr($initials, 0, 4) ?: 'UNT';
    }

=======
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
