<?php

use App\Models\Appraisal;
use App\Models\CompanyAsset;

enterprise_livewire_route('/assets', 'admin.asset-manager')->name('admin.assets')->middleware('feature.lock:assets,admin.assets.view,admin.dashboard')->can('viewAdminAny', CompanyAsset::class);
enterprise_livewire_route('/appraisals', 'admin.appraisal-manager')->name('admin.appraisals')->middleware('feature.lock:appraisal,admin.appraisals.view,admin.dashboard')->can('viewAdminAny', Appraisal::class);
