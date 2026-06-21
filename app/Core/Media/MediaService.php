<?php

namespace App\Core\Media;

use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;

class MediaService
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const MAX_SIZE     = 10 * 1024 * 1024; // 10 MB

    private const SIZES = [
        'thumb'  => [150, 150],
        'medium' => [600, 400],
        'large'  => [1200, 800],
    ];

    public function upload(UploadedFile $file): array
    {
        if (! $file->isValid() || $file->hasMoved()) {
            throw new \RuntimeException('Geçersiz dosya.');
        }

        if (! in_array($file->getMimeType(), self::ALLOWED_MIME, true)) {
            throw new \RuntimeException('Desteklenmeyen dosya türü. (JPEG, PNG, WebP, GIF)');
        }

        if ($file->getSizeByUnit('bytes') > self::MAX_SIZE) {
            throw new \RuntimeException('Dosya 10MB sınırını aşıyor.');
        }

        $ext      = strtolower($file->getExtension());
        $uuid     = bin2hex(random_bytes(8));
        $filename = $uuid . '.' . $ext;
        $subDir   = date('Y/m');
        $fullDir  = FCPATH . 'uploads/' . $subDir . '/';

        if (! is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        $file->move($fullDir, $filename);
        $fullPath = $fullDir . $filename;

        [$width, $height] = @getimagesize($fullPath) ?: [null, null];

        // Thumbnail'lar oluştur
        foreach (self::SIZES as $label => [$w, $h]) {
            $this->makeThumb($fullPath, $fullDir, $uuid, $ext, $label, $w, $h);
        }

        $relPath = 'uploads/' . $subDir . '/' . $filename;

        $db   = db_connect();
        $data = [
            'filename'      => $filename,
            'original_name' => $file->getClientName(),
            'path'          => $relPath,
            'mime_type'     => $file->getMimeType(),
            'size'          => filesize($fullPath),
            'width'         => $width,
            'height'        => $height,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $db->table('media')->insert($data);
        $data['id'] = $db->insertID();

        return $data;
    }

    public function delete(int $id): void
    {
        $db  = db_connect();
        $row = $db->table('media')->where('id', $id)->get()->getRowArray();

        if ($row) {
            $db->table('media')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function getLibrary(int $page = 1, int $perPage = 40): array
    {
        $db     = db_connect();
        $offset = ($page - 1) * $perPage;

        return $db->table('media')
            ->where('deleted_at IS NULL', null, false)
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();
    }

    public function getCount(): int
    {
        return (int) db_connect()->table('media')->where('deleted_at IS NULL', null, false)->countAllResults();
    }

    public function findById(int $id): ?array
    {
        $row = db_connect()->table('media')->where('id', $id)->where('deleted_at IS NULL', null, false)->get()->getRowArray();
        return $row ?: null;
    }

    public function getUrl(array $row, string $size = 'original'): string
    {
        if ($size === 'original') {
            return base_url($row['path']);
        }

        // thumb, medium, large: uuid-thumb.jpg
        $info    = pathinfo($row['path']);
        $uuid    = pathinfo($row['filename'], PATHINFO_FILENAME);
        $ext     = $info['extension'];
        $dir     = $info['dirname'];
        $variant = $uuid . '-' . $size . '.' . $ext;

        return base_url($dir . '/' . $variant);
    }

    private function makeThumb(
        string $srcPath,
        string $destDir,
        string $uuid,
        string $ext,
        string $label,
        int $w,
        int $h
    ): void {
        $destPath = $destDir . $uuid . '-' . $label . '.' . $ext;

        try {
            if ($label === 'thumb') {
                \Config\Services::image()
                    ->withFile($srcPath)
                    ->fit($w, $h, 'center')
                    ->save($destPath);
            } else {
                \Config\Services::image()
                    ->withFile($srcPath)
                    ->resize($w, $h, true, 'auto')
                    ->save($destPath);
            }
        } catch (\Throwable) {
            // Thumbnail oluşturma başarısız olursa sessizce devam et
        }
    }
}
