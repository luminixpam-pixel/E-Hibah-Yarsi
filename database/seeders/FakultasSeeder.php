<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use Illuminate\Database\Seeder;

class FakultasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
{
    $data = [
        'Fakultas Kedokteran',
        'Fakultas Hukum',
        'Fakultas Ekonomi dan Bisnis',
        'Fakultas Teknologi Informasi',
        'Fakultas Psikologi',
        'Fakultas Kedokteran Gigi',
    ];

    foreach ($data as $nama) {
        // Gunakan nama_fakultas sebagai kunci pencarian agar tidak duplikat
        \App\Models\Fakultas::updateOrCreate(
            ['nama_fakultas' => $nama], // Cari berdasarkan nama
            ['nama_fakultas' => $nama]  // Jika tidak ada, buat dengan nama ini
        );
    }
}
}
