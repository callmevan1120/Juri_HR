<?php

namespace App\Policies;

use App\Helpers\Editions;
use App\Models\CompanyAsset;
use App\Models\User;
use App\Support\MultiCompanyService;

class CompanyAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return ! Editions::assetLocked();
    }

    public function viewAdminAny(User $user): bool
    {
        return ! Editions::assetLocked() && $user->can('viewAdminAssets');
    }

    public function view(User $user, CompanyAsset $companyAsset): bool
    {
        if (! $this->sameCompany($user, $companyAsset)) {
            return false;
        }

        return ! Editions::assetLocked()
            && ($user->can('viewAdminAssets') || $companyAsset->user_id === $user->id);
    }

    public function returnAsset(User $user, CompanyAsset $companyAsset): bool
    {
        if (! $this->sameCompany($user, $companyAsset)) {
            return false;
        }

        return ! Editions::assetLocked()
            && $companyAsset->user_id === $user->id
            && $companyAsset->status === 'assigned';
    }

    protected function sameCompany(User $actor, CompanyAsset $companyAsset): bool
    {
        if ($companyAsset->user_id === null) {
            return true;
        }

        $companyAsset->loadMissing('user');

        return $companyAsset->user !== null
            && app(MultiCompanyService::class)->canAccessUser($actor, $companyAsset->user);
    }
}
