<?php

namespace App\Http\Controllers;

use App\Models\AssetBatch;
use App\Models\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class QrCodeController extends Controller
{
    /**
     * Menampilkan daftar QR Code
     */
    public function index(Request $request)
    {
        $query = QrCode::with('asset');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code_content', 'ILIKE', "%{$search}%")
                    ->orWhere('qr_code_id', 'ILIKE', "%{$search}%")
                    ->orWhereHas('asset', function ($q2) use ($search) {
                        $q2->where('asset_id', 'ILIKE', "%{$search}%");
                    });
            });
        }

        $qrCodes = $query->latest()->paginate(20);

        return view('qrcodes.index', compact('qrCodes'));
    }

    /**
     * Menampilkan detail QR Code
     */
    public function show(QrCode $qrCode)
    {
        $qrCode->load('asset');
        return view('qrcodes.show', compact('qrCode'));
    }

    /**
     * Cetak QR Code Tunggal
     */
    public function print(QrCode $qrCode)
    {
        $qrCode->load('asset.batch.assetType');

        $svg = QrCodeGenerator::size(300)
            ->format('svg')
            ->errorCorrection('H')
            ->generate(route('asset.detail', ['qrcode' => $qrCode->code_content]));

        // Bungkus dalam array agar view-nya bisa sama dengan printBatch
        $qrCodesData = [
            ['qrCode' => $qrCode, 'qrCodeSvg' => $svg]
        ];

        return view('qrcodes.print', compact('qrCodesData'));
    }

    /**
     * 🔥 Cetak Semua QR Code dalam Satu Grup (Batch) 🔥
     */
    public function printBatch(Request $request)
    {
        $batchId = $request->query('batch_id');
        $batch = AssetBatch::with(['assets.qrCodes', 'assets.batch.assetType'])->findOrFail($batchId);

        $qrCodesData = [];

        foreach ($batch->assets as $asset) {
            foreach ($asset->qrCodes as $qrCode) {
                // Pastikan relasi dimuat untuk view
                $qrCode->setRelation('asset', $asset);

                $svg = QrCodeGenerator::size(300)
                    ->format('svg')
                    ->errorCorrection('H')
                    ->generate(route('asset.detail', ['qrcode' => $qrCode->code_content]));

                $qrCodesData[] = [
                    'qrCode' => $qrCode,
                    'qrCodeSvg' => $svg
                ];
            }
        }

        return view('qrcodes.print', compact('qrCodesData', 'batch'));
    }

    /**
     * Mengubah status aktif/nonaktif QR Code
     */
    public function toggleStatus(QrCode $qrCode)
    {
        $qrCode->update([
            'status' => $qrCode->status === 'Aktif' ? 'Nonaktif' : 'Aktif'
        ]);

        return redirect()->back()->with('success', 'Status QR Code berhasil diubah.');
    }

    /**
     * Menghapus QR Code
     */
    public function destroy(QrCode $qrCode)
    {
        $qrCode->delete();
        return redirect()->back()->with('success', 'QR Code berhasil dihapus.');
    }

    public function exportPdf(QrCode $qrCode)
    {
        $qrCode->load('asset.batch.assetType');

        // 🔥 KEMBALIKAN KE FORMAT SVG 🔥
        $svg = QrCodeGenerator::size(300)
            ->format('svg')
            ->errorCorrection('H')
            ->generate(route('asset.detail', ['qrcode' => $qrCode->code_content]));

        $qrCodesData = [
            // 🔥 ENCODE SVG KE BASE64 AGAR DOMBDF BISA MERENDERNYA 🔥
            ['qrCode' => $qrCode, 'qrCodeBase64' => 'data:image/svg+xml;base64,' . base64_encode($svg)]
        ];

        $pdf = Pdf::loadView('qrcodes.pdf', compact('qrCodesData'));
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'QR-' . ($qrCode->asset->asset_id ?? 'Aset') . '.pdf';
        $fileName = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $fileName);

        return $pdf->download($fileName);
    }

    public function exportBatchPdf(Request $request)
    {
        $batchId = $request->query('batch_id');
        $batch = AssetBatch::with(['assets.qrCodes', 'assets.batch.assetType'])->findOrFail($batchId);

        $qrCodesData = [];

        foreach ($batch->assets as $asset) {
            foreach ($asset->qrCodes as $qrCode) {
                $qrCode->setRelation('asset', $asset);

                // 🔥 KEMBALIKAN KE FORMAT SVG 🔥
                $svg = QrCodeGenerator::size(300)
                    ->format('svg')
                    ->errorCorrection('H')
                    ->generate(route('asset.detail', ['qrcode' => $qrCode->code_content]));

                $qrCodesData[] = [
                    'qrCode' => $qrCode,
                    // 🔥 ENCODE SVG KE BASE64 AGAR DOMBDF BISA MERENDERNYA 🔥
                    'qrCodeBase64' => 'data:image/svg+xml;base64,' . base64_encode($svg)
                ];
            }
        }

        $pdf = Pdf::loadView('qrcodes.pdf', compact('qrCodesData', 'batch'));
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'QR-Batch-' . ($batch->name ?? 'Grup') . '.pdf';
        $fileName = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $fileName);

        return $pdf->download($fileName);
    }
}
