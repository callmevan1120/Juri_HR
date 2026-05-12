<?php

use App\Http\Controllers\Admin\Employees\EmployeeController;
use App\Http\Controllers\User\EmployeeDocumentDownloadController;
use App\Livewire\Admin\DocumentTemplateManager;
use App\Livewire\Admin\EmployeeDocumentRequestManager;
use App\Livewire\Admin\HrChecklistManager;
use App\Models\EmployeeDocumentRequest;
use App\Models\HrChecklistCase;
use Illuminate\Support\Facades\Route;

Route::resource('/employees', EmployeeController::class)
    ->only(['index'])
    ->middleware('can:viewEmployees')
    ->names(['index' => 'admin.employees']);

Route::get('/hr-checklists', HrChecklistManager::class)
    ->name('admin.hr-checklists')
    ->can('viewAny', HrChecklistCase::class);

Route::get('/document-requests', EmployeeDocumentRequestManager::class)->name('admin.document-requests')->can('viewAdminAny', EmployeeDocumentRequest::class);
Route::get('/document-templates', DocumentTemplateManager::class)->name('admin.document-templates')->can('manageDocumentTemplates');
Route::redirect('/document-templates/library', '/admin/document-templates')
    ->name('admin.document-templates.library')
    ->middleware('can:manageDocumentTemplates');
Route::get('/document-requests/{documentRequest}/download', [EmployeeDocumentDownloadController::class, 'generated'])
    ->name('admin.document-requests.download')
    ->can('download', 'documentRequest');
Route::get('/document-requests/{documentRequest}/uploaded', [EmployeeDocumentDownloadController::class, 'uploaded'])
    ->name('admin.document-requests.uploaded')
    ->can('downloadUpload', 'documentRequest');
