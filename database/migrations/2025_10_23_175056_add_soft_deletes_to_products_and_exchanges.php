<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add soft delete to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes();
                $table->string('deleted_reason')->nullable()->after('deleted_at');
            }
        });

        // Add soft delete to exchanges table
        Schema::table('exchanges', function (Blueprint $table) {
            if (!Schema::hasColumn('exchanges', 'deleted_at')) {
                $table->softDeletes();
                $table->string('deleted_reason')->nullable()->after('deleted_at');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('deleted_reason');
        });

        Schema::table('exchanges', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('deleted_reason');
        });
    }
};
