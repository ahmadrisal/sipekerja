<?php

namespace App\Http\Controllers;

use App\Models\KabkotRating;
use App\Models\PimpinanKabkotScore;
use App\Models\PimpinanPegawaiScore;
use App\Models\Rating;
use App\Models\Satker;
use App\Models\Team;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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

    // ── Super Admin exports (any satker_id) ──────────────────────────

    public function superPegawai(Request $request, DashboardService $service)
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);
        return $this->doPegawai($request, $service, $request->query('satker_id'));
    }

    public function superKetuaTim(Request $request)
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);
        return $this->doKetuaTim($request, $request->query('satker_id'));
    }

    public function superKabkot(Request $request)
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);
        return $this->doKabkot($request);
    }

    public function superLaporan(Request $request)
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);
        $satkerId = $request->query('satker_id');
        abort_if(!$satkerId, 400);
        return $this->doLaporan($request, $satkerId);
    }

    // ── Pimpinan exports (scoped to active satker) ───────────────────

    public function pegawai(Request $request, DashboardService $service)
    {
        return $this->doPegawai($request, $service, activeSatkerId());
    }

    public function ketuaTim(Request $request)
    {
        return $this->doKetuaTim($request, activeSatkerId());
    }

    public function kabkot(Request $request)
    {
        return $this->doKabkot($request);
    }

    // ── Kepala Kabkot exports (scoped to active satker) ─────────────────

    public function kepalaPegawai(Request $request, DashboardService $service)
    {
        abort_unless(Auth::user()->hasRole('Kepala Kabkot'), 403);
        return $this->doPegawai($request, $service, activeSatkerId());
    }

    public function kepalaKetuaTim(Request $request)
    {
        abort_unless(Auth::user()->hasRole('Kepala Kabkot'), 403);
        return $this->doKetuaTim($request, activeSatkerId());
    }

    // ── Shared implementations ────────────────────────────────────────

    private function doPegawai(Request $request, DashboardService $service, ?string $satkerId)
    {
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $rekap = $service->getPimpinanRekap($month, $year, $satkerId);

        $pegawaiOnlyIds = User::role('Pegawai')
            ->where('satker_id', $satkerId)
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Ketua Tim', 'Kepala Kabkot', 'Pimpinan', 'Admin']))
            ->pluck('id')
            ->all();

        $data = collect($rekap['data'])
            ->filter(fn($u) => in_array($u->id, $pegawaiOnlyIds))
            ->sortBy('name');

        // For super admin exporting any satker, find the pimpinan of that satker
        if (Auth::user()->hasRole('Super Admin') && $satkerId) {
            $pimpinanId = User::role('Pimpinan')
                ->where('satker_id', $satkerId)
                ->value('id') ?? Auth::id();
        } else {
            $pimpinanId = Auth::id();
        }

        $pimpinanScores = PimpinanPegawaiScore::where('pimpinan_id', $pimpinanId)
            ->whereIn('pegawai_id', $pegawaiOnlyIds)
            ->where('period_month', $month)
            ->where('period_year',  $year)
            ->get()
            ->keyBy('pegawai_id');

        $rows = [];
        $no   = 1;
        foreach ($data as $u) {
            $rec        = $pimpinanScores->get($u->id);
            $raw        = $rec ? (float) $rec->score : ($u->averageScore > 0 ? $u->averageScore : null);
            $nilaiAkhir = $raw !== null ? (int) round($raw, 0) : null;

            $rows[] = [$no++, $u->nip, $u->name, $nilaiAkhir ?? '-'];
        }

        $period = ($this->monthNames[$month] ?? $month) . ' ' . $year;
        return $this->buildXlsx('Rekapitulasi Nilai Pegawai', $period,
            ['No', 'NIP', 'Nama Pegawai', 'Score Akhir'],
            $rows, "nilai_pegawai_{$month}_{$year}.xlsx");
    }

    private function doKetuaTim(Request $request, ?string $satkerId)
    {
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $ketuaTimUsers = User::role('Ketua Tim')
            ->where('satker_id', $satkerId)
            ->with('ledTeams')
            ->orderBy('name')
            ->get();

        if (Auth::user()->hasRole('Super Admin') && $satkerId) {
            $pimpinanId = User::role('Pimpinan')
                ->where('satker_id', $satkerId)
                ->value('id') ?? Auth::id();
        } else {
            $pimpinanId = Auth::id();
        }

        $ketuaIds = $ketuaTimUsers->pluck('id');

        // Skor pimpinan per tim yang diketuai KT
        $pimpinanRatings = Rating::where('evaluator_id', $pimpinanId)
            ->whereIn('target_user_id', $ketuaIds)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get(['target_user_id', 'team_id', 'score']);

        // Override pimpinan untuk KT sebagai anggota tim
        $pegawaiOverrides = PimpinanPegawaiScore::where('pimpinan_id', $pimpinanId)
            ->whereIn('pegawai_id', $ketuaIds)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get()
            ->keyBy('pegawai_id');

        // Auto-avg: skor yang diterima KT saat menjadi anggota tim (dinilai oleh KT lain)
        $memberRatings = Rating::whereIn('target_user_id', $ketuaIds)
            ->whereIn('evaluator_id', $ketuaIds)
            ->where('satker_id', $satkerId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->where('score', '>', 0)
            ->get(['target_user_id', 'final_score']);

        $rows = [];
        $no   = 1;
        foreach ($ketuaTimUsers as $kt) {
            if ($kt->ledTeams->isEmpty()) continue;

            // Kumpulkan skor pimpinan per tim yang diketuai
            $savedScores = [];
            foreach ($kt->ledTeams as $team) {
                $saved = $pimpinanRatings
                    ->where('target_user_id', $kt->id)
                    ->where('team_id', $team->id)
                    ->first();
                if ($saved) $savedScores[] = (float) $saved->score;
            }

            // Tentukan skor sebagai anggota tim: override → auto_avg → null
            $override = $pegawaiOverrides->get($kt->id);
            if ($override) {
                $pegScore = (float) $override->score;
            } else {
                $ktMemberRatings = $memberRatings->where('target_user_id', $kt->id);
                $pegScore = $ktMemberRatings->count() > 0
                    ? (float) round($ktMemberRatings->avg('final_score'), 2)
                    : null;
            }

            // Nilai akhir = rata-rata semua komponen (sama persis dengan dashboard)
            $allScores = $savedScores;
            if ($pegScore !== null) $allScores[] = $pegScore;

            $nilaiAkhir = count($allScores)
                ? (int) round(array_sum($allScores) / count($allScores))
                : null;

            $rows[] = [$no++, $kt->nip, $kt->name, $nilaiAkhir ?? '-'];
        }

        $period = ($this->monthNames[$month] ?? $month) . ' ' . $year;
        return $this->buildXlsx('Rekapitulasi Nilai Ketua Tim', $period,
            ['No', 'NIP', 'Nama Ketua Tim', 'Score Akhir'],
            $rows, "nilai_ketua_tim_{$month}_{$year}.xlsx");
    }

    private function doKabkot(Request $request)
    {
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $kabkots = User::role('Kepala Kabkot')->orderBy('name')->get();

        $pimpinanId = Auth::user()->hasRole('Super Admin')
            ? (User::role('Pimpinan')->value('id') ?? Auth::id())
            : Auth::id();

        $pimpinanScores = PimpinanKabkotScore::where('pimpinan_id', $pimpinanId)
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
            $rows[] = [$no++, $kabkot->nip, $kabkot->name, $score ?? '-'];
        }

        $period = ($this->monthNames[$month] ?? $month) . ' ' . $year;
        return $this->buildXlsx('Rekapitulasi Nilai Kepala Kabkot', $period,
            ['No', 'NIP', 'Nama Kepala Kabkot', 'Score Akhir'],
            $rows, "nilai_kabkot_{$month}_{$year}.xlsx");
    }

    // ── Laporan Lengkap (multi-sheet) ─────────────────────────────────

    private function doLaporan(Request $request, string $satkerId)
    {
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $satker = Satker::findOrFail($satkerId);

        $pimpinanId = User::role('Pimpinan')
            ->where('satker_id', $satkerId)
            ->value('id');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // remove default sheet

        // ── Sheet 1: Nilai Pegawai ─────────────────────────────────────
        $sheet1 = new Worksheet($spreadsheet, 'Nilai Pegawai');
        $spreadsheet->addSheet($sheet1);

        $teams = Team::where('satker_id', $satkerId)->with('leader')->get()->keyBy('id');

        $headers1 = ['No', 'NIP', 'Nama Pegawai', 'Tim', 'Ketua Tim (Evaluator)', 'Score KT', 'Volume', 'Kualitas', 'Score Akhir'];
        $this->applySheetTitle($sheet1, 'Nilai Pegawai — ' . ($this->monthNames[$month] ?? $month) . ' ' . $year, count($headers1));
        $this->applySheetHeaders($sheet1, $headers1);

        $ratings1 = Rating::where('satker_id', $satkerId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->where('score', '>', 0)
            ->with(['targetUser', 'evaluator', 'team'])
            ->get();

        $no = 1;
        $rowIdx = 0;
        foreach ($ratings1->sortBy(fn($r) => optional($r->targetUser)->name) as $rating) {
            $pegawai   = $rating->targetUser;
            $evaluator = $rating->evaluator;
            $team      = $rating->team;
            if (!$pegawai) continue;

            $this->applyDataRow($sheet1, $rowIdx + 3, $rowIdx, [
                $no++,
                $pegawai->nip ?? '',
                $pegawai->name ?? '',
                $team ? $team->team_name : '',
                $evaluator ? $evaluator->name : '',
                $rating->score,
                $rating->volume_work ?? '',
                $rating->quality_work ?? '',
                $rating->final_score,
            ]);
            $rowIdx++;
        }

        $this->setColumnWidths($sheet1, [6, 22, 32, 24, 28, 10, 12, 14, 12]);

        // ── Sheet 2: Nilai Ketua Tim ───────────────────────────────────
        $sheet2 = new Worksheet($spreadsheet, 'Nilai Ketua Tim');
        $spreadsheet->addSheet($sheet2);

        $headers2 = ['No', 'NIP', 'Nama Ketua Tim', 'Tim Yang Diketuai', 'Score Pimpinan/Kepala', 'Score Sbg Anggota', 'Score Akhir'];
        $this->applySheetTitle($sheet2, 'Nilai Ketua Tim — ' . ($this->monthNames[$month] ?? $month) . ' ' . $year, count($headers2));
        $this->applySheetHeaders($sheet2, $headers2);

        $ketuaTimUsers = User::role('Ketua Tim')
            ->where('satker_id', $satkerId)
            ->with('ledTeams')
            ->orderBy('name')
            ->get();

        $ketuaIds = $ketuaTimUsers->pluck('id');

        $pimpinanRatings2 = $pimpinanId
            ? Rating::where('evaluator_id', $pimpinanId)
                ->whereIn('target_user_id', $ketuaIds)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->get(['target_user_id', 'team_id', 'score'])
            : collect();

        $pegawaiOverrides2 = $pimpinanId
            ? PimpinanPegawaiScore::where('pimpinan_id', $pimpinanId)
                ->whereIn('pegawai_id', $ketuaIds)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->get()
                ->keyBy('pegawai_id')
            : collect();

        $memberRatings2 = Rating::whereIn('target_user_id', $ketuaIds)
            ->whereIn('evaluator_id', $ketuaIds)
            ->where('satker_id', $satkerId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->where('score', '>', 0)
            ->get(['target_user_id', 'final_score']);

        $no = 1;
        $rowIdx = 0;
        foreach ($ketuaTimUsers as $kt) {
            if ($kt->ledTeams->isEmpty()) continue;

            $override = $pegawaiOverrides2->get($kt->id);
            if ($override) {
                $pegScore = (float) $override->score;
            } else {
                $ktMemberRatings = $memberRatings2->where('target_user_id', $kt->id);
                $pegScore = $ktMemberRatings->count() > 0
                    ? (float) round($ktMemberRatings->avg('final_score'), 2)
                    : null;
            }

            foreach ($kt->ledTeams as $team) {
                $savedRating = $pimpinanRatings2
                    ->where('target_user_id', $kt->id)
                    ->where('team_id', $team->id)
                    ->first();
                $pimScore = $savedRating ? (float) $savedRating->score : null;

                $allScores = array_filter([$pimScore, $pegScore], fn($s) => $s !== null);
                $nilaiAkhir = count($allScores)
                    ? (int) round(array_sum($allScores) / count($allScores))
                    : null;

                $this->applyDataRow($sheet2, $rowIdx + 3, $rowIdx, [
                    $no++,
                    $kt->nip ?? '',
                    $kt->name ?? '',
                    $team->team_name,
                    $pimScore ?? '-',
                    $pegScore ?? '-',
                    $nilaiAkhir ?? '-',
                ]);
                $rowIdx++;
            }
        }

        $this->setColumnWidths($sheet2, [6, 22, 32, 24, 20, 18, 12]);

        // ── Sheets 3 & 4: Hanya untuk satker Provinsi ─────────────────
        if ($satker->type === 'provinsi') {

            // Sheet 3: Nilai Kepala Kabkot (per Tim)
            $sheet3 = new Worksheet($spreadsheet, 'Nilai Kepala Kabkot');
            $spreadsheet->addSheet($sheet3);

            $headers3 = ['No', 'NIP', 'Nama Kepala Kabkot', 'Tim Penilai', 'Ketua Tim Penilai', 'Score'];
            $this->applySheetTitle($sheet3, 'Nilai Kepala Kabkot (per Tim) — ' . ($this->monthNames[$month] ?? $month) . ' ' . $year, count($headers3));
            $this->applySheetHeaders($sheet3, $headers3);

            $kabkotRatings = KabkotRating::where('period_month', $month)
                ->where('period_year', $year)
                ->where('score', '>', 0)
                ->with(['kabkot', 'evaluator', 'team'])
                ->get();

            $no = 1;
            $rowIdx = 0;
            foreach ($kabkotRatings->sortBy(fn($r) => optional($r->kabkot)->name) as $kr) {
                $kabkot    = $kr->kabkot;
                $evaluator = $kr->evaluator;
                $team      = $kr->team;
                if (!$kabkot) continue;

                $this->applyDataRow($sheet3, $rowIdx + 3, $rowIdx, [
                    $no++,
                    $kabkot->nip ?? '',
                    $kabkot->name ?? '',
                    $team ? $team->team_name : '',
                    $evaluator ? $evaluator->name : '',
                    $kr->score,
                ]);
                $rowIdx++;
            }

            $this->setColumnWidths($sheet3, [6, 22, 32, 24, 28, 10]);

            // Sheet 4: Nilai Kabkot dari Pimpinan
            $sheet4 = new Worksheet($spreadsheet, 'Nilai Kabkot dari Pimpinan');
            $spreadsheet->addSheet($sheet4);

            $headers4 = ['No', 'NIP', 'Nama Kepala Kabkot', 'Score Pimpinan'];
            $this->applySheetTitle($sheet4, 'Nilai Kabkot dari Pimpinan — ' . ($this->monthNames[$month] ?? $month) . ' ' . $year, count($headers4));
            $this->applySheetHeaders($sheet4, $headers4);

            $kabkotUsers = User::role('Kepala Kabkot')->orderBy('name')->get();

            $pimpinanKabkotScores = $pimpinanId
                ? PimpinanKabkotScore::where('pimpinan_id', $pimpinanId)
                    ->whereIn('kabkot_id', $kabkotUsers->pluck('id'))
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->get()
                    ->keyBy('kabkot_id')
                : collect();

            $no = 1;
            $rowIdx = 0;
            foreach ($kabkotUsers as $kabkot) {
                $rec   = $pimpinanKabkotScores->get($kabkot->id);
                $score = $rec ? round((float) $rec->score, 2) : null;

                $this->applyDataRow($sheet4, $rowIdx + 3, $rowIdx, [
                    $no++,
                    $kabkot->nip ?? '',
                    $kabkot->name ?? '',
                    $score ?? '-',
                ]);
                $rowIdx++;
            }

            $this->setColumnWidths($sheet4, [6, 22, 32, 14]);
        }

        $safeName = preg_replace('/[^A-Za-z0-9]+/', '_', $satker->name);
        $filename  = "laporan_{$safeName}_{$month}_{$year}.xlsx";

        $temp   = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function applySheetTitle(Worksheet $sheet, string $title, int $colCount): void
    {
        $lastCol = $colCount <= 26 ? chr(64 + $colCount) : 'A' . chr(64 + ($colCount - 26));
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);
    }

    private function applySheetHeaders(Worksheet $sheet, array $headers): void
    {
        $colCount = count($headers);
        $lastCol  = $colCount <= 26 ? chr(64 + $colCount) : 'A' . chr(64 + ($colCount - 26));
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '2', $header);
        }
        $sheet->getStyle('A2:' . $lastCol . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(24);
    }

    private function applyDataRow(Worksheet $sheet, int $rowNum, int $rowIdx, array $values): void
    {
        $colCount = count($values);
        $lastCol  = $colCount <= 26 ? chr(64 + $colCount) : 'A' . chr(64 + ($colCount - 26));
        $bgColor  = $rowIdx % 2 === 0 ? 'F5F5FF' : 'FFFFFF';

        foreach ($values as $colIdx => $value) {
            $cell = chr(65 + $colIdx) . $rowNum;
            if ($colIdx === 1) {
                $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($cell, $value);
            }
        }

        $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0F0']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($rowNum)->setRowHeight(20);
    }

    private function setColumnWidths(Worksheet $sheet, array $widths): void
    {
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension(chr(65 + $i))->setWidth($w);
        }
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
        $lastCol  = chr(64 + $colCount);

        $sheet->setCellValue('A1', $title . ' — ' . $period);
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '2', $header);
        }
        $sheet->getStyle('A2:' . $lastCol . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(24);

        foreach ($rows as $rowIdx => $row) {
            $rowNum  = $rowIdx + 3;
            $bgColor = $rowIdx % 2 === 0 ? 'F5F5FF' : 'FFFFFF';

            foreach ($row as $colIdx => $value) {
                $cell = chr(65 + $colIdx) . $rowNum;
                if ($colIdx === 1) {
                    $sheet->setCellValueExplicit($cell, (string) $value,
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($cell, $value);
                }
            }

            $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($rowNum)->setRowHeight(20);
        }

        foreach ([8, 22, 32, 14] as $i => $w) {
            if ($i < $colCount) $sheet->getColumnDimension(chr(65 + $i))->setWidth($w);
        }

        $totalRows = count($rows) + 2;
        $sheet->getStyle('A3:A' . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($lastCol . '3:' . $lastCol . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $temp   = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
