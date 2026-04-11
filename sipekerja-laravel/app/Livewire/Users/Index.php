<?php

namespace App\Livewire\Users;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterRole = 'ALL';
    public $filterTeam = 'ALL';

    // Form State
    public $isModalOpen = false;
    public $isResetModalOpen = false;
    public $userId = null;
    public $nip = '';
    public $username = '';
    public $name = '';
    public $email = '';
    public $password = '';
    public $selectedRoles = [];
    public $newPassword = '';

    // Import State
    public $isImportModalOpen = false;
    public $importFile = null;
    public $importParsed = false;
    public $importValid = [];
    public $importInvalid = [];

    // Data for dropdowns
    public $allRoles = [];
    public $uniqueTeams = [];

    // Bulk Actions
    public $selectedIds = [];
    public $selectAll = false;
    public $isBulkDeleteModalOpen = false;

    protected function rules()
    {
        return [
            'nip' => 'required|unique:users,nip,' . $this->userId,
            'username' => 'required|unique:users,username,' . $this->userId,
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable' : 'required|min:6',
        ];
    }

    public function mount()
    {
        $this->allRoles = Role::all();
        $this->uniqueTeams = \App\Models\Team::orderBy('team_name')->pluck('team_name')->toArray();
    }

    public function updatedSearch() { $this->resetPage(); $this->selectedIds = []; $this->selectAll = false; }
    public function updatedFilterRole() { $this->resetPage(); $this->selectedIds = []; $this->selectAll = false; }
    public function updatedFilterTeam() { $this->resetPage(); $this->selectedIds = []; $this->selectAll = false; }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->nip = $user->nip;
        $this->username = $user->username;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->isModalOpen = true;
    }

    public function openResetModal($id)
    {
        $this->userId = $id;
        $this->newPassword = '';
        $this->isResetModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function closeResetModal()
    {
        $this->isResetModalOpen = false;
        $this->newPassword = '';
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Pegawai');

        $roles = Role::pluck('name')->toArray();
        $roleList = implode(', ', $roles);

        $headers = ['NIP', 'Username', 'Nama Lengkap', 'Email', 'Password Sementara', 'Peran (Roles)'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValue(chr(65 + $col) . '1', $header);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '4F46E5']]],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(24);

        $examples = [
            ['199001012020011001', 'johndoe', 'John Doe', 'john.doe@bps.go.id', 'Pass123!', 'Pegawai'],
            ['199502152021012002', 'janedoe', 'Jane Doe', 'jane.doe@bps.go.id', 'Pass456!', 'Ketua Tim, Pegawai'],
        ];

        foreach ($examples as $rowIdx => $row) {
            $rowNum = $rowIdx + 2;
            foreach ($row as $col => $val) {
                $sheet->setCellValueExplicit(chr(65 + $col) . $rowNum, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']],
                'font' => ['italic' => true, 'color' => ['rgb' => '6366F1']],
            ]);
        }

        $sheet->setCellValue('A4', '--- ISI DATA PEGAWAI MULAI DARI BARIS INI (hapus contoh di atas jika perlu) ---');
        $sheet->mergeCells('A4:F4');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
        ]);

        $notesSheet = $spreadsheet->createSheet();
        $notesSheet->setTitle('Petunjuk');

        $notes = [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT PEGAWAI'],
            [''],
            ['Kolom', 'Keterangan', 'Wajib'],
            ['NIP', 'Nomor Induk Pegawai (unik untuk tiap pegawai)', 'Ya'],
            ['Username', 'Username login (unik, tanpa spasi)', 'Ya'],
            ['Nama Lengkap', 'Nama lengkap pegawai (min. 3 karakter)', 'Ya'],
            ['Email', 'Email valid dan unik', 'Ya'],
            ['Password Sementara', 'Password awal (min. 6 karakter)', 'Ya'],
            ['Peran (Roles)', "Pilih dari: {$roleList}. Untuk multi-role, pisahkan dengan koma.", 'Ya'],
            [''],
            ['CATATAN:'],
            ['- Baris contoh (baris 2-3) bisa dihapus atau ditimpa'],
            ['- Pastikan NIP, Username, dan Email tidak duplikat'],
            ['- Baris 4 (merah) adalah penanda, hapus sebelum import'],
        ];

        foreach ($notes as $rowIdx => $row) {
            $rowNum = $rowIdx + 1;
            foreach ((array)$row as $col => $val) {
                $notesSheet->setCellValue(chr(65 + $col) . $rowNum, $val);
            }
        }

        $notesSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('6366F1');
        $notesSheet->getStyle('A3:C3')->getFont()->setBold(true);
        $notesSheet->getColumnDimension('A')->setWidth(22);
        $notesSheet->getColumnDimension('B')->setWidth(55);
        $notesSheet->getColumnDimension('C')->setWidth(10);

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'template_import_pegawai.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }

    // === Import methods ===

    public function openImportModal()
    {
        $this->importFile = null;
        $this->importParsed = false;
        $this->importValid = [];
        $this->importInvalid = [];
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->importFile = null;
        $this->importParsed = false;
        $this->importValid = [];
        $this->importInvalid = [];
    }

    public function updatedImportFile()
    {
        if (!$this->importFile) return;

        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $this->parseExcel();
    }

    private function parseExcel()
    {
        $path = $this->importFile->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $validRoles = Role::pluck('name')->toArray();
        $existingNips = User::pluck('nip')->toArray();
        $existingUsernames = User::pluck('username')->toArray();
        $existingEmails = User::pluck('email')->toArray();

        $valid = [];
        $invalid = [];

        // Track duplicates within the file itself
        $fileNips = [];
        $fileUsernames = [];
        $fileEmails = [];

        foreach ($rows as $rowIdx => $row) {
            if ($rowIdx === 1) continue; // Skip header

            $nip = trim($row['A'] ?? '');
            $username = trim($row['B'] ?? '');
            $name = trim($row['C'] ?? '');
            $email = trim($row['D'] ?? '');
            $password = trim($row['E'] ?? '');
            $rolesStr = trim($row['F'] ?? '');

            // Skip empty rows
            if (empty($nip) && empty($name) && empty($email)) continue;

            // Skip instruction row
            if (str_contains(strtolower($nip), 'isi data') || str_contains(strtolower($nip), '---')) continue;

            $errors = [];

            // Validate required fields
            if (empty($nip)) $errors[] = 'NIP kosong';
            if (empty($username)) $errors[] = 'Username kosong';
            if (empty($name)) $errors[] = 'Nama kosong';
            if (strlen($name) < 3 && !empty($name)) $errors[] = 'Nama min 3 karakter';
            if (empty($email)) $errors[] = 'Email kosong';
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
            if (empty($password)) $errors[] = 'Password kosong';
            if (!empty($password) && strlen($password) < 6) $errors[] = 'Password min 6 karakter';

            // Check database uniqueness
            if (!empty($nip) && in_array($nip, $existingNips)) $errors[] = 'NIP sudah terdaftar';
            if (!empty($username) && in_array($username, $existingUsernames)) $errors[] = 'Username sudah terdaftar';
            if (!empty($email) && in_array($email, $existingEmails)) $errors[] = 'Email sudah terdaftar';

            // Check within-file duplicates
            if (!empty($nip) && in_array($nip, $fileNips)) $errors[] = 'NIP duplikat di file';
            if (!empty($username) && in_array($username, $fileUsernames)) $errors[] = 'Username duplikat di file';
            if (!empty($email) && in_array($email, $fileEmails)) $errors[] = 'Email duplikat di file';

            // Validate roles
            $rolesParsed = [];
            if (!empty($rolesStr)) {
                $rolesParsed = array_map('trim', explode(',', $rolesStr));
                foreach ($rolesParsed as $r) {
                    if (!in_array($r, $validRoles)) {
                        $errors[] = "Role '{$r}' tidak dikenal";
                    }
                }
            } else {
                $errors[] = 'Peran kosong';
            }

            $entry = [
                'row' => $rowIdx,
                'nip' => $nip,
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'roles' => $rolesParsed,
                'errors' => $errors,
            ];

            if (empty($errors)) {
                $valid[] = $entry;
                $fileNips[] = $nip;
                $fileUsernames[] = $username;
                $fileEmails[] = $email;
            } else {
                $invalid[] = $entry;
            }
        }

        $this->importValid = $valid;
        $this->importInvalid = $invalid;
        $this->importParsed = true;
    }

    public function confirmImport()
    {
        $count = 0;
        foreach ($this->importValid as $entry) {
            $user = User::create([
                'nip' => $entry['nip'],
                'username' => $entry['username'],
                'name' => $entry['name'],
                'email' => $entry['email'],
                'password' => Hash::make($entry['password']),
            ]);
            if (!empty($entry['roles'])) {
                $user->syncRoles($entry['roles']);
            }
            $count++;
        }

        $this->closeImportModal();
        session()->flash('success', "{$count} pegawai berhasil diimport.");
    }

    // === End Import ===

    public function resetForm()
    {
        $this->userId = null;
        $this->nip = '';
        $this->username = '';
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->selectedRoles = [];
        $this->resetErrorBag();
    }

    public function saveUser()
    {
        $this->validate();

        $userData = [
            'nip' => $this->nip,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $userData);
        $user->syncRoles($this->selectedRoles);

        $this->isModalOpen = false;
        $this->resetForm();
        session()->flash('success', 'Pengguna berhasil disimpan.');
    }

    public function resetPassword()
    {
        $this->validate(['newPassword' => 'required|min:6']);
        
        $user = User::findOrFail($this->userId);
        $user->update(['password' => Hash::make($this->newPassword)]);
        
        $this->isResetModalOpen = false;
        session()->flash('success', 'Password berhasil direset.');
    }

    // Delete State
    public $isDeleteModalOpen = false;
    public $deleteId = null;

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
        if ($this->deleteId === auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menghapus diri sendiri.');
            $this->cancelDelete();
            return;
        }
        User::findOrFail($this->deleteId)->delete();
        session()->flash('success', 'Pengguna berhasil dihapus.');
        $this->cancelDelete();
        $this->selectedIds = array_diff($this->selectedIds, [$this->deleteId]);
    }

    // Bulk Delete logic
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->getUsersQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds()
    {
        $allIds = $this->getUsersQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        if (count($this->selectedIds) === count($allIds) && count($allIds) > 0) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedIds)) return;
        $this->isBulkDeleteModalOpen = true;
    }

    public function cancelBulkDelete()
    {
        $this->isBulkDeleteModalOpen = false;
    }

    public function executeBulkDelete()
    {
        $idsToDelete = array_diff($this->selectedIds, [auth()->id()]);
        $count = count($idsToDelete);
        
        User::whereIn('id', $idsToDelete)->delete();
        
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->isBulkDeleteModalOpen = false;
        
        session()->flash('success', "{$count} pegawai berhasil dihapus.");
    }

    private function getUsersQuery()
    {
        $query = User::query()
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function($qq) use ($s) {
                    $qq->where('name', 'like', $s)
                      ->orWhere('nip', 'like', $s)
                      ->orWhere('username', 'like', $s)
                      ->orWhere('email', 'like', $s);
                });
            });

        if ($this->filterRole !== 'ALL') {
            $query->role($this->filterRole);
        }

        if ($this->filterTeam !== 'ALL') {
            $query->whereHas('teams', fn($t) => $t->where('team_name', $this->filterTeam));
        }

        return $query;
    }

    public function render()
    {
        return view('livewire.users.index', [
            'users' => $this->getUsersQuery()->latest()->paginate(10)
        ]);
    }
}
