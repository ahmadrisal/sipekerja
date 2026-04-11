<?php

namespace App\Livewire\Teams;



use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterLeader = 'ALL';
    public $filterSize = 'ALL';

    // Form State
    public $isModalOpen = false;
    public $teamId = null;
    public $teamName = '';
    public $leaderId = '';
    public $memberIds = [];

    // Search state for dropdowns inside modal
    public $searchLeader = '';
    public $searchMember = '';

    // Delete Modal State
    public $isDeleteModalOpen = false;
    public $deleteId = null;

    // All users for picker (cached in mount)
    public $allUsers = [];

    protected $rules = [
        'teamName' => 'required|min:3',
        'leaderId' => 'nullable|exists:users,id',
        'memberIds' => 'array',
    ];

    public function mount()
    {
        $this->allUsers = User::orderBy('name')->get(['id', 'name', 'nip'])->toArray();
    }

    public function updatedSearch() { $this->resetPage(); }

    public function setLeader($userId)
    {
        $this->leaderId = $userId;
        $this->searchLeader = '';
        // Leader is automatically removed from members if they were in it
        if (in_array($userId, $this->memberIds)) {
            $this->memberIds = array_diff($this->memberIds, [$userId]);
        }
    }

    public function removeLeader()
    {
        $this->leaderId = '';
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $team = Team::with('members')->findOrFail($id);
        $this->teamId = $team->id;
        $this->teamName = $team->team_name;
        $this->leaderId = $team->leader_id;
        $this->memberIds = $team->members->pluck('id')->toArray();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->teamId = null;
        $this->teamName = '';
        $this->leaderId = '';
        $this->memberIds = [];
        $this->searchLeader = '';
        $this->searchMember = '';
        $this->resetErrorBag();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->isDeleteModalOpen = true;
    }

    public function cancelDelete()
    {
        $this->isDeleteModalOpen = false;
        $this->deleteId = null;
    }

    public function executeDelete()
    {
        $team = Team::with('members')->findOrFail($this->deleteId);
        $oldLeaderId = $team->leader_id;
        $oldMemberIds = $team->members->pluck('id')->toArray();
        
        $team->delete();

        // Cleanup the leader's Spatie role if they no longer lead any team
        if ($oldLeaderId) {
            $stillLeader = Team::where('leader_id', $oldLeaderId)->exists();
            if (!$stillLeader) {
                $oldLeader = User::find($oldLeaderId);
                if ($oldLeader && $oldLeader->hasRole('Ketua Tim')) {
                    $oldLeader->removeRole('Ketua Tim');
                }
            }
        }

        // Cleanup former members' 'Pegawai' role if they are no longer in any team
        foreach ($oldMemberIds as $mId) {
            $stillMember = DB::table('team_members')->where('user_id', $mId)->exists();
            if (!$stillMember) {
                $user = User::find($mId);
                if ($user && $user->hasRole('Pegawai')) {
                    $user->removeRole('Pegawai');
                }
            }
        }

        session()->flash('success', 'Tim berhasil dihapus beserta penyesuaian hak akses.');
        $this->cancelDelete();
    }

    public function saveTeam()
    {
        $this->validate();

        DB::transaction(function () {
            // Get original team if updating, to track the old leader and old members
            $oldLeaderId = null;
            $oldMemberIds = [];
            
            if ($this->teamId) {
                $oldTeam = Team::with('members')->find($this->teamId);
                if ($oldTeam) {
                    $oldLeaderId = $oldTeam->leader_id;
                    $oldMemberIds = $oldTeam->members->pluck('id')->toArray();
                }
            }

            $team = Team::updateOrCreate(
                ['id' => $this->teamId],
                [
                    'team_name' => $this->teamName,
                    'leader_id' => $this->leaderId ?: null,
                ]
            );

            $team->members()->sync($this->memberIds);

            // ============================================
            // ROLE ASSIGNMENT: KETUA TIM
            // ============================================
            if ($this->leaderId) {
                $newLeader = User::find($this->leaderId);
                if ($newLeader && !$newLeader->hasRole('Ketua Tim')) {
                    $newLeader->assignRole('Ketua Tim');
                }
            }
            if ($oldLeaderId && $oldLeaderId !== $this->leaderId) {
                $stillLeader = Team::where('leader_id', $oldLeaderId)->exists();
                if (!$stillLeader) {
                    $oldLeader = User::find($oldLeaderId);
                    if ($oldLeader && $oldLeader->hasRole('Ketua Tim')) {
                        $oldLeader->removeRole('Ketua Tim');
                    }
                }
            }

            // ============================================
            // ROLE ASSIGNMENT: PEGAWAI (ANGGOTA TIM)
            // ============================================
            $addedMemberIds = array_diff($this->memberIds, $oldMemberIds);
            $removedMemberIds = array_diff($oldMemberIds, $this->memberIds);

            // 1. Assign role to new members
            foreach ($addedMemberIds as $mId) {
                $user = User::find($mId);
                if ($user && !$user->hasRole('Pegawai')) {
                    $user->assignRole('Pegawai');
                }
            }

            // 2. Revoke role from removed members (only if they aren't in any other team)
            foreach ($removedMemberIds as $mId) {
                $stillMember = DB::table('team_members')->where('user_id', $mId)->exists();
                if (!$stillMember) {
                    $user = User::find($mId);
                    if ($user && $user->hasRole('Pegawai')) {
                        $user->removeRole('Pegawai');
                    }
                }
            }
        });

        $this->isModalOpen = false;
        $this->resetForm();
        session()->flash('success', 'Tim berhasil disimpan, hak akses otomatis disesuaikan.');
    }

    public function toggleMember($userId)
    {
        if (in_array($userId, $this->memberIds)) {
            $this->memberIds = array_diff($this->memberIds, [$userId]);
        } else {
            $this->memberIds[] = $userId;
        }
    }

    public function render()
    {
        $query = Team::with(['leader', 'members'])
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where('team_name', 'like', $s)
                  ->orWhereHas('leader', fn($l) => $l->where('name', 'like', $s)->orWhere('nip', 'like', $s))
                  ->orWhereHas('members', fn($m) => $m->where('name', 'like', $s)->orWhere('nip', 'like', $s));
            });

        // Filter Leader
        if ($this->filterLeader === 'HAS_LEADER') $query->whereNotNull('leader_id');
        if ($this->filterLeader === 'NO_LEADER') $query->whereNull('leader_id');

        $teams = $query->latest()->get();

        // Size Filter (done in PHP for simplicity with derived count)
        if ($this->filterSize !== 'ALL') {
            $teams = $teams->filter(function($t) {
                $count = $t->members->count();
                if ($this->filterSize === 'EMPTY') return $count === 0;
                if ($this->filterSize === 'SMALL') return $count > 0 && $count <= 5;
                if ($this->filterSize === 'LARGE') return $count > 5;
                return true;
            });
        }

        return view('livewire.teams.index', [
            'teams' => $teams
        ]);
    }
}
