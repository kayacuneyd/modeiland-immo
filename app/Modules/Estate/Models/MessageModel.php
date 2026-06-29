<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table         = 'messages';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['listing_id', 'seeker_id', 'owner_id', 'body', 'read_at'];
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    public function getForOwner(int $ownerId): array
    {
        return $this->db->table('messages m')
            ->select('m.*, s.email AS seeker_email, l.location_approx AS listing_location')
            ->join('seekers s', 's.id = m.seeker_id', 'left')
            ->join('listings l', 'l.id = m.listing_id', 'left')
            ->where('m.owner_id', $ownerId)
            ->orderBy('m.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function countUnreadForOwner(int $ownerId): int
    {
        return (int) $this->where('owner_id', $ownerId)
                          ->where('read_at', null)
                          ->countAllResults();
    }

    public function markRead(int $messageId): void
    {
        $this->db->table('messages')
            ->where('id', $messageId)
            ->update(['read_at' => date('Y-m-d H:i:s')]);
    }

    public function getForSeeker(int $seekerId): array
    {
        return $this->db->table('messages m')
            ->select('m.*, l.location_approx AS listing_location, o.display_name AS owner_name')
            ->join('listings l', 'l.id = m.listing_id', 'left')
            ->join('owners o', 'o.id = m.owner_id', 'left')
            ->where('m.seeker_id', $seekerId)
            ->orderBy('m.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function countUnreadForSeeker(int $seekerId): int
    {
        return (int) $this->where('seeker_id', $seekerId)
                          ->where('read_at', null)
                          ->countAllResults();
    }
}
