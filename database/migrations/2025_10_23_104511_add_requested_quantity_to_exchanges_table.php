<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exchanges', function (Blueprint $table) {
            $table->decimal('requested_quantity', 8, 2)->default(1)->after('product_id');
        });
    }

    public function down()
    {
        Schema::table('exchanges', function (Blueprint $table) {
            $table->dropColumn('requested_quantity');
        });
    }
};
