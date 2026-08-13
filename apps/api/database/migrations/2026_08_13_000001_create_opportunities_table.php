<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('status', 32);
            $table->string('priority', 16);
            $table->string('title', 200);
            $table->string('organization', 200);
            $table->string('source_url', 2048)->nullable();
            $table->string('location', 200)->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('deadline_at')->nullable();
            $table->string('deadline_precision', 16)->nullable();
            $table->string('deadline_timezone', 64)->nullable();
            $table->string('next_action', 500)->nullable();
            $table->timestampTz('next_action_at')->nullable();
            $table->timestampTz('archived_at')->nullable();
            $table->timestampsTz();

            $table->index(['owner_id', 'archived_at', 'updated_at']);
            $table->index(['owner_id', 'status']);
            $table->index(['owner_id', 'type']);
            $table->index(['owner_id', 'priority']);
            $table->index(['owner_id', 'deadline_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
