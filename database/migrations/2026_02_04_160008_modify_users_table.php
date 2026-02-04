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
        Schema::table('users', function (Blueprint $table) {
            // Add username column after id
            $table->string('username')->unique()->after('id');
            
            // Add role enum
            $table->enum('role', ['admin', 'prodi'])->default('admin')->after('password');
            
            // Add prodi_id for staff prodi (nullable)
            $table->foreignId('prodi_id')->nullable()->after('role')->constrained('prodi')->onDelete('set null');
            
            // Add is_active flag
            $table->boolean('is_active')->default(true)->after('prodi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropColumn(['username', 'role', 'prodi_id', 'is_active']);
        });
    }
};
