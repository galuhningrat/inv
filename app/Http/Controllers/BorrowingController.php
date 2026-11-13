<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with('asset');
        $user = auth()->user();

        if (in_array($user->level, ['Sarpras', 'Admin'])) {
            // lihat semua, tidak difilter
        } elseif ($user->level === 'Kalab') {
            // Kalab: peminjaman miliknya sendiri + semua peminjaman aset unit-nya (termasuk yang menunggu approval dia)
            $query->where(function ($q) use ($user) {
                $q->where('borrower_user_id', $user->id)
                    ->orWhereHas('asset', fn($a) => $a->where('unit_id', $user->unit_id));
            });
        } else {
            $query->where('borrower_user_id', $user->id);
        }

        $borrowings = $query->latest()->paginate(10);
        return view('borrowings.index', compact('borrowings'));
    }

    public function create()
    {
        $availableAssets = Asset::with('assetType')
            ->where('status', 'Tersedia')
            ->orderBy('name')
            ->get()
            ->groupBy('assetType.name');

        $borrowerRoles = ['Dosen', 'Mahasiswa', 'Staff', 'Karyawan'];

        return view('borrowings.create', compact('availableAssets', 'borrowerRoles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'borrower_name' => 'required|string|max:255',
            'borrower_role' => 'required|in:Dosen,Mahasiswa,Staff,Karyawan',
            'borrow_date' => 'required|date',
            'return_date' => 'required|date|after:borrow_date',
            'purpose' => 'required|string',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);
        $user = Auth::user();

        // Sarpras/Admin selalu dianggap "unit sendiri" (override, tidak pernah butuh approval Kalab)
        $isCrossUnit = !in_array($user->level, ['Sarpras', 'Admin']) && $asset->unit_id !== $user->unit_id;

        $validated['borrower_user_id'] = $user->id;
        $validated['borrower_name'] = $validated['borrower_name'] ?? $user->name;
        $validated['approved_by'] = $user->id;
        $validated['status'] = $isCrossUnit ? 'Menunggu Persetujuan Kalab' : 'Aktif';

        $borrowing = Borrowing::create($validated);

        if (!$isCrossUnit) {
            $asset->update(['status' => 'Dipinjam']); // sama seperti sebelumnya
        }
        // Lintas-unit: aset TETAP "Tersedia" — jangan dikunci sebelum Kalab approve

        return redirect()->route('borrowings.index')->with(
            'success',
            $isCrossUnit
            ? 'Peminjaman lintas-unit diajukan, menunggu persetujuan Kalab pemilik aset!'
            : 'Peminjaman berhasil dicatat!'
        );
    }

    public function approveCrossUnit(Borrowing $borrowing)
    {
        $this->authorize('approveCrossUnit', $borrowing);

        $borrowing->update([
            'status' => 'Aktif',
            'kalab_approved_by' => Auth::id(),
            'kalab_approved_at' => now(),
        ]);

        $borrowing->asset->update(['status' => 'Dipinjam']); // dikunci sekarang, setelah disetujui

        return redirect()->route('borrowings.index')->with('success', 'Peminjaman lintas-unit disetujui!');
    }

    public function rejectCrossUnit(Request $request, Borrowing $borrowing)
    {
        $this->authorize('rejectCrossUnit', $borrowing);

        $validated = $request->validate(['kalab_rejection_notes' => 'nullable|string']);

        $borrowing->update([
            'status' => 'Ditolak', // otomatis batal, sesuai keputusan Anda
            'kalab_approved_by' => Auth::id(),
            'kalab_approved_at' => now(),
            'kalab_rejection_notes' => $validated['kalab_rejection_notes'] ?? null,
        ]);
        // Aset tidak pernah dikunci sejak awal, jadi tidak perlu direvert

        return redirect()->route('borrowings.index')->with('success', 'Peminjaman lintas-unit ditolak.');
    }

    public function show(Borrowing $borrowing)
    {
        $this->authorize('view', $borrowing);

        $borrowing->load('asset', 'approver');
        return view('borrowings.show', compact('borrowing'));
    }

    public function returnAsset(Borrowing $borrowing)
    {
        $this->authorize('update', $borrowing);

        $borrowing->update(['status' => 'Selesai', 'actual_return_date' => now()]);
        $borrowing->asset->update(['status' => 'Tersedia']);

        return redirect()->route('borrowings.index')->with('success', 'Aset berhasil dikembalikan!');
    }

    public function destroy(Borrowing $borrowing)
    {
        $this->authorize('delete', $borrowing);

        if ($borrowing->status === 'Aktif') {
            $borrowing->asset->update(['status' => 'Tersedia']);
        }
        $borrowing->delete();

        return redirect()->route('borrowings.index')->with('success', 'Data peminjaman berhasil dihapus!');
    }
}