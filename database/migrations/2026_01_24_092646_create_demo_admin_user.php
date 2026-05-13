<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin.demo@pandanteknik.com'],
            [
                'id' => (string) str(Str::ulid())->lower(),
                'nip' => '0000000000000001',
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'group' => 'admin',
                'email_verified_at' => now(),
                'phone' => '081234567890',
                'gender' => 'male',
                'address' => 'Demo Address, Jakarta',
                'city' => 'Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('email', 'admin.demo@pandanteknik.com')->delete();
    }
};
