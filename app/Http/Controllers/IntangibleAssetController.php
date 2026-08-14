<?php

namespace App\Http\Controllers;

use App\Models\IntangibleAsset;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IntangibleAssetController extends Controller
{
    public function index()
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        $items = IntangibleAsset::with('unit')->latest()->paginate(10);
        return view('intangible-assets.index', compact('items'));
    }

    public function create()
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        $units = Unit::whereNotNull('category')->orderBy('category')->orderBy('name')->get();
        $fundingSources = ['Dana Yayasan', 'Hibah/Bantuan Pemerintah (LLDIKTI)', 'Dana Mandiri/UKT Mahasiswa', 'Kerja Sama Industri'];
        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level'); 

        return view('intangible-assets.create', compact('units', 'fundingSources', 'usersByLevel'));
    }

    public function store(Request $request)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:Software,HAKI/Paten,Jurnal Ilmiah,Domain/Hosting,Kurikulum',
            'vendor' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'activation_date' => 'required|date',
            'funding_source' => 'nullable|string|max:255',
            'contract_number' => 'nullable|string|max:255',
            'license_type' => 'required|in:Berlangganan,Selamanya',
            'expiry_date' => 'required_if:license_type,Berlangganan|nullable|date|after:activation_date',
            'reminder_days' => 'nullable|in:30,14,7',
            'quota' => 'nullable|string|max:100',
            'unit_id' => 'required|exists:units,id',
            'access_url' => 'nullable|url|max:255',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pic_id' => 'nullable|exists:users,id',
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('intangible-certificates', 'public');
        }

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'Aktif';

        IntangibleAsset::create($validated);

        return redirect()->route('intangible-assets.index')->with('success', 'Aset non-fisik (prototipe) berhasil ditambahkan!');
    }

    public function show(IntangibleAsset $intangibleAsset)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);
        return view('intangible-assets.show', compact('intangibleAsset'));
    }
}