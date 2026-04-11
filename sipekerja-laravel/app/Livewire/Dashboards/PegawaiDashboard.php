<?php

namespace App\Livewire\Dashboards;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PegawaiDashboard extends Component
{
    public $userId;
    public $isFromPimpinan = false;
    public $month;
    public $year;

    public $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function mount($userId = null, $month = null, $year = null, $isFromPimpinan = false)
    {
        $this->userId = $userId;
        $this->isFromPimpinan = $isFromPimpinan;
        $this->month = $month ?? date('n');
        $this->year = $year ?? date('Y');
    }

    public function updatedMonth()
    {
        $this->dispatch('refreshPegawaiCharts');
    }

    public function updatedYear()
    {
        $this->dispatch('refreshPegawaiCharts');
    }

    public function render(DashboardService $service)
    {
        $targetUserId = $this->userId ?? Auth::id();
        $data = $service->getPegawaiDashboard($targetUserId, $this->month, $this->year);

        return view('livewire.dashboards.pegawai-dashboard', [
            'data' => $data,
        ]);
    }
}
