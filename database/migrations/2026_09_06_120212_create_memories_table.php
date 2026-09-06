<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('memories', function (Blueprint $table) {
            $table->id();

            // Every memory belongs to one APONWORKS user.
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // note, diary, task, document, photo, voice, etc.
            $table->string('type')->default('note');

            $table->string('title')->nullable();

            // Original information entered by the user.
            $table->text('description');

            // Where the memory came from.
            $table->string('source_type')->default('manual');

            // Language of the original content.
            $table->string('language', 20)->nullable();

            // active, completed, archived, etc.
            $table->string('status')->default('active');

            // Important dates.
            $table->dateTime('occurred_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('expiry_at')->nullable();

            // Optional money information.
            $table->string('currency_code', 3)->nullable();
            $table->decimal('amount', 18, 2)->nullable();

            // Original files can remain on the user's device.
            $table->text('local_file_reference')->nullable();

            // Reserved for later AI processing.
            $table->text('ai_summary')->nullable();
            $table->decimal('ai_confidence', 5, 4)->nullable();

            // Important AI-extracted facts can require confirmation.
            $table->boolean('user_confirmed')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'due_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memories');
    }
};