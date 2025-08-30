<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // กันเผื่อเคยมีคอลัมน์อยู่แล้ว จะได้ไม่ error ซ้ำ
            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')
                      ->nullable()
                      ->constrained()     // อ้างถึงตาราง categories (id)
                      ->nullOnDelete();   // ลบหมวด → เซ็ตเป็น NULL
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'category_id')) {
                // ลบ FK + คอลัมน์
                $table->dropConstrainedForeignId('category_id');
                // หรือถ้าบางเครื่องร้องเรื่องชื่อ constraint ใช้:
                // $table->dropForeign(['category_id']);
                // $table->dropColumn('category_id');
            }
        });
    }
};
