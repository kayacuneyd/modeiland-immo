<?php

namespace App\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Setup extends BaseCommand
{
    protected $group       = 'CekirdekCMS';
    protected $name        = 'setup';
    protected $description = 'İlk kurulum: .env kopyala, migration çalıştır, seed et, admin kullanıcı oluştur.';

    public function run(array $params): void
    {
        CLI::write('=== CekirdekCMS Kurulum Sihirbazı ===', 'cyan');
        CLI::newLine();

        // 1. .env kontrolü
        $envCreated = false;
        if (! file_exists(ROOTPATH . '.env')) {
            if (file_exists(ROOTPATH . '.env.example')) {
                copy(ROOTPATH . '.env.example', ROOTPATH . '.env');
                CLI::write('✓ .env oluşturuldu (.env.example kopyalandı)', 'green');
                $envCreated = true;
            } else {
                CLI::write('⚠ .env.example bulunamadı. .env manuel oluşturun.', 'yellow');
            }
        } else {
            CLI::write('✓ .env zaten mevcut', 'green');
        }

        // 1b. Encryption key üret (eksikse)
        $envPath = ROOTPATH . '.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (str_contains($envContent, 'encryption.key =') &&
                ! preg_match('/^encryption\.key\s*=\s*.+$/m', $envContent)) {
                $this->call('key:generate');
                CLI::write('✓ Encryption key üretildi', 'green');
            }
        }

        // 2. Database dizini
        $dbDir = WRITEPATH . 'database';
        if (! is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        CLI::write('✓ Database dizini hazır', 'green');

        // 3. Migration
        CLI::write('Migration çalıştırılıyor...', 'yellow');
        $this->call('migrate', ['--all' => null]);

        // 4. Seed
        CLI::write('Veriler seed ediliyor...', 'yellow');
        $this->call('db:seed', ['InitialDataSeeder']);

        // 5. Admin kullanıcı
        CLI::newLine();
        CLI::write('Admin kullanıcı oluşturma:', 'cyan');

        $name  = CLI::prompt('Ad Soyad', 'Admin');
        $email = CLI::prompt('E-posta', 'admin@example.com');

        // E-posta kontrolü
        $db = db_connect();
        $exists = $db->table('users')->where('email', $email)->countAllResults();
        if ($exists) {
            CLI::write("⚠ Bu e-posta zaten kayıtlı. Admin kullanıcı atlandı.", 'yellow');
        } else {
            $password  = CLI::prompt('Şifre (min 8 karakter)');
            $password2 = CLI::prompt('Şifre tekrar');

            if ($password !== $password2) {
                CLI::error('Şifreler eşleşmiyor. Kurulumu tekrar çalıştırın.');
                return;
            }

            if (strlen($password) < 8) {
                CLI::error('Şifre en az 8 karakter olmalı.');
                return;
            }

            $adminRole = $db->table('roles')->where('slug', 'admin')->get()->getRowArray();
            if (! $adminRole) {
                CLI::error('Admin rolü bulunamadı. InitialDataSeeder sonucunu kontrol edin.');
                return;
            }

            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->table('users')->insert([
                'name'          => $name,
                'email'         => $email,
                'password_hash' => $hash,
                'role_id'       => $adminRole['id'],
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            CLI::write("✓ Admin kullanıcı oluşturuldu: {$email}", 'green');
        }

        CLI::newLine();
        CLI::write('=== Kurulum tamamlandı! ===', 'cyan');
        CLI::write('Admin panele erişmek için: /admin/login', 'white');
    }
}
