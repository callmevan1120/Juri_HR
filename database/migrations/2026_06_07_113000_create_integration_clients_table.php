<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $permission = 'admin.api_integrations.manage';

    public function up(): void
    {
        Schema::create('integration_clients', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('api_key_hash', 64)->unique();
            $table->text('secret_encrypted');
            $table->json('abilities');
            $table->json('allowed_sources')->nullable();
            $table->json('allowed_ips')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['revoked_at', 'expires_at']);
        });

        Schema::table('integration_attendance_events', function (Blueprint $table): void {
            $table->foreignUlid('integration_client_id')
                ->nullable()
                ->after('id')
                ->constrained('integration_clients')
                ->nullOnDelete();
        });

        $this->grantPermission();
    }

    public function down(): void
    {
        Schema::table('integration_attendance_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('integration_client_id');
        });

        Schema::dropIfExists('integration_clients');

        $this->removePermission();
    }

    private function grantPermission(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roles = DB::table('roles')
            ->whereIn('slug', ['super_admin', 'admin', 'it'])
            ->get(['id', 'permissions']);

        foreach ($roles as $role) {
            $permissions = $this->decodePermissions($role->permissions);
            $permissions[] = $this->permission;

            DB::table('roles')
                ->where('id', $role->id)
                ->update([
                    'permissions' => json_encode(array_values(array_unique($permissions)), JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
        }
    }

    private function removePermission(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roles = DB::table('roles')
            ->whereIn('slug', ['admin', 'it'])
            ->get(['id', 'permissions']);

        foreach ($roles as $role) {
            $permissions = array_values(array_filter(
                $this->decodePermissions($role->permissions),
                fn (string $permission): bool => $permission !== $this->permission,
            ));

            DB::table('roles')
                ->where('id', $role->id)
                ->update([
                    'permissions' => json_encode($permissions, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
        }
    }

    private function decodePermissions(?string $encodedPermissions): array
    {
        if (! $encodedPermissions) {
            return [];
        }

        $permissions = json_decode($encodedPermissions, true);

        if (! is_array($permissions)) {
            return [];
        }

        return array_values(array_filter(
            $permissions,
            fn ($permission): bool => is_string($permission) && $permission !== '',
        ));
    }
};
