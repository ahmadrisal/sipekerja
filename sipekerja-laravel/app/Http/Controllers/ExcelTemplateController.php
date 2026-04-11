<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Spatie\Permission\Models\Role;

class ExcelTemplateController extends Controller
{
    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Pegawai');

        // Get available roles
        $roles = Role::pluck('name')->toArray();
        $roleList = implode(', ', $roles);

        // === HEADERS ===
        $headers = ['NIP', 'Username', 'Nama Lengkap', 'Email', 'Password Sementara', 'Peran (Roles)'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4F46E5']]],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(24);

        // === EXAMPLE DATA (rows 2-3) ===
        $examples = [
            ['199001012020011001', 'johndoe', 'John Doe', 'john.doe@bps.go.id', 'Pass123!', 'Pegawai'],
            ['199502152021012002', 'janedoe', 'Jane Doe', 'jane.doe@bps.go.id', 'Pass456!', 'Ketua Tim, Pegawai'],
        ];

        foreach ($examples as $rowIdx => $row) {
            $rowNum = $rowIdx + 2;
            foreach ($row as $col => $val) {
                $sheet->setCellValueExplicit(chr(65 + $col) . $rowNum, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            // Light blue background for examples
            $sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']],
                'font' => ['italic' => true, 'color' => ['rgb' => '6366F1']],
            ]);
        }

        // === INSTRUCTION ROW ===
        $sheet->setCellValue('A4', '--- ISI DATA PEGAWAI MULAI DARI BARIS INI (hapus contoh di atas jika perlu) ---');
        $sheet->mergeCells('A4:F4');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
        ]);

        // === NOTES SHEET ===
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

        // Set active sheet back to template
        $spreadsheet->setActiveSheetIndex(0);

        // Write to temp and stream
        $fileName = 'template_import_pegawai.xlsx';
        $temp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($temp);

        return response()->download($temp, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
