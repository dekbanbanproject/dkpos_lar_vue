<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_in_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_in_id')->constrained('stock_ins')->cascadeOnDelete();

            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('qty');                     // จำนวนที่รับเข้า
            $table->decimal('cost', 10, 2)->nullable();         // ต้นทุนต่อหน่วย (ถ้ามี)
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('stock_in_items');
    }
};
