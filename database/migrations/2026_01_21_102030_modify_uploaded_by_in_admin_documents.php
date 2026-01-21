<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('admin_documents', function (Blueprint $table) {
            // Mengubah INT menjadi STRING (VARCHAR) agar bisa menampung 'admin'
            $table->string('uploaded_by')->change();
        });
    }

    public function down()
    {
        Schema::table('admin_documents', function (Blueprint $table) {
            $table->integer('uploaded_by')->change();
        });
    }
};
