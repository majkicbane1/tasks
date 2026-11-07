<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->index()->after('id');
            $table->string('google_id', 100)->nullable()->unique()->after('email');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->unsignedBigInteger('two_factor_last_step')->nullable();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->index()->after('id');
        });

        Schema::table('leave_days', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->index()->after('id');
        });

        // Everything that exists today belongs to the first firm (the original freelancer).
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Uizzard',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('role', 'super_admin')->update(['tenant_id' => $tenantId]);
        DB::table('clients')->update(['tenant_id' => $tenantId]);
        DB::table('leave_days')->update(['tenant_id' => $tenantId]);
        DB::table('users')->where('role', 'client')->update(['tenant_id' => $tenantId]);
        // accounts that already exist were created by an admin, so they count as verified
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);

        // one leave entry per day *per firm*
        Schema::table('leave_days', function (Blueprint $table) {
            $table->dropUnique('leave_days_date_unique');
            $table->unique(['tenant_id', 'date']);
        });

        // optional: bootstrap the platform owner from .env on the first migrate
        $email = env('PLATFORM_ADMIN_EMAIL');
        $password = env('PLATFORM_ADMIN_PASSWORD');
        if ($email && $password && ! DB::table('users')->where('email', $email)->exists()) {
            DB::table('users')->insert([
                'role' => 'platform_admin',
                'name' => 'Platform Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('leave_days', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'date']);
            $table->unique('date');
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropUnique(['google_id']);
            $table->dropColumn(['tenant_id', 'google_id', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'two_factor_last_step']);
        });
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('tenants');
    }
};
