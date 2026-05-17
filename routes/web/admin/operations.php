<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/operations', 'admin.operational-workspace')
    ->name('admin.operations')
    ->can('viewOperationsWorkspace');

Route::livewire('/commercial', 'admin.commercial-workspace')
    ->name('admin.commercial')
    ->can('viewCommercialWorkspace');

Route::livewire('/accounting', 'admin.accounting-workspace')
    ->name('admin.accounting')
    ->can('viewAccountingWorkspace');

Route::livewire('/custom-forms', 'admin.custom-form-manager')
    ->name('admin.custom-forms')
    ->can('viewCustomForms');
