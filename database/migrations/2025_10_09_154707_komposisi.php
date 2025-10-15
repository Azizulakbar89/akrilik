<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('komposisi_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->integer('jumlah');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('komposisi_bahan_baku');
    }
};
