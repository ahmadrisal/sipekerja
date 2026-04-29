<?php

namespace App\Livewire\Dashboards;

use App\Services\DashboardService;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AdminDashboard extends Component
{
    public $month;
    public $year;
    public $adminDialogType = null;
    public $searchQuery = '';

    public $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function mount()
    {
        $this->month = date('n');
        $this->year = date('Y');
    }

    public function updatedMonth()
    {
        $this->dispatch('refreshAdminData');
    }

    public function updatedYear()
    {
        $this->dispatch('refreshAdminData');
    }

    public function setAdminDialog($type)
    {
        $this->adminDialogType = $type;
        $this->searchQuery = '';
    }

    // ── Exports ──────────────────────────────────────────────────────

    private function headerStyle(string $bg = '4F46E5'): array
    {
        return [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }

    private function streamXlsx(Spreadsheet $spreadsheet, string $fileName)
    {
        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function exportDistribusiTimPegawai(DashboardService $service)
    {
        $chart = $service->getAdminChartData();
        $users = \App\Models\User::where('satker_id', activeSatkerId())->with('teams')->orderBy('name')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Distribusi Tim per Pegawai');

        $headers = ['No', 'Nama Pegawai', 'NIP', 'Jumlah Tim', 'Nama Tim-Tim'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
            $sheet->getStyle("{$col}1")->applyFromArray($this->headerStyle());
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth([4, 35, 22, 14, 60][$i]);
        }

        foreach ($users as $idx => $user) {
            $row = $idx + 2;
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $user->name);
            $sheet->setCellValue("C{$row}", $user->nip);
            $sheet->setCellValue("D{$row}", $user->teams->count());
            $sheet->setCellValue("E{$row}", $user->teams->pluck('team_name')->implode(', ') ?: '-');

            if ($user->teams->count() === 0) {
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
                    'font' => ['color' => ['rgb' => 'B91C1C']],
                ]);
            }
        }

        return $this->streamXlsx($spreadsheet, 'distribusi_tim_per_pegawai.xlsx');
    }

    public function exportDistribusiUkuranTim(DashboardService $service)
    {
        $teams = \App\Models\Team::where('satker_id', activeSatkerId())->with(['members', 'leader'])->orderByDesc(
            \App\Models\Team::selectRaw('count(*)')
                ->from('team_members')
                ->whereColumn('team_id', 'teams.id')
        )->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Distribusi Ukuran Tim');

        $headers = ['No', 'Nama Tim', 'Ketua Tim', 'Jumlah Anggota', 'Daftar Anggota'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
            $sheet->getStyle("{$col}1")->applyFromArray($this->headerStyle('059669'));
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth([4, 35, 30, 16, 80][$i]);
        }

        foreach ($teams as $idx => $team) {
            $row = $idx + 2;
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $team->team_name);
            $sheet->setCellValue("C{$row}", $team->leader?->name ?? '-');
            $sheet->setCellValue("D{$row}", $team->members->count());
            $sheet->setCellValue("E{$row}", $team->members->pluck('name')->implode(', ') ?: '-');
        }

        return $this->streamXlsx($spreadsheet, 'distribusi_ukuran_tim.xlsx');
    }

    public function exportStatusPlot(DashboardService $service)
    {
        $users = \App\Models\User::where('satker_id', activeSatkerId())->with('teams')->orderBy('name')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Status Plot Pegawai');

        $headers = ['No', 'Nama Pegawai', 'NIP', 'Status', 'Nama Tim-Tim'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
            $sheet->getStyle("{$col}1")->applyFromArray($this->headerStyle('7C3AED'));
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth([4, 35, 22, 18, 60][$i]);
        }

        foreach ($users as $idx => $user) {
            $row     = $idx + 2;
            $plotted = $user->teams->count() > 0;
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $user->name);
            $sheet->setCellValue("C{$row}", $user->nip);
            $sheet->setCellValue("D{$row}", $plotted ? 'Sudah Terplot' : 'Belum Terplot');
            $sheet->setCellValue("E{$row}", $user->teams->pluck('team_name')->implode(', ') ?: '-');

            $fillColor = $plotted ? 'F0FDF4' : 'FFF1F2';
            $fontColor = $plotted ? '166534' : '9F1239';
            $sheet->getStyle("D{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'font' => ['bold' => true, 'color' => ['rgb' => $fontColor]],
            ]);
        }

        return $this->streamXlsx($spreadsheet, 'status_plot_pegawai.xlsx');
    }

    public function exportTopBebanTim(DashboardService $service)
    {
        $users = \App\Models\User::where('satker_id', activeSatkerId())->with('teams')->get()
            ->sortByDesc(fn($u) => $u->teams->count())
            ->take(10)->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Top Beban Tim Terbanyak');

        $headers = ['Peringkat', 'Nama Pegawai', 'NIP', 'Jumlah Tim', 'Nama Tim-Tim'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
            $sheet->getStyle("{$col}1")->applyFromArray($this->headerStyle('B45309'));
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth([12, 35, 22, 14, 60][$i]);
        }

        foreach ($users as $idx => $user) {
            $row = $idx + 2;
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $user->name);
            $sheet->setCellValue("C{$row}", $user->nip);
            $sheet->setCellValue("D{$row}", $user->teams->count());
            $sheet->setCellValue("E{$row}", $user->teams->pluck('team_name')->implode(', ') ?: '-');

            if ($idx === 0) {
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBEB']],
                    'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                ]);
            }
        }

        return $this->streamXlsx($spreadsheet, 'top_beban_tim_terbanyak.xlsx');
    }

    public function render(DashboardService $service)
    {
        $stats      = $service->getAdminStats();
        $chartData  = $service->getAdminChartData();

        return view('livewire.dashboards.admin-dashboard', [
            'stats'     => $stats,
            'chartData' => $chartData,
        ]);
    }
}
