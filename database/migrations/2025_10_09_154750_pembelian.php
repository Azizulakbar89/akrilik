<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pembelian')->unique();
            $table->foreignId('supplier_id')->constrained('supplier')->onDelete('cascade');
            $table->decimal('total', 15, 2);
            $table->date('tanggal');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembelian');
    }
};
