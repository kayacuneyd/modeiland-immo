<?php

namespace App\Core\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $dateFormat     = 'datetime';
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';

    private static bool $walSet = false;

    public function __construct()
    {
        parent::__construct();

        if (! self::$walSet) {
            $this->db->query('PRAGMA journal_mode=WAL');
            self::$walSet = true;
        }
    }

    public function scopePublished(): static
    {
        return $this->where($this->table . '.status', 'published');
    }

    public function scopeByLang(string $lang): static
    {
        return $this->where($this->table . '.lang', $lang);
    }
}
