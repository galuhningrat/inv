<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\Asset;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = QrCode::with('asset');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('code_content', 'ILIKE', "%{$search}%")
                ->orWhereHas('asset', function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%");
                });
        }

        $qrCodes = $query->latest()->paginate(10);

        return view('qrcodes.index', compact('qrCodes'));
    }

    public function show(QrCode $qrCode)
    {
        $qrCode->load('asset');
        return view('qrcodes.show', compact('qrCode'));
    }

    public function toggleStatus(QrCode $qrCode)
    {
        $newStatus = $qrCode->status === 'Aktif' ? 'Nonaktif' : 'Aktif';
        $qrCode->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Status QR Code berhasil diubah menjadi {$newStatus}!");
    }

    public function print(QrCode $qrCode)
    {
        $qrCode->load('asset');

        $qrCodeSvg = QrCodeGenerator::size(150)
            ->format('svg')
            ->generate(route('asset.detail', ['qrcode' => $qrCode->code_content]));

        return view('qrcodes.print', compact('qrCode', 'qrCodeSvg'));
    }

    public function exportAllPdf()
    {
        $qrCodes = QrCode::with('asset')->where('status', 'Aktif')->get();

        $data = $qrCodes->map(function ($qrCode) {
            return [
                'qrCode' => $qrCode,
                'svg' => QrCodeGenerator::size(80)
                    ->format('svg')
                    ->generate(route('asset.detail', ['qrcode' => $qrCode->code_content]))
            ];
        });

        $pdf = Pdf::loadView('qrcodes.export-pdf', compact('data'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('qr-codes-semua-aset.pdf');
    }

    public function destroy(QrCode $qrCode)
    {
        $qrCode->delete();

        return redirect()->route('qrcodes.index')
            ->with('success', 'Data QR Code berhasil dihapus!');
    }
}