<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;

class Helpers
{
    public static function normalizeInternalUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        if (! isset($parts['scheme']) && ! isset($parts['host'])) {
            return $url;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return $path.$query.$fragment;
    }

    public static function getGoogleMapsUrl($lat, $lng)
    {
        return "https://maps.google.com/maps?q=$lat,$lng";
    }

    /**
     * Get the URL path from the app URL
     *
     * E.g. base url/app url = http://localhost:8000/path => path
     *
     * Returns empty string if base url is root path
     */
    public static function getNonRootBaseUrlPath()
    {
        $segments = explode('/', parse_url(config('app.url'), PHP_URL_PATH));

        return count($segments) < 2 ? '' : $segments[1];
    }

    /**
     * Format time based on application settings
     *
     * @param  string|Carbon|null  $time
     * @return string
     */
    public static function format_time($time)
    {
        if (! $time) {
            return '-';
        }

        if (is_string($time)) {
            try {
                $time = Carbon::parse($time);
            } catch (\Exception $e) {
                return $time;
            }
        }

        $format = Setting::getValue('app.time_format', '24');
        $showSeconds = (bool) Setting::getValue('app.show_seconds', false);

        if ($format == '12') {
            $formatString = $showSeconds ? 'h:i:s A' : 'h:i A';
        } else {
            $formatString = $showSeconds ? 'H:i:s' : 'H:i';
        }

        return $time->format($formatString);
    }

    public static function formatNumberId(float|int|string|null $value, int $decimals = 0, bool $trimZeros = true): string
    {
        $number = is_numeric($value) ? (float) $value : 0.0;
        $formatted = number_format($number, $decimals, ',', '.');

        if ($trimZeros && $decimals > 0) {
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }

        return $formatted;
    }

    public static function formatRupiah(float|int|string|null $value, int $decimals = 0): string
    {
        return 'Rp'.self::formatNumberId($value, $decimals, false);
    }

    public static function formatPercentId(float|int|string|null $value, int $decimals = 2, bool $trimZeros = true): string
    {
        return self::formatNumberId($value, $decimals, $trimZeros).'%';
    }

    public static function formatUnitId(float|int|string|null $value, string $unit, int $decimals = 3): string
    {
        $unit = trim($unit);

        return trim(self::formatNumberId($value, $decimals).' '.$unit);
    }
}
