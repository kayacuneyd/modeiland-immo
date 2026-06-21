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

    private static bool $pragmasSet = false;

    public function __construct()
    {
        parent::__construct();

        if (! self::$pragmasSet) {
            $this->db->query('PRAGMA journal_mode=WAL');
            $this->db->query('PRAGMA foreign_keys=ON');
            self::$pragmasSet = true;
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
