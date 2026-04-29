<?php
/**
 * PAKAR — Migration & Setup Runner
 * PENTING: Hapus file ini dari server setelah selesai digunakan!
 */

@set_time_limit(300);
@ini_set('memory_limit', '256M');
@ini_set('display_errors', '1');
error_reporting(E_ALL);

// Flush output segera ke browser (hindari buffering)
if (ob_get_level()) ob_end_clean();

$secret = 'PAKAR_MIGRATE_2026';

if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    die('<h2 style="font-family:monospace;color:red">403 Forbidden — Sertakan ?key=PAKAR_MIGRATE_2026</h2>');
}

function out(string $text): void {
    echo $text . "\n";
    if (ob_get_level()) ob_flush();
    flush();
}

define('LARAVEL_START', microtime(true));

// ── Hapus config cache SEBELUM bootstrap agar .env dibaca segar ──────
$cacheDir = __DIR__ . '/../bootstrap/cache/';
foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php'] as $f) {
    if (file_exists($cacheDir . $f)) {
        @unlink($cacheDir . $f);
    }
}

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px">';
out("=== PAKAR Migration & Setup Runner ===");
out("Waktu  : " . date('Y-m-d H:i:s'));
out("PHP    : " . PHP_VERSION);
out("Memory : " . ini_get('memory_limit'));
out("Timeout: " . ini_get('max_execution_time') . "s");
out("");

// ── Bootstrap Laravel ────────────────────────────────────────────────
out("--- [BOOT] Bootstrap Laravel ---");
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    out("  OK: Laravel siap.");
} catch (\Throwable $e) {
    out("  ERROR Bootstrap: " . $e->getMessage());
    out("  File : " . $e->getFile() . ":" . $e->getLine());
    echo '</pre>'; exit(1);
}
out("");

use Spatie\Permission\Models\Role;
use App\Models\ScoringConfig;

out("DB Connection : " . config('database.default'));
out("DB Path       : " . config('database.connections.sqlite.database'));
out("");

// ── 0. Cek & Permission SQLite ───────────────────────────────────────
out("--- [0/4] Cek SQLite ---");
$dbPath = database_path('database.sqlite');
$dbDir  = dirname($dbPath);

out("  Path : {$dbPath}");
out("  Dir writable  : " . (is_writable($dbDir) ? 'YA' : 'TIDAK ← MASALAH!'));

if (!file_exists($dbPath)) {
    if (@touch($dbPath)) {
        @chmod($dbPath, 0664);
        out("  File dibuat: OK");
    } else {
        out("  ERROR: Tidak bisa membuat file SQLite! Cek permission folder database/");
        echo '</pre>'; exit(1);
    }
} else {
    out("  File ada: " . (is_writable($dbPath) ? 'writable OK' : 'TIDAK WRITABLE ← MASALAH!'));
    if (!is_writable($dbPath)) {
        if (@chmod($dbPath, 0664)) {
            out("  Permission diperbaiki ke 0664: OK");
        } else {
            out("  Gagal set permission. Set manual di File Manager ke 664.");
            echo '</pre>'; exit(1);
        }
    }
}
out("");

// ── 1. Migrasi ───────────────────────────────────────────────────────
out("--- [1/4] php artisan migrate --force ---");
try {
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    $migrateOut = trim(Artisan::output());
    out($migrateOut ?: "(tidak ada output)");
    out("Exit code: {$exitCode}");
    if ($exitCode !== 0) {
        out("  PERINGATAN: Migrasi mungkin tidak sempurna (exit {$exitCode}).");
    }
} catch (\Throwable $e) {
    out("  ERROR Migrasi: " . $e->getMessage());
    out("  File: " . $e->getFile() . ":" . $e->getLine());
    echo '</pre>'; exit(1);
}
out("");

// ── 2. Roles ─────────────────────────────────────────────────────────
out("--- [2/4] Sinkronisasi Roles ---");
try {
    $requiredRoles = ['Super Admin', 'Admin', 'Pimpinan', 'Kepala Kabkot', 'Ketua Tim', 'Pegawai'];
    foreach ($requiredRoles as $roleName) {
        $role    = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $created = $role->wasRecentlyCreated ? ' <- BARU dibuat' : ' (sudah ada)';
        out("  Role: {$roleName}{$created}");
    }
} catch (\Throwable $e) {
    out("  ERROR Roles: " . $e->getMessage());
}
out("");

// ── 3. Satker Seeder ─────────────────────────────────────────────────
out("--- [3/5] Satker Provinsi (seed) ---");
try {
    Artisan::call('db:seed', ['--class' => 'SatkerSeeder', '--force' => true]);
    out("  SatkerSeeder -> " . trim(Artisan::output() ?: 'OK'));
} catch (\Throwable $e) {
    out("  ERROR SatkerSeeder: " . $e->getMessage());
}
out("");

// ── 4. Scoring Configs ───────────────────────────────────────────────
out("--- [3/4] Scoring Configs ---");
try {
    $count = ScoringConfig::count();
    if ($count === 0) {
        foreach (ScoringConfig::defaults() as $d) {
            ScoringConfig::create($d);
        }
        out("  " . count(ScoringConfig::defaults()) . " konfigurasi default berhasil diisi.");
    } else {
        out("  Sudah ada {$count} konfigurasi, dilewati.");
    }
} catch (\Throwable $e) {
    out("  ERROR ScoringConfig: " . $e->getMessage());
}
out("");

// ── 5. Cache ─────────────────────────────────────────────────────────
out("--- [5/5] Clear & Rebuild Cache ---");
try {
    Artisan::call('config:cache'); out("  config:cache -> " . trim(Artisan::output() ?: 'OK'));
    Artisan::call('route:cache');  out("  route:cache  -> " . trim(Artisan::output() ?: 'OK'));
    Artisan::call('view:clear');   out("  view:clear   -> " . trim(Artisan::output() ?: 'OK'));
    Artisan::call('cache:clear');  out("  cache:clear  -> " . trim(Artisan::output() ?: 'OK'));
} catch (\Throwable $e) {
    out("  ERROR Cache: " . $e->getMessage());
}
out("");

out("=== SELESAI " . date('H:i:s') . " ===");
out("Durasi: " . round(microtime(true) - LARAVEL_START, 2) . "s");
out("");
out("⚠ SEGERA HAPUS file ini dari server:");
out("  public/migrate-runner.php");
echo '</pre>';
