<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class SavedSearchModel extends Model
{
    protected $table         = 'saved_searches';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['seeker_id', 'label', 'filters_json', 'alert_enabled', 'last_alerted_at'];
    protected $useTimestamps = false;

    public function getForSeeker(int $seekerId): array
    {
        return $this->where('seeker_id', $seekerId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /** Returns saved searches with alert_enabled=1 for cron processing. */
    public function getAlertsToSend(): array
    {
        return $this->db->table('saved_searches ss')
            ->select('ss.*, s.email AS seeker_email')
            ->join('seekers s', 's.id = ss.seeker_id')
            ->where('ss.alert_enabled', 1)
            ->whereIn('s.subscription_status', ['active', 'trial'])
            ->get()->getResultArray();
    }

    public function stampAlerted(int $id): void
    {
        $this->db->table('saved_searches')
            ->where('id', $id)
            ->update(['last_alerted_at' => date('Y-m-d H:i:s')]);
    }
}
