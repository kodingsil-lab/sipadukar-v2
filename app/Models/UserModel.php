<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_lengkap',
        'username',
        'email',
        'password_hash',
        'nip',
        'unit_kerja',
        'program_studi_id',
        'upps_id',
        'jabatan',
        'foto',
        'is_aktif',
        'terakhir_login',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'               => 'integer',
        'program_studi_id' => '?integer',
        'upps_id'          => '?integer',
        'is_aktif'         => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getAktif(): array
    {
        return $this->where('is_aktif', 1)
            ->orderBy('nama_lengkap', 'ASC')
            ->findAll();
    }

    public function getByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    public function getByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function getWithRoles(): array
    {
        $users = $this->select('users.*, program_studi.nama_program_studi as nama_program_studi_user, upps.nama_upps as nama_upps_user')
            ->join('program_studi', 'program_studi.id = users.program_studi_id', 'left')
            ->join('upps', 'upps.id = users.upps_id', 'left')
            ->where('users.deleted_at', null)
            ->orderBy('users.nama_lengkap', 'ASC')
            ->findAll();

        foreach ($users as &$user) {
            $roles = $this->getRolesByUserId((int) $user['id']);
            $user['roles'] = $roles;
            $user['role_names'] = implode(', ', array_map(static fn ($role) => $role['nama_role'], $roles));
            $user['role_slugs'] = array_map(static fn ($role) => $role['slug_role'], $roles);
        }

        return $users;
    }

    public function getDetailWithRoles(int $userId): ?array
    {
        $user = $this->where('users.id', $userId)
            ->select('users.*, program_studi.nama_program_studi as nama_program_studi_user, upps.nama_upps as nama_upps_user')
            ->join('program_studi', 'program_studi.id = users.program_studi_id', 'left')
            ->join('upps', 'upps.id = users.upps_id', 'left')
            ->where('users.deleted_at', null)
            ->first();

        if (! $user) {
            return null;
        }

        $roles = $this->getRolesByUserId($userId);
        $user['roles'] = $roles;
        $user['role_ids'] = array_map(static fn ($role) => (int) $role['id'], $roles);
        $user['role_names'] = implode(', ', array_map(static fn ($role) => $role['nama_role'], $roles));

        return $user;
    }

    public function getRolesByUserId(int $userId): array
    {
        return $this->db->table('user_roles')
            ->select('roles.*')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->orderBy('roles.nama_role', 'ASC')
            ->get()
            ->getResultArray();
    }
}
