<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Rating;
use App\Models\Satker;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $activeTab = 'rekap';

    // Satker management
    public bool $showSatkerModal = false;
    public string $satkerName = '';
    public string $satkerKode = '';
    public ?string $editingSatkerId = null;

    // Assign admin modal
    public bool $showAssignAdminModal = false;
    public ?string $assignSatkerId = null;
    public string $assignSatkerName = '';
    public string $adminSearch = '';
    public ?string $selectedUserId = null;

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->reset(['adminSearch', 'selectedUserId']);
    }

    // ── Satker CRUD ──────────────────────────────────────────────────

    public function openCreateSatker(): void
    {
        $this->reset(['satkerName', 'satkerKode', 'editingSatkerId']);
        $this->showSatkerModal = true;
    }

    public function openEditSatker(string $id): void
    {
        $satker = Satker::findOrFail($id);
        $this->editingSatkerId = $id;
        $this->satkerName = $satker->name;
        $this->satkerKode = $satker->kode ?? '';
        $this->showSatkerModal = true;
    }

    public function saveSatker(): void
    {
        $this->validate([
            'satkerName' => 'required|min:3|max:100',
            'satkerKode' => 'nullable|max:20|unique:satkers,kode' . ($this->editingSatkerId ? ",{$this->editingSatkerId}" : ''),
        ], [
            'satkerName.required' => 'Nama satker wajib diisi.',
            'satkerName.min'      => 'Nama satker minimal 3 karakter.',
            'satkerKode.unique'   => 'Kode satker sudah digunakan.',
        ]);

        if ($this->editingSatkerId) {
            Satker::findOrFail($this->editingSatkerId)->update([
                'name' => $this->satkerName,
                'kode' => $this->satkerKode ?: null,
            ]);
        } else {
            Satker::create([
                'name'      => $this->satkerName,
                'type'      => 'kabkot',
                'kode'      => $this->satkerKode ?: null,
                'is_active' => true,
            ]);
        }

        $this->showSatkerModal = false;
        $this->reset(['satkerName', 'satkerKode', 'editingSatkerId']);
        session()->flash('success', 'Satker berhasil disimpan.');
    }

    public function toggleSatkerActive(string $id): void
    {
        $satker = Satker::findOrFail($id);
        if ($satker->type === 'provinsi') return;
        $satker->update(['is_active' => !$satker->is_active]);
    }

    // ── Assign Admin Kabkot ──────────────────────────────────────────

    public function openAssignAdmin(string $satkerId): void
    {
        $this->assignSatkerId    = $satkerId;
        $this->assignSatkerName  = Satker::findOrFail($satkerId)->name;
        $this->adminSearch       = '';
        $this->selectedUserId    = null;
        $this->showAssignAdminModal = true;
    }

    public function selectUser(string $userId): void
    {
        $this->selectedUserId = $userId;
    }

    public function assignAdmin(): void
    {
        if (!$this->selectedUserId || !$this->assignSatkerId) return;

        $user   = User::findOrFail($this->selectedUserId);
        $satker = Satker::findOrFail($this->assignSatkerId);

        // Move user to the kabkot satker
        $user->update(['satker_id' => $this->assignSatkerId]);

        // Ensure user has Admin role
        if (!$user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }

        $this->showAssignAdminModal = false;
        $this->reset(['assignSatkerId', 'assignSatkerName', 'adminSearch', 'selectedUserId']);
        session()->flash('success', "Admin kabkot berhasil di-assign ke {$satker->name}.");
    }

    // ── Data helpers ─────────────────────────────────────────────────

    private function getRekapData(): array
    {
        $satkers = Satker::where('is_active', true)->orderByRaw("type = 'provinsi' DESC")->orderBy('name')->get();
        $month   = now()->month;
        $year    = now()->year;

        return $satkers->map(function (Satker $satker) use ($month, $year) {
            $totalPegawai = User::where('satker_id', $satker->id)
                ->whereHas('roles', fn($q) => $q->where('name', 'Pegawai'))
                ->count();

            $totalTim = Team::where('satker_id', $satker->id)->count();

            $totalAdmin = User::where('satker_id', $satker->id)
                ->whereHas('roles', fn($q) => $q->where('name', 'Admin'))
                ->count();

            // Ratings in this satker for current period
            $rated = Rating::where('satker_id', $satker->id)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->distinct('target_user_id')
                ->count();

            $avgScore = Rating::where('satker_id', $satker->id)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->where('score', '>', 0)
                ->avg('final_score');

            return [
                'satker'       => $satker,
                'total_pegawai'=> $totalPegawai,
                'total_tim'    => $totalTim,
                'total_admin'  => $totalAdmin,
                'rated_count'  => $rated,
                'avg_score'    => $avgScore ? round($avgScore, 1) : null,
                'pct_rated'    => $totalPegawai > 0 ? round($rated / $totalPegawai * 100) : 0,
            ];
        })->all();
    }

    public function render()
    {
        $satkers = Satker::orderByRaw("type = 'provinsi' DESC")->orderBy('name')->get();

        // Users not yet assigned to kabkot (still in provinsi) for assign admin search
        $searchableUsers = collect();
        if ($this->showAssignAdminModal && strlen($this->adminSearch) >= 2) {
            $searchableUsers = User::where('name', 'like', "%{$this->adminSearch}%")
                ->orWhere('nip', 'like', "%{$this->adminSearch}%")
                ->limit(10)
                ->get();
        }

        $rekap = $this->activeTab === 'rekap' ? $this->getRekapData() : [];

        return view('livewire.super-admin.dashboard', [
            'satkers'         => $satkers,
            'searchableUsers' => $searchableUsers,
            'rekap'           => $rekap,
        ])->title('Super Admin — PAKAR');
    }
}
