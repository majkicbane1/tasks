<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_entries', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('payment_id');
            $table->timestamp('invoiced_at')->nullable()->after('visible_to_client');
            $table->timestamp('paid_at')->nullable()->after('invoiced_at');
        });

        DB::table('work_entries')->orderBy('id')->get(['id'])->each(function ($entry, $index): void {
            DB::table('work_entries')->where('id', $entry->id)->update([
                'sort_order' => ($index + 1) * 10,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
            $table->dropColumn(['sort_order', 'invoiced_at', 'paid_at']);
        });
    }
};
