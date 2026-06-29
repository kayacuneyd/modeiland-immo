<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['actor_type', 'actor_id', 'action', 'target', 'meta'];
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    public function record(
        string $action,
        ?string $target = null,
        ?array $meta = null,
        ?string $actorType = null,
        ?int $actorId = null
    ): void {
        $this->insert([
            'actor_type' => $actorType,
            'actor_id'   => $actorId,
            'action'     => $action,
            'target'     => $target,
            'meta'       => $meta ? json_encode($meta) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
