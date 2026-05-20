<?php

namespace App\Support;

use Illuminate\Support\Collection;

class FeatureMaturityMatrix
{
    /**
     * @return Collection<string, array<string, mixed>>
     */
    public function modules(): Collection
    {
        return collect(config('feature_maturity.modules', []))
            ->map(function (array $module, string $key): array {
                $evidence = collect($module['evidence'] ?? [])
                    ->map(fn (string $path): array => [
                        'path' => $path,
                        'exists' => is_file(base_path($path)),
                    ])
                    ->values()
                    ->all();

                return [
                    'key' => $key,
                    'label' => (string) ($module['label'] ?? $key),
                    'score' => (int) ($module['score'] ?? 0),
                    'weight' => (int) ($module['weight'] ?? 1),
                    'status' => (string) ($module['status'] ?? 'unknown'),
                    'evidence' => $evidence,
                    'missing_evidence' => collect($evidence)
                        ->where('exists', false)
                        ->pluck('path')
                        ->values()
                        ->all(),
                    'gaps' => array_values($module['gaps'] ?? []),
                ];
            });
    }

    /**
     * @return array{score:int,target:int,ready:bool,production_ready:int,release_candidate:int,foundation:int,not_release_ready:int,missing_evidence:array<int, string>,modules:array<int, array<string, mixed>>}
     */
    public function summary(): array
    {
        $modules = $this->modules();
        $weightedScore = $modules->sum(fn (array $module): int => $module['score'] * max(1, $module['weight']));
        $totalWeight = max(1, $modules->sum(fn (array $module): int => max(1, $module['weight'])));
        $score = (int) round($weightedScore / $totalWeight);
        $target = (int) config('feature_maturity.target_score', 95);
        $missingEvidence = $modules
            ->flatMap(fn (array $module): array => $module['missing_evidence'])
            ->unique()
            ->values()
            ->all();

        $notReleaseReady = $modules->where('status', 'not_release_ready')->count();

        return [
            'score' => $score,
            'target' => $target,
            'ready' => $score >= $target && $missingEvidence === [] && $notReleaseReady === 0,
            'production_ready' => $modules->where('status', 'production_ready')->count(),
            'release_candidate' => $modules->where('status', 'release_candidate')->count(),
            'foundation' => $modules->where('status', 'foundation')->count(),
            'not_release_ready' => $notReleaseReady,
            'missing_evidence' => $missingEvidence,
            'modules' => $modules->values()->all(),
        ];
    }
}
