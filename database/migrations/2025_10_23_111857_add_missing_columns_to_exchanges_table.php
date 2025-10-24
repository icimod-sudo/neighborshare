<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exchanges', function (Blueprint $table) {
            // Add requested_quantity column if it doesn't exist
            if (!Schema::hasColumn('exchanges', 'requested_quantity')) {
                $table->decimal('requested_quantity', 8, 2)->default(1)->after('product_id');
            }

            // Add contact_info column if it doesn't exist
            if (!Schema::hasColumn('exchanges', 'contact_info')) {
                $table->string('contact_info', 255)->nullable()->after('message');
            }
        });
    }

    public function down()
    {
        Schema::table('exchanges', function (Blueprint $table) {
            // Remove columns if they exist
            if (Schema::hasColumn('exchanges', 'requested_quantity')) {
                $table->dropColumn('requested_quantity');
            }

            if (Schema::hasColumn('exchanges', 'contact_info')) {
                $table->dropColumn('contact_info');
            }
        });
    }
};
