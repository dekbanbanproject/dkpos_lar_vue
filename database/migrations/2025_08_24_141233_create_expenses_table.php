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
        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->enum('mov_type', ['purchase','sale','adjustment']);
            $t->unsignedBigInteger('ref_id')->nullable(); // purchase_id หรือ sale_id
            $t->foreignId('ingredient_id')->constrained();
            $t->decimal('qty_in', 12, 3)->default(0);
            $t->decimal('qty_out', 12, 3)->default(0);
            $t->decimal('unit_cost', 10, 2)->default(0); // ต้นทุนที่ใช้บันทึก
            $t->timestamps();
            $t->index(['mov_type','ref_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
