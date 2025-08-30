<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // ลบ FK เดิมที่ผิด (ชื่อทั่วไปจะเป็น order_items_product_id_foreign)
            $table->dropForeign(['product_id']);

            // ให้แน่ใจว่า type ตรงกับ products.id (unsignedBigInteger)
            $table->unsignedBigInteger('product_id')->change();

            // สร้าง FK ใหม่ให้ถูกต้อง
            $table->foreign('product_id')
                  ->references('id')->on('products')   // ✅ ชี้ไปที่ products
                  ->restrictOnDelete();                // หรือ ->cascadeOnDelete() ตามนโยบายที่ต้องการ
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            // เผื่อย้อนกลับ (ไม่แนะนำให้ใช้ชื่อผิดอีกแล้ว) — ใส่กลับแบบถูกไว้ก็ได้
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->restrictOnDelete();
        });
    }
};
