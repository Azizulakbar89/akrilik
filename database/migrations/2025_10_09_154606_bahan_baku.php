<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('satuan');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('harga_jual', 15, 2);
            $table->integer('stok')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->integer('rop')->default(0);
            $table->integer('min')->default(0);
            $table->integer('max')->default(0);
            $table->integer('lead_time')->default(1);
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        // Tabel untuk menyimpan data penggunaan bahan baku
        Schema::create('penggunaan_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
            $table->integer('jumlah');
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('penggunaan_bahan_baku');
        Schema::dropIfExists('bahan_baku');
    }
};
