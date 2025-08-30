<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders','status'))
                $table->string('status',20)->default('awaiting_payment')->after('total'); // awaiting_payment|paid|cancelled
            if (Schema::hasColumn('orders','user_id'))
                $table->foreignId('user_id')->nullable()->change();

            if (!Schema::hasColumn('orders','payment_method'))
                $table->string('payment_method',20)->nullable()->after('status');
            if (!Schema::hasColumn('orders','payment_ref'))
                $table->string('payment_ref',64)->nullable()->after('payment_method');
            if (!Schema::hasColumn('orders','payment_amount'))
                $table->decimal('payment_amount',10,2)->nullable()->after('payment_ref');
            if (!Schema::hasColumn('orders','payment_slip_path'))
                $table->string('payment_slip_path')->nullable()->after('payment_amount');
            if (!Schema::hasColumn('orders','paid_at'))
                $table->timestamp('paid_at')->nullable()->after('payment_slip_path');

            if (!Schema::hasColumn('orders','customer_name'))
                $table->string('customer_name')->nullable()->after('user_id');
            if (!Schema::hasColumn('orders','customer_phone'))
                $table->string('customer_phone',30)->nullable()->after('customer_name');
            if (!Schema::hasColumn('orders','customer_address'))
                $table->text('customer_address')->nullable()->after('customer_phone');
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $drop = ['status','payment_method','payment_ref','payment_amount','payment_slip_path','paid_at','customer_name','customer_phone','customer_address'];
            foreach ($drop as $col) if (Schema::hasColumn('orders',$col)) $table->dropColumn($col);
        });
    }
};
