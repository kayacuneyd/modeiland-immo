<?php

namespace App\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DbBackup extends BaseCommand
{
    protected $group       = 'CekirdekCMS';
    protected $name        = 'db:backup';
    protected $description = 'SQLite veritabanını writable/backups/ altına yedekler.';

    public function run(array $params): void
    {
        $source    = WRITEPATH . 'database/cekirdek.db';
        $backupDir = WRITEPATH . 'backups/';

        if (! file_exists($source)) {
            CLI::error('Veritabanı dosyası bulunamadı: ' . $source);
            return;
        }

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp  = date('Ymd-His');
        $backupFile = $backupDir . 'cekirdek-' . $timestamp . '.db';

        // SQLite3::backup() hot backup (güvenli, kilit almadan çalışır)
        $sqlite  = new \SQLite3($source);
        $dest    = new \SQLite3($backupFile);
        $sqlite->backup($dest);
        $dest->close();
        $sqlite->close();

        // Bütünlük kontrolü
        $check = new \SQLite3($backupFile);
        $result = $check->querySingle('PRAGMA integrity_check');
        $check->close();

        if ($result !== 'ok') {
            CLI::error("Yedek bütünlük kontrolü başarısız: {$result}");
            unlink($backupFile);
            return;
        }

        $size = round(filesize($backupFile) / 1024, 1);
        CLI::write("✓ Yedek oluşturuldu: {$backupFile} ({$size} KB)", 'green');

        // 10'dan fazla yedek varsa en eskisini sil
        $backups = glob($backupDir . 'cekirdek-*.db');
        if (count($backups) > 10) {
            sort($backups);
            unlink($backups[0]);
            CLI::write('  Eski yedek silindi: ' . basename($backups[0]), 'dark_gray');
        }
    }
}
