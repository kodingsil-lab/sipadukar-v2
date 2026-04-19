<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<style>
    .users-filter-form {
        display: grid;
        grid-template-columns: 2.2fr 1.3fr 0.95fr 0.85fr auto;
        gap: 12px;
        align-items: end;
    }

    .users-filter-item {
        min-width: 0;
    }

    .user-filter-actions {
        justify-content: flex-end;
        align-items: center;
        min-width: 0;
    }

    .user-filter-actions .user-filter-btn {
        min-height: 42px;
        min-width: 126px;
        padding: 0 14px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        white-space: nowrap;
    }

    .user-header-actions {
        gap: .45rem;
    }

    .user-header-actions .btn {
        min-height: 34px;
        padding: 5px 11px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    @media (max-width: 1199.98px) {
        .users-filter-form {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .user-filter-actions {
            justify-content: stretch;
        }

        .user-filter-actions .user-filter-btn {
            flex: 1 1 auto;
        }
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Manajemen User</h3>
        <p class="page-subtitle">Kelola akun pengguna dan pembagian role pada sistem.</p>
    </div>

    <div class="d-flex flex-wrap align-items-center user-header-actions">
        <a href="<?= base_url('/users/template-excel'); ?>" class="btn btn-outline-secondary" download="template_import_user.xlsx">
            <i class="bi bi-download me-1"></i>Template Excel
        </a>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#imporUserModal">
            <i class="bi bi-upload me-1"></i>Impor Excel
        </button>
        <a href="<?= base_url('/users/create'); ?>" class="btn btn-primary">
            Tambah User
        </a>
    </div>
</div>

<?php
$filter = $filter ?? ['keyword' => '', 'assignment' => ''];
$selectedAssignmentFilter = (string) ($filter['assignment'] ?? '');
$keyword = (string) ($filter['keyword'] ?? '');
$selectedSort = (string) ($filter['sort'] ?? 'assignment_desc');
$selectedPerPage = (int) ($filter['per_page'] ?? 10);
$pagination = $pagination ?? ['page' => 1, 'per_page' => 10, 'total_items' => 0, 'total_pages' => 1, 'offset' => 0];
$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$offset = (int) ($pagination['offset'] ?? 0);

$buildQueryUrl = static function (array $override) use ($filter): string {
    $query = [
        'q' => (string) ($filter['keyword'] ?? ''),
        'assignment' => (string) ($filter['assignment'] ?? ''),
        'sort' => (string) ($filter['sort'] ?? 'assignment_desc'),
        'per_page' => (int) ($filter['per_page'] ?? 10),
        'page' => (int) ($filter['page'] ?? 1),
    ];

    foreach ($override as $key => $value) {
        $query[$key] = $value;
    }

    $query = array_filter($query, static fn ($value) => ! (($value === '') || ($value === null)));
    $queryString = http_build_query($query);

    return current_url() . ($queryString !== '' ? ('?' . $queryString) : '');
};
?>

<div class="card card-clean mb-3">
    <div class="card-body">
        <form method="get" action="<?= current_url(); ?>" class="users-filter-form">
            <div class="users-filter-item">
                <label class="form-label">Cari User</label>
                <input type="text" name="q" class="form-control" value="<?= esc($keyword); ?>" placeholder="Nama, username, email, NIDN, prodi, role">
            </div>
            <div class="users-filter-item">
                <label class="form-label">Filter Assignment</label>
                <select name="assignment" class="form-select">
                    <option value="" <?= $selectedAssignmentFilter === '' ? 'selected' : ''; ?>>Semua User</option>
                    <option value="dosen_only" <?= $selectedAssignmentFilter === 'dosen_only' ? 'selected' : ''; ?>>Dosen Saja</option>
                    <option value="dosen_multi_prodi" <?= $selectedAssignmentFilter === 'dosen_multi_prodi' ? 'selected' : ''; ?>>Dosen Multi-Prodi</option>
                    <option value="non_multi_prodi" <?= $selectedAssignmentFilter === 'non_multi_prodi' ? 'selected' : ''; ?>>Selain Dosen Multi-Prodi</option>
                </select>
            </div>
            <div class="users-filter-item">
                <label class="form-label">Sort</label>
                <select name="sort" class="form-select">
                    <option value="assignment_desc" <?= $selectedSort === 'assignment_desc' ? 'selected' : ''; ?>>Assignment terbanyak</option>
                    <option value="assignment_asc" <?= $selectedSort === 'assignment_asc' ? 'selected' : ''; ?>>Assignment tersedikit</option>
                    <option value="nama_asc" <?= $selectedSort === 'nama_asc' ? 'selected' : ''; ?>>Nama A-Z</option>
                    <option value="nama_desc" <?= $selectedSort === 'nama_desc' ? 'selected' : ''; ?>>Nama Z-A</option>
                    <option value="login_desc" <?= $selectedSort === 'login_desc' ? 'selected' : ''; ?>>Login terbaru</option>
                    <option value="login_asc" <?= $selectedSort === 'login_asc' ? 'selected' : ''; ?>>Login terlama</option>
                </select>
            </div>
            <div class="users-filter-item">
                <label class="form-label">Per Halaman</label>
                <select name="per_page" class="form-select">
                    <option value="10" <?= $selectedPerPage === 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?= $selectedPerPage === 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?= $selectedPerPage === 50 ? 'selected' : ''; ?>>50</option>
                </select>
            </div>
            <div class="users-filter-item">
                <div class="d-flex gap-2 user-filter-actions">
                    <button type="submit" class="btn btn-primary user-filter-btn">Terapkan</button>
                    <a href="<?= current_url(); ?>" class="btn btn-light border user-filter-btn">Reset</a>
                </div>
            </div>
        </form>

        <div class="form-text mt-2">Total dosen dengan assignment lintas prodi saat ini: <strong><?= esc((string) ($multiProdiDosenCount ?? 0)); ?></strong></div>
    </div>
</div>

<!-- Modal Impor User -->
<div class="modal fade" id="imporUserModal" tabindex="-1" aria-labelledby="imporUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('/users/impor'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="imporUserModalLabel">Impor User (Excel)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file_excel_user" class="form-label">Pilih File Excel (.xlsx/.xls)</label>
                        <input type="file" class="form-control" id="file_excel_user" name="file_excel" accept=".xlsx,.xls" required>
                        <div class="form-text">Wajib kolom: <b>Nama Lengkap, NUPTK/NIDN, Username, Status Akun</b>.</div>
                        <div class="form-text">Email/password boleh kosong (otomatis diisi sistem).</div>
                        <div class="form-text">Role user hasil import otomatis: <b>Dosen</b>.</div>
                        <div class="form-text">Unduh template: <a href="<?= base_url('/users/template-excel'); ?>" download="template_import_user.xlsx">Template Import User</a></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Impor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card card-clean">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle">
                <thead>
                    <tr>
                        <th width="60" class="cell-center">No</th>
                        <th>Nama</th>
                        <th>Username / Email</th>
                        <th>UPPS / Program Studi</th>
                        <th>Assignment Tambahan</th>
                        <th>Jabatan</th>
                        <th>Role</th>
                        <th class="cell-center">Status</th>
                        <th>Login Terakhir</th>
                        <th width="220" class="cell-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($users)): ?>
                        <?php foreach ($users as $i => $row): ?>
                            <?php $assignedProgramStudiList = $assignedProgramStudiMap[(int) ($row['id'] ?? 0)] ?? []; ?>
                            <tr>
                                <td class="cell-center"><?= $offset + $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['nama_lengkap']); ?></div>
                                    <div class="table-subtext">NUPTK/NIDN: <?= esc($row['nip'] ?: '-'); ?></div>
                                </td>
                                <td>
                                    <div><?= esc($row['username']); ?></div>
                                    <div class="table-subtext"><?= esc($row['email']); ?></div>
                                </td>
                                <td>
                                    <div><?= esc($row['nama_upps_user'] ?: '-'); ?></div>
                                    <div class="table-subtext"><?= esc($row['nama_program_studi_user'] ?: '-'); ?></div>
                                </td>
                                <td>
                                    <?php if (! empty($assignedProgramStudiList)): ?>
                                        <div class="action-group">
                                            <?php foreach ($assignedProgramStudiList as $assignedProgramStudi): ?>
                                                <span class="badge badge-soft-info text-dark"><?= esc($assignedProgramStudi); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($row['jabatan'] ?: '-'); ?></td>
                                <td>
                                    <?php if (! empty($row['roles'])): ?>
                                        <div class="action-group">
                                            <?php foreach ($row['roles'] as $role): ?>
                                                <span class="badge badge-soft-primary"><?= esc($role['nama_role']); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-center">
                                    <?php if ((int) $row['is_aktif'] === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($row['terakhir_login'] ?: '-'); ?></td>
                                <td class="cell-center">
                                    <div class="action-group">
                                        <?php if ((int) ($row['id'] ?? 0) !== (int) (session()->get('user_id') ?? 0)): ?>
                                            <form action="<?= base_url('/users/' . $row['id'] . '/impersonate'); ?>" method="post" class="js-confirm-submit" data-confirm="Masuk sebagai user ini?">
                                                <?= csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-info text-white icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Masuk Sebagai" aria-label="Masuk Sebagai">
                                                    <i class="bi bi-box-arrow-in-right"></i>
                                                    <span class="visually-hidden">Masuk Sebagai</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <a href="<?= base_url('/users/' . $row['id'] . '/edit'); ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit User" aria-label="Edit User">
                                            <i class="bi bi-pencil-fill"></i>
                                            <span class="visually-hidden">Edit</span>
                                        </a>

                                        <form action="<?= base_url('/users/' . $row['id'] . '/delete'); ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus user ini?">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus User" aria-label="Hapus User">
                                                <i class="bi bi-trash-fill"></i>
                                                <span class="visually-hidden">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">Belum ada data user.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="small text-muted">
                    Menampilkan <?= esc((string) count($users)); ?> data di halaman <?= esc((string) $currentPage); ?> dari <?= esc((string) $totalPages); ?>.
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <a href="<?= esc($buildQueryUrl(['page' => max(1, $currentPage - 1)])); ?>" class="btn btn-sm btn-light border <?= $currentPage <= 1 ? 'disabled' : ''; ?>">Sebelumnya</a>
                    <div class="btn-group">
                        <?php if ($startPage > 1): ?>
                            <a href="<?= esc($buildQueryUrl(['page' => 1])); ?>" class="btn btn-sm btn-light border">1</a>
                            <?php if ($startPage > 2): ?>
                                <button type="button" class="btn btn-sm btn-light border" disabled>...</button>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                            <a href="<?= esc($buildQueryUrl(['page' => $page])); ?>" class="btn btn-sm <?= $page === $currentPage ? 'btn-primary' : 'btn-light border'; ?>">
                                <?= esc((string) $page); ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <button type="button" class="btn btn-sm btn-light border" disabled>...</button>
                            <?php endif; ?>
                            <a href="<?= esc($buildQueryUrl(['page' => $totalPages])); ?>" class="btn btn-sm btn-light border"><?= esc((string) $totalPages); ?></a>
                        <?php endif; ?>
                    </div>
                    <a href="<?= esc($buildQueryUrl(['page' => min($totalPages, $currentPage + 1)])); ?>" class="btn btn-sm btn-light border <?= $currentPage >= $totalPages ? 'disabled' : ''; ?>">Berikutnya</a>

                    <form method="get" action="<?= current_url(); ?>" class="d-flex align-items-center gap-2 ms-2">
                        <input type="hidden" name="q" value="<?= esc($keyword); ?>">
                        <input type="hidden" name="assignment" value="<?= esc($selectedAssignmentFilter); ?>">
                        <input type="hidden" name="sort" value="<?= esc($selectedSort); ?>">
                        <input type="hidden" name="per_page" value="<?= esc((string) $selectedPerPage); ?>">
                        <label for="jumpToPage" class="small text-muted mb-0">Ke halaman</label>
                        <input
                            id="jumpToPage"
                            type="number"
                            name="page"
                            min="1"
                            max="<?= esc((string) $totalPages); ?>"
                            value="<?= esc((string) $currentPage); ?>"
                            class="form-control form-control-sm"
                            style="width: 88px;"
                        >
                        <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection(); ?>
