<?php

use App\Livewire\Admin\Finance\CashAdvanceManager;
use App\Livewire\Admin\ReimbursementManager;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\Route;

Route::get('/reimbursements', ReimbursementManager::class)->name('admin.reimbursements')->can('viewAdminAny', Reimbursement::class);
Route::get('/manage-kasbon', CashAdvanceManager::class)->name('admin.manage-kasbon')->middleware('feature.lock:cash_advance,admin.cash_advances.manage,admin.dashboard')->can('manageCashAdvances');
