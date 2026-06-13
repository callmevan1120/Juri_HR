<?php
$content = file_get_contents('/Users/lutuk/Project/learning/absensi-gps-barcode/resources/views/livewire/admin/toko-pos-addon.blade.php');
$lines = explode("\n", $content);
$stack = [];

foreach ($lines as $idx => $line) {
    if (preg_match_all('/@(if|forelse|foreach|while|empty|else|elseif|endif|endforelse|endforeach|endwhile)\b/', $line, $matches)) {
        foreach ($matches[1] as $match) {
            if (in_array($match, ['if', 'forelse', 'foreach', 'while'])) {
                $stack[] = ['type' => $match, 'line' => $idx + 1];
            } elseif (in_array($match, ['endif', 'endforelse', 'endforeach', 'endwhile'])) {
                $expected = str_replace('end', '', $match);
                if (empty($stack)) {
                    echo "UNEXPECTED $match at line " . ($idx + 1) . "\n";
                } else {
                    $last = array_pop($stack);
                    if ($last['type'] !== $expected) {
                        echo "MISMATCH at line " . ($idx + 1) . ": found $match, expected end{$last['type']} (opened at line {$last['line']})\n";
                    }
                }
            } elseif (in_array($match, ['else', 'elseif', 'empty'])) {
                if (empty($stack)) {
                    echo "UNEXPECTED $match at line " . ($idx + 1) . "\n";
                }
            }
        }
    }
}
if (!empty($stack)) {
    echo "UNCLOSED BLOCKS:\n";
    print_r($stack);
} else {
    echo "ALL MATCHED!\n";
}
