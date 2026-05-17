<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\CustomFormSubmission;
use App\Models\CustomFormTemplate;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Support\CustomFormBuilderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class CustomFormManager extends Component
{
    use InteractsWithBanner;

    private const TABS = ['templates', 'submissions'];

    protected CustomFormBuilderService $forms;

    #[Url(history: true)]
    public string $activeTab = 'templates';

    public string $search = '';

    public string $templateCompanyId = '';

    public string $templateTitle = '';

    public string $templateCategory = 'general';

    public string $templateDescription = '';

    public string $fieldLines = "Nama Lokasi|text|required\nJenis Kunjungan|select|required|Audit,Instalasi,Follow-up\nCatatan|textarea|optional";

    public bool $automationEnabled = false;

    public string $automationProjectId = '';

    public string $automationTaskTitle = '';

    public string $automationTaskPriority = ProjectTask::PRIORITY_NORMAL;

    public function boot(CustomFormBuilderService $forms): void
    {
        Gate::authorize('viewCustomForms');

        $this->forms = $forms;
    }

    public function mount(): void
    {
        $this->normalizeActiveTab();

        $companyId = $this->defaultCompanyId();

        if ($companyId !== null) {
            $this->templateCompanyId = $companyId;
        }
    }

    public function updatedTemplateCompanyId(): void
    {
        $this->reset('automationProjectId');
    }

    public function updatedActiveTab(): void
    {
        $this->normalizeActiveTab();
    }

    public function createTemplate(): void
    {
        Gate::authorize('manageCustomForms');

        $validated = $this->validate([
            'templateCompanyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'templateTitle' => ['required', 'string', 'max:180'],
            'templateCategory' => ['required', 'string', 'max:80'],
            'templateDescription' => ['nullable', 'string', 'max:1000'],
            'fieldLines' => ['required', 'string', 'max:4000'],
            'automationEnabled' => ['boolean'],
            'automationProjectId' => [
                'nullable',
                'integer',
                Rule::exists('projects', 'id')->where('company_id', (int) $this->templateCompanyId),
            ],
            'automationTaskTitle' => ['nullable', 'string', 'max:180'],
            'automationTaskPriority' => ['required', Rule::in([ProjectTask::PRIORITY_LOW, ProjectTask::PRIORITY_NORMAL, ProjectTask::PRIORITY_HIGH])],
        ]);

        $this->forms->createTemplate(auth()->user(), [
            'company_id' => (int) $validated['templateCompanyId'],
            'title' => $validated['templateTitle'],
            'category' => $validated['templateCategory'],
            'description' => $validated['templateDescription'] ?: null,
            'field_lines' => $validated['fieldLines'],
            'automation_enabled' => $validated['automationEnabled'],
            'automation_project_id' => $validated['automationProjectId'] ?: null,
            'automation_task_title' => $validated['automationTaskTitle'] ?: null,
            'automation_task_priority' => $validated['automationTaskPriority'],
        ]);

        $this->reset(['templateTitle', 'templateCategory', 'templateDescription', 'automationEnabled', 'automationProjectId', 'automationTaskTitle']);
        $this->templateCategory = 'general';
        $this->automationTaskPriority = ProjectTask::PRIORITY_NORMAL;
        $this->banner(__('Custom form template created.'));
    }

    public function toggleTemplate(int $templateId): void
    {
        Gate::authorize('manageCustomForms');

        $template = CustomFormTemplate::query()->findOrFail($templateId);
        abort_unless($this->forms->canAccessCompany(auth()->user(), $template->company_id), 403);

        $template->forceFill(['is_active' => ! $template->is_active])->save();
    }

    public function render()
    {
        $user = auth()->user();
        $companyIds = $this->forms
            ->scopeCompanies(Company::query(), $user)
            ->pluck('id')
            ->all();

        $companies = Company::query()
            ->whereIn('id', $companyIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $templates = CustomFormTemplate::query()
            ->with(['company:id,name'])
            ->withCount('submissions')
            ->whereIn('company_id', $companyIds)
            ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $nested): void {
                $nested
                    ->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('category', 'like', '%'.$this->search.'%');
            }))
            ->latest()
            ->get();

        $projects = Project::query()
            ->whereIn('company_id', $companyIds)
            ->orderBy('name')
            ->get(['id', 'company_id', 'name']);
        $selectedTemplateCompanyId = $this->scopedCompanyId($companyIds, $this->templateCompanyId);

        $submissions = CustomFormSubmission::query()
            ->with(['template:id,title,category', 'submitter:id,name,email'])
            ->whereIn('company_id', $companyIds)
            ->latest()
            ->get();

        return view('livewire.admin.custom-form-manager', [
            'companies' => $companies,
            'templates' => $templates,
            'projects' => $projects,
            'automationProjectOptions' => $selectedTemplateCompanyId === null
                ? $projects
                : $projects->where('company_id', $selectedTemplateCompanyId)->values(),
            'submissions' => $submissions,
            'fieldTypes' => $this->forms->fieldTypes(),
            'canManage' => $user->can('manageCustomForms'),
        ]);
    }

    private function defaultCompanyId(): ?string
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $companyId = $this->forms
            ->scopeCompanies(Company::query(), $user)
            ->orderBy('name')
            ->value('id');

        return $companyId === null ? null : (string) $companyId;
    }

    /**
     * @param  list<int|string>  $companyIds
     */
    private function scopedCompanyId(array $companyIds, string $companyId): ?int
    {
        if ($companyId === '') {
            return null;
        }

        $companyId = (int) $companyId;

        return in_array($companyId, array_map('intval', $companyIds), true) ? $companyId : null;
    }

    private function normalizeActiveTab(): void
    {
        if (! in_array($this->activeTab, self::TABS, true)) {
            $this->activeTab = 'templates';
        }
    }
}
