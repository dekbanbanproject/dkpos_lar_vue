<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('order_revisions', function (Blueprint $t) {
      $t->id();
      $t->foreignId('order_id')->constrained()->cascadeOnDelete();
      $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
      $t->text('reason')->nullable();
      $t->json('before');  // สถานะก่อนแก้ (items,total,...)
      $t->json('after');   // สถานะหลังแก้
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('order_revisions');
  }
};
