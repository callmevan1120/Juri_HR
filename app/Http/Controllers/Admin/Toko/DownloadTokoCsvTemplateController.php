<?php

namespace App\Http\Controllers\Admin\Toko;

use App\Http\Controllers\Controller;
use App\Support\TokoCsvImportTemplates;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadTokoCsvTemplateController extends Controller
{
    public function __invoke(string $type): StreamedResponse
    {
        $template = TokoCsvImportTemplates::find($type);

        abort_if($template === null, 404);

        return response()->streamDownload(function () use ($template): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fputcsv($output, $template['headers']);
            fputcsv($output, $template['sample']);
            fclose($output);
        }, $template['filename'], [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
