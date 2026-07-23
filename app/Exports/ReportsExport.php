<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromArray, ShouldAutoSize, WithStyles
{
    protected array $rows;

    /**
     * @param array $rows Fully prepared rows (metadata + header + data)
     * @param int|null $headerRow 1-indexed row for bold styling (null = no bold)
     * @param string|null $mergeColumn Column letter to merge duplicate values (e.g. 'B')
     * @param int|null $mergeStartRow Row to start merging from (1-indexed)
     */
    public function __construct(
        array $rows,
        protected ?int $headerRow = null,
        protected ?string $mergeColumn = null,
        protected ?int $mergeStartRow = null,
    ) {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function styles(Worksheet $sheet): void
    {
        // Bold the header row only
        if ($this->headerRow !== null) {
            $sheet->getStyle($this->headerRow)->getFont()->setBold(true);
        }

        // Merge duplicate values in the specified column
        if ($this->mergeColumn && $this->mergeStartRow) {
            $this->mergeColumnValues($sheet);
        }
    }

    /**
     * Merge contiguous cells with the same value vertically.
     */
    private function mergeColumnValues(Worksheet $sheet): void
    {
        $col = $this->mergeColumn;
        $startRow = $this->mergeStartRow;
        $highestRow = $sheet->getHighestRow();
        $prevValue = null;
        $mergeFrom = null;

        for ($row = $startRow; $row <= $highestRow + 1; $row++) {
            $currentValue = $row <= $highestRow
                ? $sheet->getCell($col . $row)->getValue()
                : null;

            if ($currentValue !== $prevValue) {
                // End previous merge group if it spans more than 1 row
                if ($mergeFrom !== null && $row - 1 > $mergeFrom) {
                    $sheet->mergeCells($col . $mergeFrom . ':' . $col . ($row - 1));
                }
                // Start new merge group (only if not empty)
                $mergeFrom = !empty($currentValue) ? $row : null;
                $prevValue = $currentValue;
            }
        }
    }
}
