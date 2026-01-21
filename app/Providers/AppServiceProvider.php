<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// TAMBAHKAN 3 BARIS DI BAWAH INI:
use Illuminate\Support\Facades\View;
use App\Models\Fakultas;
use App\Models\User;
use App\Models\Template;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
  public function boot(): void
    {
        // Gunakan View Composer
        view()->composer('*', function ($view) {

            // 1. Cek tabel fakultas
            if (\Illuminate\Support\Facades\Schema::hasTable('fakultas')) {
                $view->with('list_fakultas', Fakultas::all());
            }

            // 2. Cek tabel users
            if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                $view->with('all_dosen', User::whereIn('role', ['reviewer', 'pengaju'])->get());
            }

            // 3. Cek tabel templates
            if (\Illuminate\Support\Facades\Schema::hasTable('templates')) {
                $view->with('template_kemajuan', Template::where('jenis', 'laporan_kemajuan')->first());
                $view->with('template_akhir', Template::where('jenis', 'laporan_akhir')->first());
            }
        });
    }
}
