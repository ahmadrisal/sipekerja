<?php

namespace App\Livewire\Dashboards;

use App\Services\DashboardService;
use Livewire\Component;

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

    public function render(DashboardService $service)
    {
        $stats = $service->getAdminStats();

        return view('livewire.dashboards.admin-dashboard', [
            'stats' => $stats,
        ]);
    }
}
