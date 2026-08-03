<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetDetailController extends Controller
{
    public function show(Request $request)
    {
        $qrcode = $request->query('qrcode');

        if (!$qrcode) {
            return view('public.asset-detail', [
                'asset' => null,
                'error' => 'Tidak ada QR Code yang terdeteksi di URL.'
            ]);
        }

        $asset = Asset::with('assetType')
            ->where('qr_code', $qrcode)
            ->first();

        if (!$asset) {
            return view('public.asset-detail', [
                'asset' => null,
                'error' => 'Aset dengan QR Code ini tidak ditemukan.'
            ]);
        }

        return view('public.asset-detail', [
            'asset' => $asset,
            'error' => null
        ]);
    }
}