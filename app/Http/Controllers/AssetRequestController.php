<?php

namespace App\Http\Controllers;

use App\Models\AssetRequest;
use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetRequest::with('requester', 'assetType', 'approver');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10);

        return view('requests.index', compact('requests'));
    }

    public function create()
    {
        $assetTypes = AssetType::all();
        $priorities = ['Rendah', 'Sedang', 'Tinggi', 'Urgent'];

        return view('requests.create', compact('assetTypes', 'priorities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'quantity' => 'required|integer|min:1',
            'estimated_price' => 'nullable|numeric|min:0',
            'priority' => 'required|in:Rendah,Sedang,Tinggi,Urgent',
            'reason' => 'required|string',
        ]);

        $validated['requester_id'] = Auth::id();
        $validated['status'] = 'Pending';

        AssetRequest::create($validated);

        return redirect()->route('requests.index')
            ->with('success', 'Pengajuan berhasil dikirim!');
    }

    public function show(AssetRequest $assetRequest)
    {
        $assetRequest->load('requester', 'assetType', 'approver');
        return view('requests.show', compact('assetRequest'));
    }

    public function approve(AssetRequest $assetRequest)
    {
        $assetRequest->update([
            'status' => 'Disetujui',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('requests.index')
            ->with('success', 'Pengajuan disetujui!');
    }

    public function reject(Request $request, AssetRequest $assetRequest)
    {
        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $assetRequest->update([
            'status' => 'Ditolak',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        return redirect()->route('requests.index')
            ->with('success', 'Pengajuan ditolak!');
    }

    public function destroy(AssetRequest $assetRequest)
    {
        $assetRequest->delete();

        return redirect()->route('requests.index')
            ->with('success', 'Pengajuan berhasil dihapus!');
    }
}