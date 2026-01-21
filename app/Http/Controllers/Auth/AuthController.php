<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // 1. Cek Login Lokal (Admin / User yang sudah di-override)
        $userLokal = User::where('username', $request->username)->first();
        if ($userLokal && $userLokal->password && Hash::check($request->password, $userLokal->password)) {
            Auth::login($userLokal);
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // 2. Cek Login LDAP
        try {
            if (Auth::attempt(['uid' => $request->username, 'password' => $request->password])) {
                $ldapUser = Auth::user();

                // Sinkronisasi data LDAP ke Database Lokal
                $user = User::updateOrCreate(
                    ['username' => $request->username],
                    [
                        'name'          => $ldapUser->displayname[0] ?? $request->username,
                        'email'         => $ldapUser->mail[0] ?? null,
                        'nidn'          => $ldapUser->description[0] ?? null,
                        'fakultas'      => $ldapUser->department[0] ?? null,
                        'program_studi' => $ldapUser->title[0] ?? null,
                        'jabatan'       => $ldapUser->physicaldeliveryofficename[0] ?? null,
                        'guid'          => $ldapUser->getConvertedGuid(),
                        'domain'        => 'default',
                        'role'          => $userLokal ? $userLokal->role : 'pengaju',
                    ]
                );

                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->intended('/dashboard');
            }
        } catch (\Exception $e) {
            // Log::error($e->getMessage());
        }

        return back()->withErrors(['username' => 'Kredensial salah atau server LDAP tidak merespon.']);
    }
public function adminUpdate(Request $request, $id)
{
    $user = User::findOrFail($id);
    $isAdmin = (auth()->user()->role === 'admin');

    if (!$isAdmin) {
        return back()->with('error', 'Hanya Admin yang dapat melakukan tindakan ini.');
    }

    // Validasi input
    $request->validate([
        'username' => 'required|string|max:255|unique:users,username,' . $id,
        'email'    => 'required|email|unique:users,email,' . $id,
        'nidn'     => 'nullable|string|max:50',
        'fakultas' => 'required|string',
        'password' => 'nullable|min:6', // Password opsional
    ]);

    // Update data dasar
    $user->username = $request->username;
    $user->email    = $request->email;
    $user->nidn     = $request->nidn;
    $user->fakultas = $request->fakultas;

    // Update password jika diisi oleh admin
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return back()->with('success', 'Akun ' . $user->username . ' berhasil diperbarui!');
}
  public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

}
