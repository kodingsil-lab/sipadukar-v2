<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = $mode === 'edit';
$action = $isEdit
    ? base_url('/users/' . $data['id'] . '/update')
    : base_url('/users/store');

$selectedRoleId = (int) old('role_id', $data['role_ids'][0] ?? 0);
$selectedAssignedProgramStudiIds = old('assigned_program_studi_ids');
if (! is_array($selectedAssignedProgramStudiIds)) {
    $selectedAssignedProgramStudiIds = $assignedProgramStudiIds ?? [];
}

$selectedAssignedProgramStudiIds = array_values(array_unique(array_map(
    static fn ($programStudiId) => (int) $programStudiId,
    array_filter($selectedAssignedProgramStudiIds, static fn ($programStudiId) => (int) $programStudiId > 0)
)));

$selectedPrimaryProgramStudiId = (int) old('program_studi_id', $data['program_studi_id'] ?? 0);
$selectedRoleSlug = '';
foreach (($roles ?? []) as $role) {
    if ((int) ($role['id'] ?? 0) === $selectedRoleId) {
        $selectedRoleSlug = (string) ($role['slug_role'] ?? '');
        break;
    }
}

$rolesWithAdditionalAssignments = ['dosen', 'kaprodi', 'dekan'];

?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="mb-1"><?= esc($title); ?></h3>
                    <p class="text-muted mb-0">Isi data akun pengguna dan tentukan role aksesnya.</p>
                </div>

                <form action="<?= $action; ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= esc(old('nama_lengkap', $data['nama_lengkap'] ?? '', false)); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">NUPTK/NIDN</label>
                            <input type="text" name="nip" class="form-control" value="<?= esc(old('nip', $data['nip'] ?? '', false)); ?>" placeholder="Isi NUPTK atau NIDN (opsional)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= esc(old('username', $data['username'] ?? '', false)); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= esc(old('email', $data['email'] ?? '', false)); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <?= $isEdit ? 'Password Baru (opsional)' : 'Password'; ?>
                            </label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                <?= $isEdit ? '' : 'required'; ?>
                            >
                            <?php if ($isEdit): ?>
                                <div class="form-text">Kosongkan jika password tidak ingin diubah.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Akun</label>
                            <select name="is_aktif" class="form-select" required>
                                <option value="1" <?= old('is_aktif', $data['is_aktif'] ?? 1) == 1 ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?= old('is_aktif', $data['is_aktif'] ?? 1) == 0 ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role User</label>
                            <select name="role_id" class="form-select js-role-select" required>
                                <option value="">Pilih Role User</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= esc($role['id']); ?>" data-role-slug="<?= esc((string) ($role['slug_role'] ?? '')); ?>" <?= $selectedRoleId === (int) $role['id'] ? 'selected' : ''; ?>>
                                        <?= esc($role['nama_role']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Setiap user memakai 1 role utama. Dekan/Kaprodi dapat mengerjakan dokumen sesuai scope-nya.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Program Studi</label>
                            <select name="program_studi_id" class="form-select js-primary-prodi-select">
                                <option value="">Pilih Program Studi (opsional)</option>
                                <?php
                                $selectedProdi = old('program_studi_id', $data['program_studi_id'] ?? '');
                                foreach (($programStudiList ?? []) as $prodi):
                                ?>
                                    <option value="<?= esc($prodi['id']); ?>" <?= (string) $selectedProdi === (string) $prodi['id'] ? 'selected' : ''; ?>>
                                        <?= esc($prodi['nama_program_studi']); ?><?= ! empty($prodi['jenjang']) ? ' (' . esc($prodi['jenjang']) . ')' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Wajib untuk role Kaprodi dan Dosen.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">UPPS</label>
                            <select name="upps_id" class="form-select">
                                <option value="">Pilih UPPS (opsional)</option>
                                <?php
                                $selectedUpps = old('upps_id', $data['upps_id'] ?? '');
                                foreach (($uppsList ?? []) as $upps):
                                ?>
                                    <option value="<?= esc($upps['id']); ?>" <?= (string) $selectedUpps === (string) $upps['id'] ? 'selected' : ''; ?>>
                                        <?= esc($upps['nama_upps']); ?><?= ! empty($upps['nama_singkatan']) ? ' (' . esc($upps['nama_singkatan']) . ')' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Wajib untuk role Dekan/Pimpinan UPPS.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="<?= esc(old('jabatan', $data['jabatan'] ?? '', false)); ?>">
                        </div>
                    </div>

                    <div class="mb-4 js-assignment-section <?= in_array($selectedRoleSlug, $rolesWithAdditionalAssignments, true) ? '' : 'd-none'; ?>">
                        <label class="form-label">Penugasan Prodi Tambahan</label>
                        <div class="form-text mb-2">Khusus untuk role Dosen, Kaprodi, dan Dekan. Prodi utama tidak ditampilkan pada daftar di bawah.</div>
                        <select name="assigned_program_studi_ids[]" class="form-select js-assignment-multi-select" multiple size="5">
                            <?php foreach (($programStudiList ?? []) as $prodi): ?>
                                <?php
                                $programStudiId = (int) ($prodi['id'] ?? 0);
                                $isChecked = in_array($programStudiId, $selectedAssignedProgramStudiIds, true);
                                $isPrimaryProgramStudi = $selectedPrimaryProgramStudiId > 0 && $selectedPrimaryProgramStudiId === $programStudiId;
                                ?>
                                <option
                                    value="<?= esc((string) $programStudiId); ?>"
                                    class="js-assignment-option"
                                    data-prodi-id="<?= esc((string) $programStudiId); ?>"
                                    <?= $isChecked ? 'selected' : ''; ?>
                                    <?= $isPrimaryProgramStudi ? 'hidden disabled' : ''; ?>
                                >
                                    <?= esc($prodi['nama_program_studi']); ?><?= ! empty($prodi['jenjang']) ? ' (' . esc($prodi['jenjang']) . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Tekan Ctrl (Windows) untuk memilih lebih dari satu Prodi.</div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-3">
                        <a href="<?= base_url('/users'); ?>" class="btn btn-light border">Kembali</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Update User' : 'Simpan User'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var roleSelect = document.querySelector('.js-role-select');
        var primaryProdiSelect = document.querySelector('.js-primary-prodi-select');
        var assignmentSection = document.querySelector('.js-assignment-section');
        var assignmentMultiSelect = document.querySelector('.js-assignment-multi-select');
        var assignmentOptions = assignmentMultiSelect
            ? Array.prototype.slice.call(assignmentMultiSelect.querySelectorAll('.js-assignment-option'))
            : [];

        if (!roleSelect || !primaryProdiSelect || !assignmentSection || !assignmentMultiSelect) {
            return;
        }

        function selectedRoleSlug() {
            var selectedOption = roleSelect.options[roleSelect.selectedIndex];
            return selectedOption ? String(selectedOption.getAttribute('data-role-slug') || '') : '';
        }

        function syncAssignmentVisibility() {
            var roleSlug = selectedRoleSlug();
            var canUseAssignments = ['dosen', 'kaprodi', 'dekan'].indexOf(roleSlug) !== -1;
            assignmentSection.classList.toggle('d-none', !canUseAssignments);
        }

        function syncPrimaryProgramStudiList() {
            var primaryProdiId = String(primaryProdiSelect.value || '');
            assignmentOptions.forEach(function (option) {
                var isPrimary = primaryProdiId !== '' && String(option.value) === primaryProdiId;
                option.hidden = isPrimary;
                option.disabled = isPrimary;
                if (isPrimary && option.selected) {
                    option.selected = false;
                }
            });
        }

        roleSelect.addEventListener('change', syncAssignmentVisibility);
        primaryProdiSelect.addEventListener('change', syncPrimaryProgramStudiList);

        syncAssignmentVisibility();
        syncPrimaryProgramStudiList();
    });
</script>

<?= $this->endSection(); ?>
