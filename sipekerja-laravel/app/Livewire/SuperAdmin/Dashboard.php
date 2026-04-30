<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Rating;
use App\Models\Satker;
use App\Models\ScoringConfig;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $activeTab = 'dashboard';

    // Period filter (shared across tabs)
    public int $month;
    public int $year;

    // Distribution filter
    public ?string $distSatkerId = null;

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

    // Tambah user modal
    public bool $showAddUserModal = false;
    public ?string $addUserSatkerId = null;
    public string $addUserSatkerName = '';
    public string $addUserName = '';
    public string $addUserNip = '';
    public string $addUserEmail = '';
    public string $addUserPassword = '';
    public string $addUserRole = 'Pegawai';

    // Pindah user modal
    public bool $showMoveUserModal = false;
    public string $moveUserSearch = '';
    public ?string $moveUserId = null;
    public ?string $moveTargetSatkerId = null;

    // Kelola Pegawai tab
    public string $pgFilterSatker = '';
    public string $pgSearch = '';
    public string $pgFilterRole = 'ALL';

    // Edit user modal
    public bool $showEditUserModal = false;
    public ?string $editUserId = null;
    public string $editUserName = '';
    public string $editUserNip = '';
    public string $editUserEmail = '';
    public string $editUserUsername = '';
    public array $editUserRoles = [];
    public ?string $editUserSatkerId = null;

    // Reset password modal
    public bool $showResetPwModal = false;
    public ?string $resetPwUserId = null;
    public string $resetPwNew = '';

    // Delete user modal
    public bool $showDeleteUserModal = false;
    public ?string $deleteUserId = null;
    public string $deleteUserName = '';

    // Konfigurasi
    public array $configValues = [];
    public bool $maintenanceMode = false;

    protected $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year  = now()->year;
        $this->loadConfigValues();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->reset(['adminSearch', 'selectedUserId', 'moveUserSearch', 'moveUserId']);
        $this->resetPage();
        if ($tab === 'konfigurasi') $this->loadConfigValues();
    }

    public function updatedPgFilterSatker(): void { $this->resetPage(); }
    public function updatedPgSearch(): void { $this->resetPage(); }
    public function updatedPgFilterRole(): void { $this->resetPage(); }

    public function updatedMonth(): void {}
    public function updatedYear(): void {}
    public function updatedDistSatkerId(): void {}

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

    // ── Assign Admin ─────────────────────────────────────────────────

    public function openAssignAdmin(string $satkerId): void
    {
        $this->assignSatkerId   = $satkerId;
        $this->assignSatkerName = Satker::findOrFail($satkerId)->name;
        $this->adminSearch      = '';
        $this->selectedUserId   = null;
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

        $user->update(['satker_id' => $this->assignSatkerId]);
        if (!$user->hasRole('Admin')) $user->assignRole('Admin');

        $this->showAssignAdminModal = false;
        $this->reset(['assignSatkerId', 'assignSatkerName', 'adminSearch', 'selectedUserId']);
        session()->flash('success', "Admin kabkot berhasil di-assign ke {$satker->name}.");
    }

    // ── Tambah User ──────────────────────────────────────────────────

    public function openAddUser(string $satkerId): void
    {
        $this->addUserSatkerId   = $satkerId;
        $this->addUserSatkerName = Satker::findOrFail($satkerId)->name;
        $this->reset(['addUserName', 'addUserNip', 'addUserEmail', 'addUserPassword']);
        $this->addUserRole = 'Pegawai';
        $this->showAddUserModal = true;
    }

    public function addUser(): void
    {
        $this->validate([
            'addUserName'     => 'required|min:2|max:100',
            'addUserNip'      => 'required|unique:users,nip',
            'addUserEmail'    => 'required|email|unique:users,email',
            'addUserPassword' => 'required|min:6',
            'addUserRole'     => 'required|in:Pegawai,Ketua Tim,Pimpinan,Admin,Kepala Kabkot',
        ], [
            'addUserNip.unique'   => 'NIP sudah terdaftar.',
            'addUserEmail.unique' => 'Email sudah terdaftar.',
        ]);

        $user = User::create([
            'name'       => $this->addUserName,
            'nip'        => $this->addUserNip,
            'email'      => $this->addUserEmail,
            'password'   => Hash::make($this->addUserPassword),
            'satker_id'  => $this->addUserSatkerId,
        ]);

        $user->assignRole($this->addUserRole);

        $this->showAddUserModal = false;
        $this->reset(['addUserSatkerId', 'addUserSatkerName', 'addUserName', 'addUserNip', 'addUserEmail', 'addUserPassword']);
        session()->flash('success', "User {$user->name} berhasil ditambahkan.");
    }

    // ── Pindah User ──────────────────────────────────────────────────

    public function openMoveUser(): void
    {
        $this->reset(['moveUserSearch', 'moveUserId', 'moveTargetSatkerId']);
        $this->showMoveUserModal = true;
    }

    public function selectMoveUser(string $userId): void
    {
        $this->moveUserId = $userId;
    }

    public function moveUser(): void
    {
        if (!$this->moveUserId || !$this->moveTargetSatkerId) return;

        $user   = User::findOrFail($this->moveUserId);
        $satker = Satker::findOrFail($this->moveTargetSatkerId);

        $user->update(['satker_id' => $this->moveTargetSatkerId]);

        $this->showMoveUserModal = false;
        $this->reset(['moveUserSearch', 'moveUserId', 'moveTargetSatkerId']);
        session()->flash('success', "{$user->name} berhasil dipindahkan ke {$satker->name}.");
    }

    // ── Kelola Pegawai ────────────────────────────────────────────────

    public function openEditUser(string $id): void
    {
        $user = User::with('roles')->findOrFail($id);
        $this->editUserId       = $user->id;
        $this->editUserName     = $user->name;
        $this->editUserNip      = $user->nip;
        $this->editUserEmail    = $user->email;
        $this->editUserUsername = $user->username ?? '';
        $this->editUserRoles    = $user->roles->pluck('name')->toArray();
        $this->editUserSatkerId = $user->satker_id;
        $this->showEditUserModal = true;
    }

    public function saveEditUser(): void
    {
        $this->validate([
            'editUserName'     => 'required|min:2|max:100',
            'editUserNip'      => 'required|unique:users,nip,' . $this->editUserId,
            'editUserEmail'    => 'required|email|unique:users,email,' . $this->editUserId,
            'editUserUsername' => 'nullable|unique:users,username,' . $this->editUserId,
            'editUserRoles'    => 'required|array|min:1',
            'editUserSatkerId' => 'nullable|exists:satkers,id',
        ], [
            'editUserNip.unique'   => 'NIP sudah digunakan user lain.',
            'editUserEmail.unique' => 'Email sudah digunakan user lain.',
            'editUserRoles.min'    => 'Pilih minimal 1 role.',
        ]);

        $user = User::findOrFail($this->editUserId);
        $user->update([
            'name'      => $this->editUserName,
            'nip'       => $this->editUserNip,
            'email'     => $this->editUserEmail,
            'username'  => $this->editUserUsername ?: null,
            'satker_id' => $this->editUserSatkerId,
        ]);
        $user->syncRoles($this->editUserRoles);

        $this->showEditUserModal = false;
        $this->resetErrorBag();
        session()->flash('success', "Data {$user->name} berhasil diperbarui.");
    }

    public function openResetPw(string $id): void
    {
        $this->resetPwUserId = $id;
        $this->resetPwNew    = '';
        $this->showResetPwModal = true;
    }

    public function saveResetPw(): void
    {
        $this->validate(['resetPwNew' => 'required|min:6'], [
            'resetPwNew.min' => 'Password minimal 6 karakter.',
        ]);
        $user = User::findOrFail($this->resetPwUserId);
        $user->update(['password' => Hash::make($this->resetPwNew)]);
        $this->showResetPwModal = false;
        session()->flash('success', "Password {$user->name} berhasil direset.");
    }

    public function confirmDeleteUser(string $id): void
    {
        $user = User::findOrFail($id);
        $this->deleteUserId   = $id;
        $this->deleteUserName = $user->name;
        $this->showDeleteUserModal = true;
    }

    public function executeDeleteUser(): void
    {
        if ($this->deleteUserId === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri.');
            $this->showDeleteUserModal = false;
            return;
        }
        User::findOrFail($this->deleteUserId)->delete();
        $name = $this->deleteUserName;
        $this->showDeleteUserModal = false;
        $this->reset(['deleteUserId', 'deleteUserName']);
        session()->flash('success', "{$name} berhasil dihapus.");
    }

    private function getPegawaiQuery()
    {
        return User::with(['roles', 'teams', 'satker'])
            ->when($this->pgFilterSatker, fn($q) => $q->where('satker_id', $this->pgFilterSatker))
            ->when($this->pgSearch, function ($q) {
                $s = '%' . $this->pgSearch . '%';
                $q->where(fn($qq) => $qq
                    ->where('name', 'like', $s)
                    ->orWhere('nip', 'like', $s)
                    ->orWhere('email', 'like', $s));
            })
            ->when($this->pgFilterRole !== 'ALL', fn($q) => $q->role($this->pgFilterRole))
            ->orderBy('name');
    }

    // ── Konfigurasi ──────────────────────────────────────────────────

    private function loadConfigValues(): void
    {
        $c = ScoringConfig::getAll();
        $this->configValues = [
            'weight_score'        => $c['weight_score']        ?? 80,
            'weight_volume'       => $c['weight_volume']       ?? 10,
            'weight_quality'      => $c['weight_quality']      ?? 10,
            'volume_ringan'       => $c['volume_ringan']       ?? 60,
            'volume_sedang'       => $c['volume_sedang']       ?? 80,
            'volume_berat'        => $c['volume_berat']        ?? 100,
            'quality_kurang'      => $c['quality_kurang']      ?? 50,
            'quality_cukup'       => $c['quality_cukup']       ?? 75,
            'quality_baik'        => $c['quality_baik']        ?? 90,
            'quality_sangat_baik' => $c['quality_sangat_baik'] ?? 100,
        ];
        $this->maintenanceMode = ScoringConfig::getMaintenanceMode();
    }

    public function saveGlobalConfig(): void
    {
        $this->validate([
            'configValues.weight_score'   => 'required|numeric|min:0|max:100',
            'configValues.weight_volume'  => 'required|numeric|min:0|max:100',
            'configValues.weight_quality' => 'required|numeric|min:0|max:100',
        ]);

        $total = $this->configValues['weight_score']
               + $this->configValues['weight_volume']
               + $this->configValues['weight_quality'];

        if (abs($total - 100) > 0.01) {
            $this->addError('configValues.weight_score', "Total bobot harus 100 (saat ini: {$total}).");
            return;
        }

        foreach ($this->configValues as $key => $value) {
            ScoringConfig::setGlobal($key, (float) $value);
        }

        session()->flash('success', 'Konfigurasi global berhasil disimpan.');
    }

    public function toggleMaintenance(): void
    {
        ScoringConfig::setMaintenanceMode(!$this->maintenanceMode);
        $this->maintenanceMode = !$this->maintenanceMode;
    }

    // ── Data helpers ─────────────────────────────────────────────────

    private function getRekapData(): array
    {
        $satkers = Satker::where('is_active', true)
            ->orderByRaw("type = 'provinsi' DESC")
            ->orderBy('name')
            ->get();

        return $satkers->map(function (Satker $satker) {
            $totalPegawai = User::where('satker_id', $satker->id)
                ->whereHas('roles', fn($q) => $q->where('name', 'Pegawai'))
                ->count();

            $totalTim   = Team::where('satker_id', $satker->id)->count();
            $totalAdmin = User::where('satker_id', $satker->id)
                ->whereHas('roles', fn($q) => $q->where('name', 'Admin'))
                ->count();

            $rated = Rating::where('satker_id', $satker->id)
                ->where('period_month', $this->month)
                ->where('period_year', $this->year)
                ->distinct('target_user_id')
                ->count('target_user_id');

            $avgScore = Rating::where('satker_id', $satker->id)
                ->where('period_month', $this->month)
                ->where('period_year', $this->year)
                ->whereNotNull('final_score')
                ->avg('final_score');

            return [
                'satker'        => $satker,
                'total_pegawai' => $totalPegawai,
                'total_tim'     => $totalTim,
                'total_admin'   => $totalAdmin,
                'rated_count'   => $rated,
                'avg_score'     => $avgScore ? round($avgScore, 1) : null,
                'pct_rated'     => $totalPegawai > 0 ? round($rated / $totalPegawai * 100) : 0,
            ];
        })->all();
    }

    private function getDistributionData(?string $satkerId): array
    {
        $timPerPegawai = DB::table('team_members')
            ->join('users', 'team_members.user_id', '=', 'users.id')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->when($satkerId, fn($q) => $q->where('teams.satker_id', $satkerId))
            ->groupBy('users.id', 'users.name')
            ->select('users.name', DB::raw('COUNT(team_members.team_id) as jumlah'))
            ->orderByDesc('jumlah')
            ->limit(10)
            ->get();

        $pegawaiPerTim = DB::table('team_members')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->when($satkerId, fn($q) => $q->where('teams.satker_id', $satkerId))
            ->groupBy('teams.id', 'teams.team_name')
            ->select('teams.team_name as name', DB::raw('COUNT(team_members.user_id) as jumlah'))
            ->orderByDesc('jumlah')
            ->limit(10)
            ->get();

        return compact('timPerPegawai', 'pegawaiPerTim');
    }

    public function render()
    {
        $satkers = Satker::orderByRaw("type = 'provinsi' DESC")->orderBy('name')->get();

        $rekap = in_array($this->activeTab, ['dashboard', 'laporan']) ? $this->getRekapData() : [];

        // Global aggregate stats
        $globalStats = [];
        if ($this->activeTab === 'dashboard' && !empty($rekap)) {
            $globalStats = [
                'total_pegawai' => collect($rekap)->sum('total_pegawai'),
                'total_tim'     => collect($rekap)->sum('total_tim'),
                'total_admin'   => collect($rekap)->sum('total_admin'),
                'total_rated'   => collect($rekap)->sum('rated_count'),
                'avg_score'     => collect($rekap)->filter(fn($r) => $r['avg_score'] !== null)
                    ->avg('avg_score'),
                'satker_count'  => count($rekap),
            ];
            $globalStats['avg_score'] = $globalStats['avg_score']
                ? round($globalStats['avg_score'], 1) : null;
        }

        // Leaderboard (sort by avg_score desc, filter out null)
        $leaderboard = !empty($rekap)
            ? collect($rekap)->filter(fn($r) => $r['avg_score'] !== null)
                ->sortByDesc('avg_score')->values()->all()
            : [];

        // Distribution
        $distribution = $this->activeTab === 'dashboard'
            ? $this->getDistributionData($this->distSatkerId)
            : ['timPerPegawai' => collect(), 'pegawaiPerTim' => collect()];

        // User search for assign admin / move user
        $searchableUsers = collect();
        if ($this->showAssignAdminModal && strlen($this->adminSearch) >= 2) {
            $searchableUsers = User::where(fn($q) => $q
                ->where('name', 'like', "%{$this->adminSearch}%")
                ->orWhere('nip', 'like', "%{$this->adminSearch}%"))
                ->with('roles')->limit(10)->get();
        }

        $moveUserResults = collect();
        if ($this->showMoveUserModal && strlen($this->moveUserSearch) >= 2) {
            $moveUserResults = User::where(fn($q) => $q
                ->where('name', 'like', "%{$this->moveUserSearch}%")
                ->orWhere('nip', 'like', "%{$this->moveUserSearch}%"))
                ->with(['roles', 'satker'])->limit(10)->get();
        }

        // Kelola Pegawai
        $pegawaiList = $this->activeTab === 'pegawai' ? $this->getPegawaiQuery()->paginate(15) : collect();
        $allRoles    = ($this->activeTab === 'pegawai' || $this->showEditUserModal)
            ? Role::orderBy('name')->get()
            : collect();

        return view('livewire.super-admin.dashboard', [
            'satkers'         => $satkers,
            'rekap'           => $rekap,
            'globalStats'     => $globalStats,
            'leaderboard'     => $leaderboard,
            'distribution'    => $distribution,
            'searchableUsers' => $searchableUsers,
            'moveUserResults' => $moveUserResults,
            'monthNames'      => $this->monthNames,
            'pegawaiList'     => $pegawaiList,
            'allRoles'        => $allRoles,
        ])->title('Super Admin — PAKAR');
    }
}
