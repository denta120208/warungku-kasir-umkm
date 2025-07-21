<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('product_name');
            $table->index('user_id');
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->index('customer_name');
            $table->index('status');
            $table->index('due_date');
            $table->index('user_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('debt_id');
            $table->index('payment_method');
            $table->index('user_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('category');
            $table->index('expense_date');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'product_name', 'user_id']);
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->dropIndex(['customer_name', 'status', 'due_date', 'user_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'debt_id', 'payment_method', 'user_id']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'category', 'expense_date', 'user_id']);
        });
    }
};
