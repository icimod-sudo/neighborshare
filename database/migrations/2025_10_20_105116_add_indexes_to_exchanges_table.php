<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exchanges', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            $table->index(['from_user_id', 'status']);
            $table->index(['to_user_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('exchanges', function (Blueprint $table) {
            $table->dropIndex(['from_user_id', 'status']);
            $table->dropIndex(['to_user_id', 'status']);
            $table->dropIndex(['product_id', 'status']);
            $table->dropIndex(['created_at']);
        });
    }
};
