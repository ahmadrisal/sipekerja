<?php

namespace App\Livewire\Dashboards;

use App\Models\User;
use App\Services\DashboardService;
use Livewire\Component;

class PimpinanDashboard extends Component
{
    public $activeTab = 'overview';
    public $month;
    public $year;
    public $search = '';
    public $teamFilter = 'All';
    public $statusFilter = 'All';
    public $sortKey = 'averageScore';
    public $sortDir = 'desc';
    
    public $detailUserId = null;
    public $reportUserId = null;
    public $showIncompleteTeamsDialog = false;
    public $showIncompleteEmployeesDialog = false;
    public $reportSearch = '';
    public $reportData = null;

    public function mount()
    {
        $this->month = date('n');
        $this->year = date('Y');
    }

    public function handleSort($key)
    {
        if ($this->sortKey === $key) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortKey = $key;
            $this->sortDir = 'desc';
        }
    }

    public function setDetailUser($id)
    {
        $this->detailUserId = $id;
    }

    public function updatedMonth()
    {
        $this->dispatchChartsRefresh();
    }

    public function updatedYear()
    {
        $this->dispatchChartsRefresh();
    }

    public function updatedTeamFilter()
    {
        $this->dispatchChartsRefresh();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'overview') {
            $this->dispatchChartsRefresh();
        }
    }

    public function setReportUserId($id)
    {
        $this->reportUserId = $id;
        $this->detailUserId = null; // Close detail dialog if open
        $this->setActiveTab('report');
        $this->reportSearch = ''; // Clear search to hide suggestions
    }

    private function dispatchChartsRefresh()
    {
        $service = app(DashboardService::class);
        $rekap = $service->getPimpinanRekap($this->month, $this->year);
        $charts = $this->prepareChartsData($rekap['data']);
        $this->dispatch('refreshCharts', charts: $charts);
    }

    public function render(DashboardService $service)
    {
        $rekap = $service->getPimpinanRekap($this->month, $this->year);
        $data = collect($rekap['data']);

        // Filtering
        if ($this->search) {
            $q = strtolower($this->search);
            $data = $data->filter(fn($u) => 
                str_contains(strtolower($u->name), $q) || str_contains($u->nip, $q)
            );
        }

        if ($this->teamFilter !== 'All') {
            $data = $data->filter(fn($u) => 
                collect($u->details)->contains('teamName', $this->teamFilter)
            );
        }

        if ($this->statusFilter === 'HasTeam') {
            $data = $data->filter(fn($u) => $u->totalTeams > 0);
        } elseif ($this->statusFilter === 'NoTeam') {
            $data = $data->filter(fn($u) => $u->totalTeams === 0);
        }

        // Sorting
        $isDesc = $this->sortDir === 'desc';
        $data = $data->sortBy($this->sortKey, SORT_REGULAR, $isDesc);

        // Extract unique teams for filter
        $allTeams = collect($rekap['data'])->flatMap(fn($u) => collect($u->details)->pluck('teamName'))->unique()->sort();

        // Prepare charts data
        $charts = $this->prepareChartsData($rekap['data']);

        return view('livewire.dashboards.pimpinan-dashboard', [
            'rekap' => $data,
            'stats' => $rekap['stats'],
            'allTeams' => $allTeams,
            'charts' => $charts,
            'monthNames' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
            'detailUser' => $this->detailUserId ? collect($rekap['data'])->firstWhere('id', $this->detailUserId) : null,
            'reportUser' => $this->reportUserId ? \App\Models\User::find($this->reportUserId) : null,
        ]);
    }

    private function prepareChartsData($data)
    {
        // 1. Team Performance Distribution (Average Score)
        $teamSumMap = [];
        $teamCountMap = [];
        foreach ($data as $u) {
            foreach ($u->details as $d) {
                if ($d['score'] !== null && $d['score'] > 0) {
                    $t = $d['teamName'];
                    $teamSumMap[$t] = ($teamSumMap[$t] ?? 0) + $d['score'];
                    $teamCountMap[$t] = ($teamCountMap[$t] ?? 0) + 1;
                }
            }
        }
        
        $teamPerfMap = [];
        foreach ($teamSumMap as $t => $sum) {
            $teamPerfMap[$t] = round($sum / $teamCountMap[$t], 2);
        }
        
        arsort($teamPerfMap);
        $topTeams = array_slice($teamPerfMap, 0, 15);

        // 2. Perf Distribution
        $dist = ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0, 'Belum Dinilai' => 0];
        foreach ($data as $u) {
            if ($u->averageScore >= 90) $dist['Sangat Baik']++;
            elseif ($u->averageScore >= 80) $dist['Baik']++;
            elseif ($u->averageScore >= 60) $dist['Cukup']++;
            elseif ($u->averageScore > 0) $dist['Kurang']++;
            else $dist['Belum Dinilai']++;
        }

        // 3. Scatter
        $scatter = [];
        $totalX = 0; $totalY = 0; $countValid = 0;
        foreach ($data as $u) {
            if ($u->averageScore > 0) {
                $scatter[] = ['x' => $u->activeWorkTeams, 'y' => $u->averageScore, 'name' => $u->name];
                $totalX += $u->activeWorkTeams;
                $totalY += $u->averageScore;
                $countValid++;
            }
        }
        $avgX = $countValid > 0 ? $totalX / $countValid : 0;
        $avgY = $countValid > 0 ? $totalY / $countValid : 0;

        return [
            'teamSize' => ['labels' => array_keys($topTeams), 'series' => array_values($topTeams)],
            'perfDist' => ['labels' => array_keys($dist), 'series' => array_values($dist)],
            'scatter' => $scatter,
            'avgX' => round($avgX, 2),
            'avgY' => round($avgY, 2)
        ];
    }
}
