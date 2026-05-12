<?php

use App\Livewire\Admin\AppraisalManager;
use App\Livewire\Admin\AssetManager;
use App\Models\Appraisal;
use App\Models\CompanyAsset;
use Illuminate\Support\Facades\Route;

Route::get('/assets', AssetManager::class)->name('admin.assets')->middleware('feature.lock:assets,admin.assets.view,admin.dashboard')->can('viewAdminAny', CompanyAsset::class);
Route::get('/appraisals', AppraisalManager::class)->name('admin.appraisals')->middleware('feature.lock:appraisal,admin.appraisals.view,admin.dashboard')->can('viewAdminAny', Appraisal::class);
