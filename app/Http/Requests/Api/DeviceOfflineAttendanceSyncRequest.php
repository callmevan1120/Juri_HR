<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DeviceOfflineAttendanceSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.client_uuid' => ['required', 'string', 'max:120'],
            'items.*.barcode_data' => ['required', 'string'],
            'items.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'items.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'items.*.timestamp' => ['required', 'date_format:Y-m-d H:i:s'],
            'items.*.accuracy' => ['nullable', 'numeric', 'min:0'],
            'items.*.gps_variance' => ['nullable', 'numeric', 'min:0'],
            'items.*.mock_location_detected' => ['nullable', 'boolean'],
            'items.*.qr_token_retries' => ['nullable', 'integer', 'min:0', 'max:10'],
            'items.*.photo_base64' => ['nullable', 'string'],
        ];
    }
}
