<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByRaw("CASE 
                                WHEN level = 'Admin' THEN 1
                                WHEN level = 'Sarpras' THEN 2
                                WHEN level = 'Rektor' THEN 3
                                WHEN level = 'Kaprodi' THEN 4
                                WHEN level = 'Keuangan' THEN 5
                                ELSE 6
                            END")
            ->paginate(10); // Tetap gunakan paginate(10) Anda

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $levels = ['Admin', 'Sarpras', 'Keuangan', 'Kaprodi', 'Rektor'];
        return view('users.create', compact('levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'level' => 'required|in:Admin,Sarpras,Keuangan,Kaprodi,Rektor',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'Aktif';

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $levels = ['Admin', 'Sarpras', 'Keuangan', 'Kaprodi', 'Rektor'];
        return view('users.edit', compact('user', 'levels'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'level' => 'required|in:Admin,Sarpras,Keuangan,Kaprodi,Rektor',
            'status' => 'required|in:Aktif,Nonaktif',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diupdate!');
    }

    public function destroy(User $user)
    {
        if ($user->id === 1) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus user admin utama!');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus!');
    }
}