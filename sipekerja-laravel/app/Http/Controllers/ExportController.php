<?php

namespace App\Http\Controllers;

use App\Models\KabkotRating;
use App\Models\PimpinanKabkotScore;
use App\Models\PimpinanPegawaiScore;
use App\Models\Rating;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportController extends Controller
{
    private array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',    4 => 'April',
        5 => 'Mei',     6 => 'Juni',     7 => 'Juli',      8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function pegawai(Request $request, DashboardService $service)
    {
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $rekap = $service->getPimpinanRekap($month, $year);

        $pegawaiOnlyIds = User::role('Pegawai')
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Ketua Tim', 'Kepala Kabkot', 'Pimpinan', 'Admin']);
            })
            ->pluck('id')
            ->all();

        $data = collect($rekap['data'])
            ->filter(fn($u) => in_array($u->id, $pegawaiOnlyIds))
            ->sortBy('name');

        $pimpinanScores = PimpinanPegawaiScore::where('pimpinan_id', Auth::id())
            ->whereIn('pegawai_id', $pegawaiOnlyIds)
            ->where('period_month', $month)
            ->where('period_year',  $year)
            ->get()
            ->keyBy('pegawai_id');

        $rows = [];
        $no   = 1;
        foreach ($data as $u) {
            $rec        = $pimpinanScores->get($u->id);
            $nilaiAkhir = $rec ? round((float) $rec->score, 2) : ($u->averageScore > 0 ? $u->averageScore : null);

            $rows[] = [
                $no++,
                $u->nip,
                $u->name,
                $nilaiAkhir !== null ? $nilaiAkhir : '-',
            ];
        }

        $period = ($this->monthNames[$month] ?? $month) . ' ' . $year;

        return $this->buildXlsx(
            'Rekapitulasi Nilai Pegawai',
            $period,
            ['No', 'NIP', 'Nama Pegawai', 'Score Akhir'],
            $rows,
            "nilai_pegawai_{$month}_{$year}.xlsx"
        );
    }

    public function ketuaTim(Request $request)
    {
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $ketuaTimUsers = User::role('Ketua Tim')->with('ledTeams')->orderBy('name')->get();

        $pimpinanRatings = Rating::where('evaluator_id', Auth::id())
            ->whereIn('target_user_id', $ketuaTimUsers->pluck('id'))
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get(['target_user_id', 'team_id', 'score']);

        $rows = [];
        $no   = 1;
        foreach ($ketuaTimUsers as $kt) {
            if ($kt->ledTeams->isEmpty()) {
                continue;
            }

            $savedScores = [];
            foreach ($kt->ledTeams as $team) {
                $saved = $pimpinanRatings
                    ->where('target_user_id', $kt->id)
                    ->where('team_id', $team->id)
                    ->first();
                if ($saved) {
                    $savedScores[] = (float) $saved->score;
                }
            }

            $nilaiAkhir = count($savedScores)
                ? round(array_sum($savedScores) / count($savedScores), 2)
                : null;

            $rows[] = [
                $no++,
                $kt->nip,
                $kt->name,
                $nilaiAkhir !== null ? $nilaiAkhir : '-',
            ];
        }

        $period = ($this->monthNames[$month] ?? $month) . ' ' . $year;

        return $this->buildXlsx(
            'Rekapitulasi Nilai Ketua Tim',
            $period,
            ['No', 'NIP', 'Nama Ketua Tim', 'Score Akhir'],
            $rows,
            "nilai_ketua_tim_{$month}_{$year}.xlsx"
        );
    }

    public function kabkot(Request $request)
    {
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $kabkots = User::role('Kepala Kabkot')->orderBy('name')->get();

        $pimpinanScores = PimpinanKabkotScore::where('pimpinan_id', Auth::id())
            ->whereIn('kabkot_id', $kabkots->pluck('id'))
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get()
            ->keyBy('kabkot_id');

        $rows = [];
        $no   = 1;
        foreach ($kabkots as $kabkot) {
            $rec   = $pimpinanScores->get($kabkot->id);
            $score = $rec ? round((float) $rec->score, 2) : null;

            $rows[] = [
                $no++,
                $kabkot->nip,
                $kabkot->name,
                $score !== null ? $score : '-',
            ];
        }

        $period = ($this->monthNames[$month] ?? $month) . ' ' . $year;

        return $this->buildXlsx(
            'Rekapitulasi Nilai Kepala Kabkot',
            $period,
            ['No', 'NIP', 'Nama Kepala Kabkot', 'Score Akhir'],
            $rows,
            "nilai_kabkot_{$month}_{$year}.xlsx"
        );
    }

    private function buildXlsx(
        string $title,
        string $period,
        array  $headers,
        array  $rows,
        string $filename
    ) {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        $colCount = count($headers);
        $lastCol  = chr(64 + $colCount); // A=65

        // ── Title row ─────────────────────────────────────────────────
        $sheet->setCellValue('A1', $title . ' — ' . $period);
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ── Header row ────────────────────────────────────────────────
        foreach ($headers as $i => $header) {
            $cell = chr(65 + $i) . '2';
            $sheet->setCellValue($cell, $header);
        }
        $sheet->getStyle('A2:' . $lastCol . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(24);

        // ── Data rows ─────────────────────────────────────────────────
        foreach ($rows as $rowIdx => $row) {
            $rowNum  = $rowIdx + 3;
            $isEven  = $rowIdx % 2 === 0;
            $bgColor = $isEven ? 'F5F5FF' : 'FFFFFF';

            foreach ($row as $colIdx => $value) {
                $cell = chr(65 + $colIdx) . $rowNum;
                // Force NIP column as string to preserve leading zeros
                if ($colIdx === 1) {
                    $sheet->setCellValueExplicit(
                        $cell,
                        (string) $value,
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
                } else {
                    $sheet->setCellValue($cell, $value);
                }
            }

            $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($rowNum)->setRowHeight(20);
        }

        // ── Column widths ─────────────────────────────────────────────
        $widths = [8, 22, 32, 14];
        foreach ($widths as $i => $w) {
            if ($i < $colCount) {
                $sheet->getColumnDimension(chr(65 + $i))->setWidth($w);
            }
        }

        // Center-align No and Score columns
        $totalRows = count($rows) + 2;
        $sheet->getStyle('A3:A' . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($lastCol . '3:' . $lastCol . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ── Stream download ───────────────────────────────────────────
        $temp   = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
