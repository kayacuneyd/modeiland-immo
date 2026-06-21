<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Roller: mevcut role/user iliskilerini bozmamak icin truncate edilmez.
        $roles = [
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'permissions' => '["*"]',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Editor',
                'slug'        => 'editor',
                'permissions' => '["pages.*","blog.*","media.*"]',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($roles as $role) {
            $exists = $db->table('roles')->where('slug', $role['slug'])->countAllResults();
            if ($exists) {
                $db->table('roles')
                    ->where('slug', $role['slug'])
                    ->update([
                        'name'        => $role['name'],
                        'permissions' => $role['permissions'],
                        'updated_at'  => $role['updated_at'],
                    ]);
            } else {
                $db->table('roles')->insert($role);
            }
        }

        // Varsayılan ayarlar
        $settings = [
            ['key' => 'site.title',          'value' => 'CekirdekCMS',        'group' => 'general', 'type' => 'string', 'label' => 'Site Başlığı'],
            ['key' => 'site.tagline',         'value' => 'Yeniden kullanılabilir CI4 CMS çekirdeği', 'group' => 'general', 'type' => 'string', 'label' => 'Site Sloganı'],
            ['key' => 'site.email',           'value' => 'admin@example.com', 'group' => 'general', 'type' => 'string', 'label' => 'Site E-postası'],
            ['key' => 'site.description',     'value' => '',                  'group' => 'general', 'type' => 'string', 'label' => 'Site Açıklaması'],
            ['key' => 'site.logo',            'value' => '',                  'group' => 'general', 'type' => 'string', 'label' => 'Logo URL'],
            ['key' => 'site.favicon',         'value' => '',                  'group' => 'general', 'type' => 'string', 'label' => 'Favicon URL'],
            ['key' => 'app.maintenance_mode', 'value' => '0',                 'group' => 'general', 'type' => 'bool',   'label' => 'Bakım Modu'],
            ['key' => 'app.css_version',      'value' => '1',                 'group' => 'general', 'type' => 'string', 'label' => 'CSS Versiyon'],
            ['key' => 'mail.from_name',       'value' => 'CekirdekCMS',       'group' => 'mail',    'type' => 'string', 'label' => 'Gönderici Adı'],
            ['key' => 'mail.from_email',      'value' => 'noreply@example.com','group' => 'mail',   'type' => 'string', 'label' => 'Gönderici E-posta'],
            ['key' => 'mail.smtp_host',       'value' => 'smtp.example.com',  'group' => 'mail',    'type' => 'string', 'label' => 'SMTP Host'],
            ['key' => 'mail.smtp_port',       'value' => '587',               'group' => 'mail',    'type' => 'int',    'label' => 'SMTP Port'],
            ['key' => 'mail.smtp_user',       'value' => '',                  'group' => 'mail',    'type' => 'string', 'label' => 'SMTP Kullanıcı'],
            ['key' => 'mail.smtp_pass',       'value' => '',                  'group' => 'mail',    'type' => 'string', 'label' => 'SMTP Şifre'],
            ['key' => 'mail.encryption',      'value' => 'tls',               'group' => 'mail',    'type' => 'string', 'label' => 'Şifreleme (tls/ssl)'],
            ['key' => 'seo.robots_txt',       'value' => "User-agent: *\nAllow: /\nSitemap: /sitemap.xml", 'group' => 'seo', 'type' => 'string', 'label' => 'robots.txt İçeriği'],
            ['key' => 'seo.og_image',         'value' => '',                  'group' => 'seo',     'type' => 'string', 'label' => 'Varsayılan OG Görseli'],
            ['key' => 'footer.copyright',     'value' => '© ' . date('Y') . ' CekirdekCMS', 'group' => 'footer', 'type' => 'string', 'label' => 'Telif Hakkı Metni'],
            ['key' => 'contact.recipient',    'value' => 'admin@example.com', 'group' => 'contact', 'type' => 'string', 'label' => 'Mesaj Alıcısı'],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($settings as &$s) {
            $s['created_at'] = $now;
            $s['updated_at'] = $now;
        }
        unset($s);

        // Mevcut kayıtları atlayarak ekle (idempotent)
        foreach ($settings as $setting) {
            $exists = $db->table('settings')->where('key', $setting['key'])->countAllResults();
            if (! $exists) {
                $db->table('settings')->insert($setting);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('InitialDataSeeder başarısız oldu.');
        }

        if (is_cli() && ENVIRONMENT !== 'testing') {
            echo "Roller ve varsayılan ayarlar hazır.\n";
        }
    }
}
