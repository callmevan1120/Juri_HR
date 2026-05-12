<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MultiCompanyService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function createCompany(string $name, ?User $owner = null, array $metadata = []): Company
    {
        return DB::transaction(function () use ($name, $owner, $metadata): Company {
            $company = Company::query()->create([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'status' => Company::STATUS_ACTIVE,
                'metadata' => $metadata,
            ]);

            if ($owner !== null) {
                $this->assignUser($owner, $company);
            }

            return $company;
        });
    }

    public function assignUser(User $user, Company $company): User
    {
        $user->forceFill(['company_id' => $company->id])->save();

        return $user->fresh();
    }

    public function guardUserQuery(Builder $query, User $actor): Builder
    {
        if ($actor->isSuperadmin || $actor->company_id === null) {
            return $query;
        }

        return $query->where('company_id', $actor->company_id);
    }

    public function canAccessUser(User $actor, User $target): bool
    {
        return $actor->isSuperadmin
            || $actor->company_id === null
            || $target->company_id === null
            || (int) $actor->company_id === (int) $target->company_id;
    }

    public function suspend(Company $company): Company
    {
        $company->forceFill(['status' => Company::STATUS_SUSPENDED])->save();

        return $company->fresh();
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $counter = 2;

        while (Company::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
