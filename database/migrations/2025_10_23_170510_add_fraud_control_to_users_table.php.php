<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_fraud_control_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('deleted_reason')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamp('suspended_until')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->integer('strike_count')->default(0);
            $table->json('fraud_flags')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'deleted_reason',
                'deleted_by',
                'suspended_until',
                'suspension_reason',
                'strike_count',
                'fraud_flags'
            ]);
        });
    }
};
