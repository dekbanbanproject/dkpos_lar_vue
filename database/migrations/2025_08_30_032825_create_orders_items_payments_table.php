<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void
{
Schema::create('orders', function (Blueprint $table) {
$table->id();
$table->string('order_no')->unique();
$table->decimal('sub_total', 10, 2)->default(0);
$table->decimal('discount', 10, 2)->default(0);
$table->decimal('total', 10, 2)->default(0);
$table->decimal('paid', 10, 2)->default(0);
$table->decimal('change', 10, 2)->default(0);
$table->string('payment_method')->default('cash');
$table->string('status')->default('paid');
$table->string('customer_name')->nullable();
$table->timestamps();
});


Schema::create('order_items', function (Blueprint $table) {
$table->id();
$table->foreignId('order_id')->constrained()->cascadeOnDelete();
$table->foreignId('product_id')->constrained()->cascadeOnDelete();
$table->string('name');
$table->decimal('price', 10, 2);
$table->integer('qty');
$table->decimal('total', 10, 2);
$table->timestamps();
});


Schema::create('payments', function (Blueprint $table) {
$table->id();
$table->foreignId('order_id')->constrained()->cascadeOnDelete();
$table->decimal('amount', 10, 2);
$table->string('method');
$table->string('ref')->nullable();
$table->timestamps();
});
}


public function down(): void
{
Schema::dropIfExists('payments');
Schema::dropIfExists('order_items');
Schema::dropIfExists('orders');
}
};