<?php

use App\Core\Media\MediaService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class MediaServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate   = true;
    protected $namespace = 'App';

    public function testDeleteSoftDeletesDatabaseRowAndRemovesFiles(): void
    {
        $dir = FCPATH . 'uploads/test';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $paths = [
            $dir . '/sample.jpg',
            $dir . '/sample-thumb.jpg',
            $dir . '/sample-medium.jpg',
            $dir . '/sample-large.jpg',
        ];

        foreach ($paths as $path) {
            file_put_contents($path, 'test');
        }

        $this->db->table('media')->insert([
            'filename'      => 'sample.jpg',
            'original_name' => 'sample.jpg',
            'path'          => 'uploads/test/sample.jpg',
            'mime_type'     => 'image/jpeg',
            'size'          => 4,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $id = $this->db->insertID();

        (new MediaService())->delete($id);

        foreach ($paths as $path) {
            $this->assertFileDoesNotExist($path);
        }

        $row = $this->db->table('media')->where('id', $id)->get()->getRowArray();
        $this->assertNotEmpty($row['deleted_at']);
    }
}
