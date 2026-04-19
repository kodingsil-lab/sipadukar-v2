<?php

namespace App\Models;

use CodeIgniter\Model;

class UserProgramStudiAssignmentModel extends Model
{
    protected $table            = 'user_program_studi_assignments';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'program_studi_id',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'               => 'integer',
        'user_id'          => 'integer',
        'program_studi_id' => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getProgramStudiIdsByUserId(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $rows = $this->select('program_studi_id')
            ->where('user_id', $userId)
            ->orderBy('program_studi_id', 'ASC')
            ->findAll();

        $ids = [];
        foreach ($rows as $row) {
            $programStudiId = (int) ($row['program_studi_id'] ?? 0);
            if ($programStudiId > 0) {
                $ids[$programStudiId] = $programStudiId;
            }
        }

        return array_values($ids);
    }

    public function syncAssignments(int $userId, array $programStudiIds): void
    {
        if ($userId <= 0) {
            return;
        }

        $normalizedIds = [];
        foreach ($programStudiIds as $programStudiId) {
            $safeId = (int) $programStudiId;
            if ($safeId > 0) {
                $normalizedIds[$safeId] = $safeId;
            }
        }

        $normalizedIds = array_values($normalizedIds);

        $this->where('user_id', $userId)->delete();

        foreach ($normalizedIds as $programStudiId) {
            $this->insert([
                'user_id' => $userId,
                'program_studi_id' => $programStudiId,
            ]);
        }
    }

    public function getAssignmentNamesMapByUserIds(array $userIds): array
    {
        $normalizedUserIds = [];
        foreach ($userIds as $userId) {
            $safeUserId = (int) $userId;
            if ($safeUserId > 0) {
                $normalizedUserIds[$safeUserId] = $safeUserId;
            }
        }

        if (empty($normalizedUserIds)) {
            return [];
        }

        $rows = $this->select('user_program_studi_assignments.user_id, program_studi.nama_program_studi, program_studi.jenjang')
            ->join('program_studi', 'program_studi.id = user_program_studi_assignments.program_studi_id', 'left')
            ->whereIn('user_program_studi_assignments.user_id', array_values($normalizedUserIds))
            ->orderBy('program_studi.nama_program_studi', 'ASC')
            ->findAll();

        $result = [];
        foreach ($rows as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $label = trim((string) ($row['nama_program_studi'] ?? ''));
            if ($label === '') {
                continue;
            }

            $jenjang = trim((string) ($row['jenjang'] ?? ''));
            if ($jenjang !== '') {
                $label .= ' (' . $jenjang . ')';
            }

            $result[$userId] ??= [];
            $result[$userId][] = $label;
        }

        return $result;
    }

    public function countDosenWithAssignments(): int
    {
        return $this->db->table($this->table)
            ->select('user_program_studi_assignments.user_id')
            ->join('user_roles', 'user_roles.user_id = user_program_studi_assignments.user_id', 'inner')
            ->join('roles', 'roles.id = user_roles.role_id', 'inner')
            ->where('roles.slug_role', 'dosen')
            ->groupBy('user_program_studi_assignments.user_id')
            ->countAllResults();
    }
}