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
        Schema::create('work_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->date('worked_on')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('hours', 8, 2)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('billable')->index();
            $table->boolean('visible_to_client')->default(true);
            $table->text('internal_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_entries');
    }
};
