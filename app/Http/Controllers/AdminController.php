<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User;
use App\Notifications\ProposalAssignedNotification;
use Illuminate\Support\Facades\Hash; // Tambahkan ini untuk hashing password
use Illuminate\Validation\Rule;       // Tambahkan ini untuk validasi unique

class AdminController extends Controller
{
    /**
     * Halaman hasil review
     */
    public function hasilReview()
    {
        $reviews = []; // contoh
        return view('admin.hasil-review', compact('reviews'));
    }

    /**
     * Halaman kalender
     */
    public function calendar()
    {
        return view('admin.calendar');
    }

    /**
     * ================================
     * ASSIGN REVIEWER + TENGGAT REVIEW
     * ================================
     */
    public function assignReviewer(Request $request, $id)
    {
        // 🔒 pastikan admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // ✅ validasi
        $request->validate([
            'reviewer_1'      => 'nullable|exists:users,id',
            'reviewer_2'      => 'nullable|exists:users,id|different:reviewer_1',
            'review_deadline' => 'required|date|after:now',
        ]);

        $proposal = Proposal::with('reviewers')->findOrFail($id);

        // kumpulkan reviewer (hindari null)
        $reviewers = array_filter([
            $request->reviewer_1,
            $request->reviewer_2,
        ]);

        // simpan reviewer (pivot)
        $proposal->reviewers()->sync($reviewers);

        // simpan tenggat & status
        $proposal->update([
            'review_deadline' => $request->review_deadline,
            'status'          => 'Perlu Direview',
        ]);

        // 🔔 kirim notifikasi ke reviewer
        $users = User::whereIn('id', $reviewers)->get();

        foreach ($users as $user) {
            $user->notify(new ProposalAssignedNotification($proposal));
        }

        return back()->with('success', 'Reviewer dan tenggat review berhasil ditetapkan.');
    }

    /**
     * ================================
     * MANAJEMEN USER (LDAP & LOKAL)
     * ================================
     */

    /**
     * Menampilkan daftar user
     */
    // app/Http/Controllers/DashboardController.php

    public function index() {
        $role = auth()->user()->role;

        // Ambil jumlah dokumen panduan yang aktif
        $templateCount = \App\Models\AdminDocument::where('is_visible', true)->count();

        // Pastikan variabel ini dikirim ke view dashboard
       // Sebutkan saja variabel apa saja yang mau dikirim ke view
    return view('dashboard', compact('templateCount', 'role'));

    }
    public function indexUsers()
    {
        if (auth()->user()->role !== 'admin') { abort(403); }

        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Update data user (Username & Password)
     */
    public function updateUser(Request $request, $id)
    {
        // 🔒 pastikan admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);

        // ✅ validasi
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'role'     => 'required|in:admin,reviewer,pengaju',
            'password' => 'nullable|min:8|confirmed', // 'confirmed' berarti butuh input password_confirmation
        ]);

        // Update data dasar
        $user->name = $request->name;
        $user->username = $request->username;
        $user->role = $request->role;

        // 🔑 Jika password diisi (untuk user lokal), update hashed passwordnya
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Data user berhasil diperbarui.');
    }

        public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'password' => 'nullable|min:6|confirmed', // password opsional, diisi jika ingin diubah
        ]);

        $user = \App\Models\User::findOrFail($id);
        $user->username = $request->username;

        // Hanya update password jika kolom diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Data user berhasil diperbarui!');
    }
}
