<?php

use App\Models\User;
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
        // Delete old demo user
        DB::table('users')->where('email', 'admin.demo@pandanteknik.com')->delete();

        // Create new Demo Admin
        DB::table('users')->updateOrInsert(
            ['email' => 'admin123@paspapan.com'],
            [
                'id' => (string) str(Str::ulid())->lower(),
                'nip' => '0000000000000001',
                'name' => 'Demo Admin',
                'password' => Hash::make('12345678'),
                'group' => 'admin',
                'email_verified_at' => now(),
                'phone' => '081234567801',
                'gender' => 'male',
                'address' => 'Demo Address Admin',
                'city' => 'Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create new Demo User
        DB::table('users')->updateOrInsert(
            ['email' => 'user123@paspapan.com'],
            [
                'id' => (string) str(Str::ulid())->lower(),
                'nip' => '0000000000000002',
                'name' => 'Demo User',
                'password' => Hash::make('12345678'),
                'group' => 'user',
                'email_verified_at' => now(),
                'phone' => '081234567802',
                'gender' => 'male',
                'address' => 'Demo Address User',
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
        DB::table('users')->whereIn('email', ['admin123@paspapan.com', 'user123@paspapan.com'])->delete();
    }
};
