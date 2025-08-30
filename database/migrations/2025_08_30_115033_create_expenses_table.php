<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();             // เลขที่เอกสาร
            $table->string('title');                        // รายการ/หัวข้อ
            $table->string('category')->nullable();         // หมวด (เช่น วัตถุดิบ/ค่าแรง/ค่าสาธารณูปโภค)
            $table->decimal('amount', 10, 2);               // จำนวนเงิน (+ คือจ่ายออก)
            $table->date('spent_at');                       // วันที่จ่าย
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('expenses'); }
};
