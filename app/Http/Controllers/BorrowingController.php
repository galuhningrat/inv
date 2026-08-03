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

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
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

        $validated['approved_by'] = Auth::id();
        $validated['status'] = 'Aktif';

        $borrowing = Borrowing::create($validated);

        // Update asset status
        Asset::where('id', $validated['asset_id'])
            ->update(['status' => 'Dipinjam']);

        return redirect()->route('borrowings.index')
            ->with('success', 'Peminjaman berhasil dicatat!');
    }

    public function show(Borrowing $borrowing)
    {
        $borrowing->load('asset', 'approver');
        return view('borrowings.show', compact('borrowing'));
    }

    public function returnAsset(Borrowing $borrowing)
    {
        $borrowing->update([
            'status' => 'Selesai',
            'actual_return_date' => now(),
        ]);

        // Update asset status
        $borrowing->asset->update(['status' => 'Tersedia']);

        return redirect()->route('borrowings.index')
            ->with('success', 'Aset berhasil dikembalikan!');
    }

    public function destroy(Borrowing $borrowing)
    {
        if ($borrowing->status === 'Aktif') {
            $borrowing->asset->update(['status' => 'Tersedia']);
        }

        $borrowing->delete();

        return redirect()->route('borrowings.index')
            ->with('success', 'Data peminjaman berhasil dihapus!');
    }
}