<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Index extends Component
{
    use WithPagination, WithFileUploads;

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

    // Import State
    public $isImportModalOpen = false;
    public $importFile = null;
    public $importParsed = false;
    public $importTeams = [];
    public $importErrors = [];

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

    // ── Import Tim ───────────────────────────────────────────────────

    public function openImportModal(): void
    {
        $this->importFile   = null;
        $this->importParsed = false;
        $this->importTeams  = [];
        $this->importErrors = [];
        $this->isImportModalOpen = true;
    }

    public function closeImportModal(): void
    {
        $this->isImportModalOpen = false;
        $this->importFile   = null;
        $this->importParsed = false;
        $this->importTeams  = [];
        $this->importErrors = [];
    }

    public function updatedImportFile(): void
    {
        if (!$this->importFile) return;

        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $this->parseImportExcel();
    }

    private function parseImportExcel(): void
    {
        $path        = $this->importFile->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        // Preload all users keyed by NIP for O(1) lookup
        $allUsers       = User::get(['id', 'name', 'nip']);
        $usersByNip     = $allUsers->keyBy(fn($u) => trim((string) $u->nip));
        $existingNames  = Team::pluck('team_name')->map(fn($n) => strtolower(trim($n)))->toArray();

        // --- Pass 1: group rows by team name ---
        $rawGroups  = [];  // ['team_name' => ['leader_nip' => ..., 'member_nips' => [...]]]
        $teamOrder  = [];  // preserve order
        $currentKey = null;

        foreach ($rows as $rowIdx => $row) {
            if ($rowIdx === 1) continue; // header

            $teamName  = trim($row['A'] ?? '');
            $leaderNip = trim($row['B'] ?? '');
            $memberNip = trim($row['C'] ?? '');

            // Skip fully empty rows
            if ($teamName === '' && $leaderNip === '' && $memberNip === '') continue;

            // Skip instruction/marker rows
            if (str_contains(strtolower($teamName), 'isi data') || str_contains($teamName, '---')) continue;

            if ($teamName !== '') {
                $currentKey = strtolower($teamName);
                if (!isset($rawGroups[$currentKey])) {
                    $rawGroups[$currentKey]  = ['original_name' => $teamName, 'leader_nip' => '', 'member_nips' => []];
                    $teamOrder[]             = $currentKey;
                }
                if ($leaderNip !== '' && $rawGroups[$currentKey]['leader_nip'] === '') {
                    $rawGroups[$currentKey]['leader_nip'] = $leaderNip;
                }
            }

            if ($memberNip !== '' && $currentKey !== null) {
                $rawGroups[$currentKey]['member_nips'][] = $memberNip;
            }
        }

        // --- Pass 2: validate each team group ---
        $validTeams   = [];
        $errorGroups  = [];
        $seenNames    = []; // within-file duplicate detection

        foreach ($teamOrder as $key) {
            $group      = $rawGroups[$key];
            $teamName   = $group['original_name'];
            $leaderNip  = $group['leader_nip'];
            $memberNips = $group['member_nips'];
            $errors     = [];

            // Team name validation
            if (strlen($teamName) < 3) {
                $errors[] = 'Nama tim kurang dari 3 karakter';
            }
            if (in_array($key, $seenNames)) {
                // Duplicate in file — already merged in pass 1, no error needed
            }
            $seenNames[] = $key;

            // Duplicate in DB
            if (in_array(strtolower($teamName), $existingNames)) {
                $errors[] = "Nama tim '{$teamName}' sudah ada di database";
            }

            // Leader validation
            $leaderId   = null;
            $leaderName = null;
            if ($leaderNip !== '') {
                $leaderUser = $usersByNip->get($leaderNip);
                if (!$leaderUser) {
                    $errors[] = "NIP Ketua Tim tidak ditemukan: {$leaderNip}";
                } else {
                    $leaderId   = $leaderUser->id;
                    $leaderName = $leaderUser->name;
                }
            }

            // Must have at least leader or one member
            if ($leaderNip === '' && count($memberNips) === 0) {
                $errors[] = 'Tim tidak memiliki ketua maupun anggota';
            }

            if (!empty($errors)) {
                $errorGroups[] = ['team_name' => $teamName, 'reasons' => $errors];
                continue;
            }

            // Member validation — invalid members are skipped individually
            $validMembers   = [];
            $skippedMembers = [];
            foreach ($memberNips as $nip) {
                $u = $usersByNip->get($nip);
                if (!$u) {
                    $skippedMembers[] = ['nip' => $nip, 'reason' => 'NIP tidak ditemukan'];
                } elseif ($u->id === $leaderId) {
                    // skip silently — leader not counted as member
                } else {
                    // deduplicate within team
                    if (!collect($validMembers)->contains('id', $u->id)) {
                        $validMembers[] = ['id' => $u->id, 'name' => $u->name, 'nip' => $u->nip];
                    }
                }
            }

            $validTeams[] = [
                'team_name'       => $teamName,
                'leader_id'       => $leaderId,
                'leader_name'     => $leaderName,
                'leader_nip'      => $leaderNip ?: null,
                'members'         => $validMembers,
                'member_ids'      => array_column($validMembers, 'id'),
                'skipped_members' => $skippedMembers,
            ];
        }

        $this->importTeams  = $validTeams;
        $this->importErrors = $errorGroups;
        $this->importParsed = true;
    }

    public function confirmImport(): void
    {
        if (empty($this->importTeams)) return;

        $count = 0;
        foreach ($this->importTeams as $entry) {
            DB::transaction(function () use ($entry) {
                $team = Team::create([
                    'team_name' => $entry['team_name'],
                    'leader_id' => $entry['leader_id'],
                ]);

                $team->members()->sync($entry['member_ids']);

                // Assign Ketua Tim role
                if ($entry['leader_id']) {
                    $leader = User::find($entry['leader_id']);
                    if ($leader && !$leader->hasRole('Ketua Tim')) {
                        $leader->assignRole('Ketua Tim');
                    }
                }

                // Assign Pegawai role to members
                foreach ($entry['member_ids'] as $mId) {
                    $user = User::find($mId);
                    if ($user && !$user->hasRole('Pegawai')) {
                        $user->assignRole('Pegawai');
                    }
                }
            });
            $count++;
        }

        $this->closeImportModal();
        session()->flash('success', "{$count} tim berhasil diimport, hak akses otomatis disesuaikan.");
    }

    public function downloadImportTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Tim');

        // Headers
        $headers = ['Nama Tim', 'NIP Ketua Tim', 'NIP Anggota'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
            $sheet->getStyle("{$col}1")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth($i === 0 ? 30 : 22);
        }

        // Example data
        $examples = [
            ['Tim Pengawasan Wilayah I', '199001012010011001', '199501012020011001'],
            ['',                         '',                   '199501012020011002'],
            ['',                         '',                   '199501012020011003'],
            ['Tim Pengawasan Wilayah II', '199002022010011002', '199601012021011001'],
            ['',                          '',                   '199601012021011002'],
        ];

        foreach ($examples as $ri => $row) {
            foreach ($row as $ci => $val) {
                $col = chr(65 + $ci);
                $sheet->setCellValue("{$col}" . ($ri + 2), $val);
            }
        }

        // Instruction row (row 7, light red)
        $sheet->setCellValue('A7', '← Nama Tim cukup diisi di baris pertama tiap tim');
        $sheet->setCellValue('B7', '← NIP Ketua Tim cukup di baris pertama');
        $sheet->setCellValue('C7', '← NIP Anggota satu per baris');
        $sheet->getStyle('A7:C7')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '991B1B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
        ]);

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'template_import_tim.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
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
