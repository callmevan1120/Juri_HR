<?php

namespace App\Services\Attendance;

use App\Contracts\AttendanceServiceInterface;
use App\Models\Attendance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CommunityService implements AttendanceServiceInterface
{
    public function storeAttachment(UploadedFile $file): string
    {
        return $file->store(
            'attachments',
            ['disk' => 'local']
        );
    }

    public function getAttachmentUrl(Attendance $attendance): string|array|null
    {
        if (! $attendance->attachment) {
            return null;
        }

        $decoded = json_decode($attendance->attachment, true);

        // Helper
        $getUrl = function ($path, $type = null) use ($attendance) {
            if (str_contains($path, 'https://') || str_contains($path, 'http://')) {
                return $path;
            }

            if ($type !== null) {
                return route('attendance.photo', [
                    'attendance' => $attendance->id,
                    'type' => $type,
                ]);
            }

            return route('attendance.attachment.download', ['attendance' => $attendance->id]);
        };

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $urls = [];
            foreach ($decoded as $key => $path) {
                $urls[$key] = $getUrl($path, $key);
            }

            return $urls;
        }

        return $getUrl($attendance->attachment);
    }

    public function shouldEnforceFaceEnrollment(): bool
    {
        return filter_var(
            \App\Models\Setting::getValue('attendance.require_face_enrollment', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function storeAttendancePhoto(string $base64Data, string $filename): string
    {
        $path = 'attendance_photos/'.date('Y/m/d');
        $filename = $this->safePhotoFilename($filename);
        $image = $this->decodeAttendancePhoto($base64Data);

        Storage::disk('local')->put($path.'/'.$filename, $image);

        return $path.'/'.$filename;
    }

    private function decodeAttendancePhoto(string $base64Data): string
    {
        $payload = trim($base64Data);

        if (str_contains($payload, ',')) {
            [$metadata, $payload] = explode(',', $payload, 2);
            if (! preg_match('/^data:image\/(?:jpeg|jpg|png);base64$/i', $metadata)) {
                throw new \InvalidArgumentException('Unsupported attendance photo MIME type.');
            }
        }

        $decoded = base64_decode(str_replace(' ', '+', $payload), true);

        if ($decoded === false || strlen($decoded) > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('Invalid attendance photo payload.');
        }

        $imageInfo = @getimagesizefromstring($decoded);

        if (! is_array($imageInfo) || ! in_array($imageInfo[2] ?? null, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            throw new \InvalidArgumentException('Invalid attendance photo image.');
        }

        return $decoded;
    }

    private function safePhotoFilename(string $filename): string
    {
        $filename = basename($filename);

        if (! preg_match('/^[A-Za-z0-9._-]+\.(?:jpg|jpeg|png)$/i', $filename)) {
            return bin2hex(random_bytes(16)).'.jpg';
        }

        return $filename;
    }

    public function registerFace(\App\Models\User $user, array $descriptor): void
    {
        // Community Edition: Face ID Unlocked
        \App\Models\FaceDescriptor::updateOrCreate(
            ['user_id' => $user->id],
            ['descriptor' => $descriptor]
        );
    }

    public function removeFace(\App\Models\User $user): void
    {
        // Community Edition: Face ID Unlocked
        $user->faceDescriptor()->delete();
    }
}
