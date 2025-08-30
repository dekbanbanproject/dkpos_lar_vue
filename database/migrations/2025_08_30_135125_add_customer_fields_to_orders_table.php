<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders','customer_name'))    $table->string('customer_name')->nullable()->after('user_id');
            if (!Schema::hasColumn('orders','customer_phone'))   $table->string('customer_phone',30)->nullable()->after('customer_name');
            if (!Schema::hasColumn('orders','customer_address')) $table->text('customer_address')->nullable()->after('customer_phone');
            if (Schema::hasColumn('orders','user_id')) { $table->foreignId('user_id')->nullable()->change(); }
        });
    }
    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders','customer_address')) $table->dropColumn('customer_address');
            if (Schema::hasColumn('orders','customer_phone'))   $table->dropColumn('customer_phone');
            if (Schema::hasColumn('orders','customer_name'))    $table->dropColumn('customer_name');
        });
    }
};
