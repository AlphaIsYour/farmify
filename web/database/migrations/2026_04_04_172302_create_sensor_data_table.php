<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->float('soil_moisture');  // 0-100 %
            $table->float('temperature')->nullable();
            $table->float('humidity')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
        });

        Schema::create('commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_client_id')->constrained()->cascadeOnDelete();
            $table->enum('command_type', ['start_pump', 'stop_pump']);
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->enum('source', ['manual', 'auto'])->default('manual');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('executed_at')->nullable();
        });

        Schema::create('pump_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('status', ['idle', 'running', 'stopped'])->default('idle');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('command_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('pump_status');
        Schema::dropIfExists('commands');
        Schema::dropIfExists('sensor_data');
    }
};