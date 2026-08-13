<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignUuid('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->json('changed_fields')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['opportunity_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_events');
    }
};
