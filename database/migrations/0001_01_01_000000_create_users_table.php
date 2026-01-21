<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    // Buat tabel users
    Schema::create('users', function (Blueprint $table) {
        $table->id();

        // Kolom LDAP (Cukup di sini saja)
        $table->string('guid')->unique()->nullable();
        $table->string('domain')->nullable();
        $table->string('name');
        $table->string('username')->unique();
        $table->string('nidn')->nullable();
        $table->string('email')->unique()->nullable();
        $table->string('no_telepon')->nullable();
        $table->string('fakultas')->nullable();
        $table->string('program_studi')->nullable();
        $table->string('jabatan')->nullable();
       $table->string('role')->default('pengaju');
        $table->string('password')->nullable(); // Sudah nullable, benar untuk LDAP
        $table->timestamp('email_verified_at')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
