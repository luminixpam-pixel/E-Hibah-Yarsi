<?php

use Illuminate\Support\Facades\Route;
// Pisahkan AuthController karena namespacenya berbeda (App\Http\Controllers\Auth)
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Password;

use App\Http\Controllers\{
    DashboardController, ProposalController,
    ReviewerController, AdminController, NotificationController,
    CalendarController, ProposalReviewerController, LaporanKemajuanController,
    ProfileController, DokumenResmiController, AdminDocumentController, TemplateController, LaporanAkhirController
};
// Halaman utama langsung ke login jika belum login
// --- TARUH DI LUAR (Bisa diakses siapa saja) ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// --- TARUH DI DALAM (Hanya untuk yang sudah login) ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    // Route update profil
    Route::put('/admin/user/update/{id}', [AuthController::class, 'adminUpdate'])->name('admin.user.update');
});

/*
|--------------------------------------------------------------------------
| ROUTE DENGAN MIDDLEWARE AUTH (SEMUA USER LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /* --- DASHBOARD & PROFILE --- */

   Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'updateProfile'])->name('dashboard.updateProfile');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    /* --- MONITORING & KALENDER --- */
    Route::get('/monitoring-kalender', [CalendarController::class, 'index'])->name('monitoring.kalender');
    Route::get('/pengumuman-pendanaan', [ProposalController::class, 'pengumumanPendanaan'])->name('proposal.pengumuman');

    /* --- PROPOSAL ACTIONS (GENERAL) --- */
    Route::controller(ProposalController::class)->group(function () {
        Route::get('/proposal', 'index')->name('proposal.index');
        Route::get('/daftar-proposal', 'index')->name('monitoring.proposalDikirim');
        Route::get('/proposal/download/{id}', 'download')->name('proposal.download');
        Route::get('/proposal/{id}/edit', 'edit')->name('proposal.edit');
        Route::put('/proposal/{id}', 'update')->name('proposal.update');
        Route::delete('/proposal/{id}', 'destroy')->name('proposal.destroy');
        Route::get('/proposal/{id}/tinjau', 'tinjau')->name('proposal.tinjau');
        Route::patch('/proposal/{id}/keputusan', 'keputusan')->name('proposal.keputusan');

        Route::get('/proposal-disetujui', 'proposalDisetujui')->name('monitoring.proposalDisetujui');
        Route::get('/proposal-ditolak', 'proposalDitolak')->name('monitoring.proposalDitolak');
        Route::get('/proposal-selesai', 'reviewSelesai')->name('monitoring.reviewSelesai');
        Route::get('/proposal-direvisi', 'proposalDirevisi')->name('monitoring.proposalDirevisi');
        Route::get('/hasil-review', 'hasilRevisi')->name('monitoring.hasilRevisi');
        Route::get('/review/{review}/pdf', 'downloadReviewPdf')->name('review.pdf');
    });


    /* --- LAPORAN KEMAJUAN & AKHIR --- */
    Route::get('/laporan-kemajuan', [LaporanKemajuanController::class, 'index'])->name('laporan.kemajuan.index');
    Route::post('/laporan-kemajuan', [LaporanKemajuanController::class, 'store'])->name('laporan.kemajuan.store');
    Route::get('/laporan-kemajuan/download/{id}', [LaporanKemajuanController::class, 'downloadLaporan'])->name('laporan.kemajuan.download');

    Route::get('/laporan-akhir', [LaporanAkhirController::class, 'index'])->name('laporan.akhir.index');
    Route::post('/laporan-akhir/store', [LaporanAkhirController::class, 'store'])->name('laporan.akhir.store');
    Route::get('/laporan-akhir/download/{id}', [LaporanAkhirController::class, 'download'])->name('laporan.akhir.download');

    /* --- DOKUMEN --- */
    Route::get('/dokumen', [AdminDocumentController::class, 'userView'])->name('dokumen.user');
    Route::get('/dokumen/download/{id}', [AdminDocumentController::class, 'download'])->name('dokumen.download');
    Route::get('/dokumen-resmi/{id}/download', [DokumenResmiController::class, 'download'])->name('dokumen.download_resmi');

    /* --- NOTIFIKASI --- */
    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications/fetch', 'fetch')->name('notifications.fetch');
        Route::get('/notifications/count', 'count')->name('notifications.count');
        Route::get('/notifications/deadline-check', 'deadlineCheck')->name('notifications.deadlineCheck');
        Route::post('/notifications/mark-all-read', 'markAllAsRead')->name('notifications.markAllAsRead');
    });

    /* --- LAIN-LAIN --- */
    Route::get('/cek-kuota-dosen', [ProposalController::class, 'cekKuota'])->name('dosen.cek-kuota');
    Route::patch('/proposal/{id}/setujui', [ProposalController::class, 'setujui'])->name('proposal.setujui');
    Route::patch('/proposal/{id}/set-review', [ProposalController::class, 'setReview'])->name('proposal.set-review');

    /*
    |--------------------------------------------------------------------------
    | ROLE: ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/riwayat-dosen', [DashboardController::class, 'riwayatDosen'])->name('admin.riwayatDosen');
        Route::get('/riwayat-dosen/{id}', [DashboardController::class, 'detailDosen'])->name('admin.dosen.detail');
        Route::get('/timeline', fn() => view('timeline'))->name('timeline');
        Route::get('/monitoring-data', fn() => view('monitoring.index'))->name('monitoring.data');
        Route::get('/admin/hasil-review', [AdminController::class, 'hasilReview'])->name('admin.hasil-review');
        Route::post('/monitoring-kalender/periode', [CalendarController::class, 'updatePeriod'])->name('monitoring.kalender.periode');

        Route::put('/proposal/{proposal}/approve', [ProposalController::class, 'approveProposal'])->name('proposal.approve');
        Route::put('/proposal/{proposal}/reject', [ProposalController::class, 'rejectProposal'])->name('proposal.reject');

        Route::controller(ReviewerController::class)->group(function () {
            Route::get('/admin/reviewer', 'index')->name('admin.reviewer.index');
            Route::post('/admin/reviewer/{user}', 'setReviewer')->name('admin.reviewer.set');
            Route::post('/admin/reviewer/{user}/remove', 'removeReviewer')->name('reviewer.remove');
            Route::get('/admin/search-reviewer', 'searchReviewer')->name('admin.searchReviewer');
        });

        Route::post('/proposal/{proposal}/assign-reviewer', [ProposalReviewerController::class, 'assign'])->name('proposal.assignReviewer');
        Route::get('/admin/dokumen', [AdminDocumentController::class, 'index'])->name('admin.dokumen.index');
        Route::post('/admin/dokumen', [AdminDocumentController::class, 'store'])->name('admin.dokumen.store');
        Route::patch('/admin/dokumen/{id}/toggle-visibility', [AdminDocumentController::class, 'toggleVisibility'])->name('admin.dokumen.toggle');

        Route::post('/admin/template/upload', [TemplateController::class, 'store'])->name('admin.template.upload');
        Route::delete('/admin/template/{id}', [TemplateController::class, 'destroy'])->name('admin.template.destroy');
    });

    /* --- ROLE: PENGAJU / REVIEWER --- */
    Route::middleware(['role:pengaju,reviewer'])->group(function () {
        Route::get('/proposal/create', [ProposalController::class, 'create'])->name('proposal.create');
        Route::post('/proposal/store', [ProposalController::class, 'store'])->name('proposal.store');
    });

    Route::middleware(['role:admin,reviewer'])->group(function () {
        Route::get('/proposal-perlu-direview', [ProposalController::class, 'proposalPerluDireview'])->name('monitoring.proposalPerluDireview');
        Route::get('/proposal-sedang-direview', [ProposalController::class, 'proposalSedangDireview'])->name('monitoring.proposalSedangDireview');
    });

    Route::middleware(['role:reviewer'])->group(function () {
        Route::get('/reviewer/isi-review/{id}', [ReviewerController::class, 'isiReview'])->name('reviewer.isi-review');
        Route::post('/reviewer/isi-review/{id}', [ReviewerController::class, 'submitReview'])->name('review.simpan');
    });

    Route::post('/admin/update-role/{id}', [DashboardController::class, 'updateRole'])->name('admin.updateRole');


});
