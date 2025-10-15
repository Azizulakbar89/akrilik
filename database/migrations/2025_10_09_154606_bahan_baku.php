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
            $table->integer('safety_stock');
            $table->integer('rop');
            $table->integer('min');
            $table->integer('max');
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bahan_baku');
    }
};
