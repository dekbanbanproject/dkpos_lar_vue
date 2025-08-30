<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();                 // เลขที่เอกสาร
            $table->string('supplier_name')->nullable();        // ผู้จำหน่าย (ถ้ามี)
            $table->timestamp('received_at')->useCurrent();     // วันที่รับของ
            $table->text('note')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('stock_ins');
    }
};
