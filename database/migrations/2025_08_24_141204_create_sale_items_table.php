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
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->integer('qty');
            $t->decimal('price', 10, 2); // ราคาขายต่อชิ้น ณ ตอนขาย
            $t->decimal('line_total', 12, 2); // qty * price
            $t->decimal('cogs_at_sale', 10, 2)->default(0); // ต้นทุนต่อชิ้น ณ ตอนขาย
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
