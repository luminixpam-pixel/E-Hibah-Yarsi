<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   // database/seeders/DatabaseSeeder.php
public function run(): void
{
    // Akun Admin Lokal (PENTING)
    \App\Models\User::updateOrCreate(
        ['username' => 'admin'],
        [
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
        ]
    );

    // Jalankan Sinkronisasi LDAP
    //$this->call(LdapDosenSeeder::class);
}
}
