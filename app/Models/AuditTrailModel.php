<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;

class AuditTrailModel extends Model
{
    protected $table            = 'audit_trails';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'nama_user',
        'username',
        'role_user',
        'unit_kerja',
        'aktivitas',
        'modul',
        'target_id',
        'deskripsi',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = false;

    protected array $casts = [
        'id'        => 'integer',
        'user_id'   => '?integer',
        'target_id' => '?integer',
    ];

    public function getLatest(int $limit = 50): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    public function getFiltered(array $filter = [], int $limit = 25, int $offset = 0): array
    {
        $builder = $this->db->table($this->table)->select('*');
        $this->applyFilterToBuilder($builder, $filter);
        $this->applySortToBuilder($builder, $filter);

        if ($limit > 0) {
            $builder->limit($limit, max(0, $offset));
        }

        return $builder->get()->getResultArray();
    }

    public function countFiltered(array $filter = []): int
    {
        $builder = $this->db->table($this->table)->select('COUNT(*) as total');
        $this->applyFilterToBuilder($builder, $filter);
        $row = $builder->get()->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    public function getDistinctAktivitas(): array
    {
        $rows = $this->db->table($this->table)
            ->select('aktivitas')
            ->where('aktivitas IS NOT NULL', null, false)
            ->where("TRIM(COALESCE(aktivitas, '')) !=", '')
            ->groupBy('aktivitas')
            ->orderBy('aktivitas', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(static fn ($row) => trim((string) ($row['aktivitas'] ?? '')), $rows)));
    }

    public function getDistinctModul(): array
    {
        $rows = $this->db->table($this->table)
            ->select('modul')
            ->where('modul IS NOT NULL', null, false)
            ->where("TRIM(COALESCE(modul, '')) !=", '')
            ->groupBy('modul')
            ->orderBy('modul', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(static fn ($row) => trim((string) ($row['modul'] ?? '')), $rows)));
    }

    public function deleteByIds(array $ids): int
    {
        $ids = array_values(array_unique(array_map(static fn ($id) => (int) $id, $ids)));
        $ids = array_values(array_filter($ids, static fn ($id) => $id > 0));
        if (empty($ids)) {
            return 0;
        }

        $builder = $this->db->table($this->table)->whereIn('id', $ids);
        $builder->delete();
        return $this->db->affectedRows();
    }

    private function applyFilterToBuilder(BaseBuilder $builder, array $filter): void
    {
        if (! empty($filter['aktivitas'])) {
            $builder->where('aktivitas', trim((string) $filter['aktivitas']));
        }

        if (! empty($filter['modul'])) {
            $builder->where('modul', trim((string) $filter['modul']));
        }

        if (! empty($filter['keyword'])) {
            $keyword = trim((string) $filter['keyword']);
            $builder->groupStart()
                ->like('nama_user', $keyword)
                ->orLike('username', $keyword)
                ->orLike('role_user', $keyword)
                ->orLike('unit_kerja', $keyword)
                ->orLike('aktivitas', $keyword)
                ->orLike('modul', $keyword)
                ->orLike('deskripsi', $keyword)
                ->orLike('ip_address', $keyword)
                ->groupEnd();
        }
    }

    private function applySortToBuilder(BaseBuilder $builder, array $filter): void
    {
        $allowedSortMap = [
            'created_at' => 'created_at',
            'nama_user'  => 'nama_user',
            'aktivitas'  => 'aktivitas',
            'modul'      => 'modul',
            'ip_address' => 'ip_address',
        ];

        $sortBy = strtolower(trim((string) ($filter['sort_by'] ?? '')));
        $sortDir = strtoupper(trim((string) ($filter['sort_dir'] ?? '')));
        if (! in_array($sortDir, ['ASC', 'DESC'], true)) {
            $sortDir = 'DESC';
        }

        if (! array_key_exists($sortBy, $allowedSortMap)) {
            $sortBy = 'created_at';
            $sortDir = 'DESC';
        }

        $builder->orderBy($allowedSortMap[$sortBy], $sortDir);
        if ($sortBy !== 'created_at') {
            $builder->orderBy('created_at', 'DESC');
        }
    }
}
