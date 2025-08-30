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
        Schema::create('recipe_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('recipe_id')->constrained();
            $t->foreignId('ingredient_id')->constrained();
            $t->decimal('qty_per_unit', 12, 3); // ต่อ 1 ชิ้น
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_items');
    }
};
