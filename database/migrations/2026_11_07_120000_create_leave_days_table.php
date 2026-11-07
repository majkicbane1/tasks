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
        Schema::create('leave_days', function (Blueprint $table) {
            $table->id();
            // one entry per calendar day; adding the same day again updates it
            $table->date('date')->unique();
            // share of the day: 1.00 = full day, 0.50 = half day, 0.25 = quarter day
            $table->decimal('portion', 3, 2)->default(1);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_days');
    }
};
