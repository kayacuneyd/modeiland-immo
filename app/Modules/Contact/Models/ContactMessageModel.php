<?php

namespace App\Modules\Contact\Models;

use CodeIgniter\Model;

class ContactMessageModel extends Model
{
    protected $table         = 'contact_messages';
    protected $useTimestamps = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = ['name', 'email', 'subject', 'message', 'ip_address', 'is_read'];

    public function getUnreadCount(): int
    {
        return (int) $this->where('is_read', 0)->countAllResults();
    }

    public function getAllPaginated(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->orderBy('created_at', 'DESC')->limit($perPage, $offset)->findAll();
    }

    public function markAsRead(int $id): void
    {
        $this->update($id, ['is_read' => 1]);
    }
}
