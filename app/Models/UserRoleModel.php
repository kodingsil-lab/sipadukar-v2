<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table            = 'user_roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'role_id',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
        'role_id' => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByUserId(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function hapusByUserId(int $userId): bool
    {
        return (bool) $this->where('user_id', $userId)->delete();
    }

    public function setRoles(int $userId, array $roleIds): void
    {
        $this->db->table($this->table)->where('user_id', $userId)->delete();

        $roleIds = array_unique(array_map('intval', $roleIds));

        foreach ($roleIds as $roleId) {
            if ($roleId <= 0) {
                continue;
            }

            $this->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);
        }
    }
}
