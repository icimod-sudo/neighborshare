<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // login, logout, product_view, product_create, exchange_request, etc.
            $table->string('description');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->json('metadata')->nullable(); // Additional data like product_id, exchange_id, etc.
            $table->timestamp('performed_at')->useCurrent();

            $table->index(['user_id', 'performed_at']);
            $table->index('type');
            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
