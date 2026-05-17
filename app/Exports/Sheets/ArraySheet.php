<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArraySheet implements FromArray, ShouldAutoSize, WithTitle
{
    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function __construct(
        private readonly string $title,
        private readonly array $rows,
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return $this->title;
    }
}
