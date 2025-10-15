<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelian')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
            $table->integer('jumlah');
            $table->decimal('harga', 15, 2);
            $table->decimal('sub_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_pembelian');
    }
};
