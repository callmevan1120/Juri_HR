<?php

namespace App\Support;

use App\Models\EmployeeDocumentTemplate;
use App\Models\EmployeeDocumentType;
use App\Models\User;
use Illuminate\Support\Collection;

class DocumentTemplateMarketplaceService
{
    /**
     * @return Collection<int, EmployeeDocumentTemplate>
     */
    public function published(?string $category = null): Collection
    {
        return EmployeeDocumentTemplate::query()
            ->with('documentType')
            ->where('is_marketplace', true)
            ->where('is_active', true)
            ->when($category, fn ($query) => $query->where('marketplace_category', $category))
            ->orderBy('marketplace_category')
            ->orderBy('name')
            ->get();
    }

    public function install(EmployeeDocumentTemplate $marketplaceTemplate, User $actor, ?string $name = null): EmployeeDocumentTemplate
    {
        if (! $marketplaceTemplate->is_marketplace) {
            throw new \InvalidArgumentException('Only marketplace templates can be installed.');
        }

        return EmployeeDocumentTemplate::query()->create([
            'document_type_id' => $marketplaceTemplate->document_type_id,
            'name' => $name ?: $marketplaceTemplate->name,
            'paper_size' => $marketplaceTemplate->paper_size,
            'orientation' => $marketplaceTemplate->orientation,
            'body' => $marketplaceTemplate->body,
            'footer' => $marketplaceTemplate->footer,
            'layout_options' => $marketplaceTemplate->layout_options,
            'is_active' => true,
            'is_marketplace' => false,
            'source_template_id' => $marketplaceTemplate->id,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    /**
     * @return Collection<int, EmployeeDocumentTemplate>
     */
    public function seedDefaultTemplates(): Collection
    {
        return collect($this->defaultTemplates())
            ->map(function (array $template): EmployeeDocumentTemplate {
                $documentType = EmployeeDocumentType::query()->firstOrCreate(
                    ['code' => $template['type_code']],
                    [
                        'name' => $template['type_name'],
                        'category' => 'hr',
                        'description' => $template['type_description'],
                        'is_active' => true,
                        'employee_requestable' => false,
                        'admin_requestable' => true,
                        'auto_generate_enabled' => true,
                    ],
                );

                return EmployeeDocumentTemplate::query()->updateOrCreate(
                    ['marketplace_slug' => $template['slug']],
                    [
                        'document_type_id' => $documentType->id,
                        'name' => $template['name'],
                        'paper_size' => 'a4',
                        'orientation' => 'portrait',
                        'body' => $template['body'],
                        'footer' => '{{ company_name }}',
                        'layout_options' => ['show_logo' => true, 'show_accents' => true],
                        'is_active' => true,
                        'is_marketplace' => true,
                        'marketplace_category' => $template['category'],
                        'marketplace_tags' => $template['tags'],
                        'published_at' => now(),
                    ],
                );
            });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function defaultTemplates(): array
    {
        return [
            [
                'slug' => 'indonesia-employment-contract',
                'name' => 'Employment Contract Template',
                'category' => 'contract',
                'tags' => ['contract', 'onboarding', 'indonesia'],
                'type_code' => 'employment_contract',
                'type_name' => 'Employment Contract',
                'type_description' => 'Reusable employee contract template.',
                'body' => '<h2>Employment Contract</h2><p>This agreement is made between {{ company_name }} and {{ employee_name }} for the position of {{ job_title }}.</p><p>Start date: {{ start_date }}.</p>',
            ],
            [
                'slug' => 'assignment-letter',
                'name' => 'Assignment Letter Template',
                'category' => 'assignment',
                'tags' => ['assignment', 'field-work'],
                'type_code' => 'assignment_letter',
                'type_name' => 'Assignment Letter',
                'type_description' => 'Template for employee assignment letters.',
                'body' => '<h2>Assignment Letter</h2><p>{{ employee_name }} is assigned to {{ assignment_location }} from {{ start_date }} to {{ end_date }}.</p>',
            ],
            [
                'slug' => 'warning-letter',
                'name' => 'Warning Letter Template',
                'category' => 'disciplinary',
                'tags' => ['disciplinary', 'warning'],
                'type_code' => 'warning_letter',
                'type_name' => 'Warning Letter',
                'type_description' => 'Template for HR warning letters.',
                'body' => '<h2>Warning Letter</h2><p>This letter records a formal warning for {{ employee_name }} regarding {{ incident_summary }}.</p>',
            ],
        ];
    }
}
