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
        Schema::create('ingredients', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('sku')->nullable();
            $t->foreignId('unit_id')->constrained();
            $t->enum('cost_method', ['avg','last'])->default('avg');
            $t->decimal('current_cost', 10, 2)->default(0);
            $t->decimal('stock_qty', 12, 3)->default(0);
            $t->decimal('min_stock', 12, 3)->default(0);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
