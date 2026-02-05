<?php

namespace App\Exports;

use App\Models\Pendaftar;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PendaftarNilaiExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    use Exportable;

    private int $prodiId;
    private bool $includeStatus;
    private int $rowNumber = 0;

    public function __construct(int $prodiId, bool $includeStatus = true)
    {
        $this->prodiId = $prodiId;
        $this->includeStatus = $includeStatus;
    }

    public function query()
    {
        return Pendaftar::query()
            ->where('prodi_id', $this->prodiId)
            ->with(['prodi'])
            ->select('id', 'nomor_pendaftaran', 'nama_lengkap', 'nilai_ujian', 'status_kelulusan', 'prodi_id', 'jadwal_ujian_id')
            ->orderBy('nama_lengkap');
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Nomor Pendaftaran',
            'Nama Lengkap',
            'Nilai Ujian (0-100)',
        ];

        if ($this->includeStatus) {
            $headers[] = 'Status Kelulusan (lulus/tidak_lulus/belum_diproses)';
        }

        return $headers;
    }

    public function map($pendaftar): array
    {
        $this->rowNumber++;
        
        $row = [
            $this->rowNumber,
            $pendaftar->nomor_pendaftaran,
            $pendaftar->nama_lengkap,
            $pendaftar->nilai_ujian ?? '',
        ];

        if ($this->includeStatus) {
            $row[] = $pendaftar->status_kelulusan ?? 'belum_diproses';
        }

        return $row;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 6,
            'B' => 22,
            'C' => 35,
            'D' => 22,
        ];

        if ($this->includeStatus) {
            $widths['E'] = 45;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $this->includeStatus ? 'E' : 'D';
        
        // Header styling
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [
            // All cells alignment
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}
