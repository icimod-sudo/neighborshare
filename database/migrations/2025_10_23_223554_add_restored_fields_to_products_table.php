<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('restored_at')->nullable()->after('deleted_reason');
            $table->foreignId('restored_by')->nullable()->after('restored_at')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['restored_at', 'restored_by']);
        });
    }
};
