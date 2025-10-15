<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('satuan');
            $table->decimal('harga', 15, 2);
            $table->integer('stok')->default(0);
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('produk');
    }
};
