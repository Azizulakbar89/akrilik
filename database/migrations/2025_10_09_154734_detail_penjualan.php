<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->onDelete('cascade');
            $table->foreignId('produk_id')->nullable()->constrained('produk')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->nullable()->constrained('bahan_baku')->onDelete('cascade');
            $table->string('nama_produk');
            $table->enum('jenis_item', ['produk', 'bahan_baku']);
            $table->integer('jumlah');
            $table->decimal('harga_sat', 15, 2);
            $table->decimal('sub_total', 15, 2);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql' && version_compare(DB::select('SELECT VERSION()')[0]->{'VERSION()'}, '8.0.16', '>=')) {
            DB::statement('ALTER TABLE detail_penjualan ADD CONSTRAINT check_single_id CHECK ((produk_id IS NOT NULL AND bahan_baku_id IS NULL) OR (produk_id IS NULL AND bahan_baku_id IS NOT NULL))');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE detail_penjualan ADD CONSTRAINT check_single_id CHECK ((produk_id IS NOT NULL AND bahan_baku_id IS NULL) OR (produk_id IS NULL AND bahan_baku_id IS NOT NULL))');
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE detail_penjualan DROP CONSTRAINT IF EXISTS check_single_id');
        }
        Schema::dropIfExists('detail_penjualan');
    }
};
