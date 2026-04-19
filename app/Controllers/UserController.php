<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\ProgramStudiModel;
use App\Models\UppsModel;
use App\Models\UserProgramStudiAssignmentModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use CodeIgniter\HTTP\RedirectResponse;

class UserController extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected ProgramStudiModel $programStudiModel;
    protected UppsModel $uppsModel;
    protected UserProgramStudiAssignmentModel $userProgramStudiAssignmentModel;
    protected UserRoleModel $userRoleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->programStudiModel = new ProgramStudiModel();
        $this->uppsModel = new UppsModel();
        $this->userProgramStudiAssignmentModel = new UserProgramStudiAssignmentModel();
        $this->userRoleModel = new UserRoleModel();
    }

    private function ensureAdmin(): ?RedirectResponse
    {
        if (has_role('admin')) {
            return null;
        }

        return redirect()->to('/dashboard')->with('error', 'Hanya Admin yang boleh mengatur user dan role.');
    }

    private function buildSessionPayloadFromUser(array $user): array
    {
        $roles = $this->userModel->getRolesByUserId((int) ($user['id'] ?? 0));
        $assignedProgramStudiIds = $this->userProgramStudiAssignmentModel
            ->getProgramStudiIdsByUserId((int) ($user['id'] ?? 0));
        $roleSlugs = array_map(static fn ($role) => (string) ($role['slug_role'] ?? ''), $roles);
        $roleNames = array_map(static fn ($role) => (string) ($role['nama_role'] ?? ''), $roles);

        return [
            'isLoggedIn'   => true,
            'user_id'      => (int) ($user['id'] ?? 0),
            'nama_lengkap' => (string) ($user['nama_lengkap'] ?? ''),
            'username'     => (string) ($user['username'] ?? ''),
            'email'        => (string) ($user['email'] ?? ''),
            'unit_kerja'   => (string) ($user['unit_kerja'] ?? ''),
            'program_studi_id' => (int) ($user['program_studi_id'] ?? 0) ?: null,
            'assigned_program_studi_ids' => $assignedProgramStudiIds,
            'upps_id'      => (int) ($user['upps_id'] ?? 0) ?: null,
            'roles'        => $roleSlugs,
            'role_names'   => $roleNames,
        ];
    }

    private function normalizeProgramStudiIds(array $rawIds): array
    {
        $programStudiIds = [];
        foreach ($rawIds as $rawId) {
            $programStudiId = (int) $rawId;
            if ($programStudiId > 0) {
                $programStudiIds[$programStudiId] = $programStudiId;
            }
        }

        return array_values($programStudiIds);
    }

    private function filterValidProgramStudiIds(array $programStudiIds): array
    {
        if (empty($programStudiIds)) {
            return [];
        }

        $rows = $this->programStudiModel
            ->select('id')
            ->whereIn('id', $programStudiIds)
            ->findAll();

        $validIds = [];
        foreach ($rows as $row) {
            $programStudiId = (int) ($row['id'] ?? 0);
            if ($programStudiId > 0) {
                $validIds[$programStudiId] = $programStudiId;
            }
        }

        return array_values($validIds);
    }

    private function sanitizeAssignedProgramStudiIds(int $primaryProgramStudiId): array
    {
        $rawAssignedIds = $this->request->getPost('assigned_program_studi_ids');
        $normalizedIds = $this->normalizeProgramStudiIds(is_array($rawAssignedIds) ? $rawAssignedIds : []);

        if ($primaryProgramStudiId > 0) {
            $normalizedIds = array_values(array_filter(
                $normalizedIds,
                static fn ($programStudiId) => (int) $programStudiId !== $primaryProgramStudiId
            ));
        }

        return $this->filterValidProgramStudiIds($normalizedIds);
    }

    private function filterUsersForIndex(array $users, array $assignedProgramStudiMap, string $keyword, string $assignmentFilter): array
    {
        $filteredUsers = [];
        $keyword = mb_strtolower(trim($keyword));

        foreach ($users as $user) {
            $userId = (int) ($user['id'] ?? 0);
            $roleSlugs = array_map(
                static fn ($slug) => trim((string) $slug),
                $user['role_slugs'] ?? []
            );
            $assignedProgramStudiList = $assignedProgramStudiMap[$userId] ?? [];
            $isDosen = in_array('dosen', $roleSlugs, true);
            $hasMultiProdiAssignment = $isDosen && ! empty($assignedProgramStudiList);

            if ($assignmentFilter === 'dosen_multi_prodi' && ! $hasMultiProdiAssignment) {
                continue;
            }

            if ($assignmentFilter === 'dosen_only' && ! $isDosen) {
                continue;
            }

            if ($assignmentFilter === 'non_multi_prodi' && $hasMultiProdiAssignment) {
                continue;
            }

            if ($keyword !== '') {
                $haystacks = [
                    (string) ($user['nama_lengkap'] ?? ''),
                    (string) ($user['username'] ?? ''),
                    (string) ($user['email'] ?? ''),
                    (string) ($user['nip'] ?? ''),
                    (string) ($user['nama_program_studi_user'] ?? ''),
                    (string) ($user['nama_upps_user'] ?? ''),
                    implode(' ', $assignedProgramStudiList),
                    implode(' ', $roleSlugs),
                ];

                $matched = false;
                foreach ($haystacks as $haystack) {
                    if ($haystack !== '' && str_contains(mb_strtolower($haystack), $keyword)) {
                        $matched = true;
                        break;
                    }
                }

                if (! $matched) {
                    continue;
                }
            }

            $filteredUsers[] = $user;
        }

        return $filteredUsers;
    }

    private function sortUsersForIndex(array $users, array $assignedProgramStudiMap, string $sort): array
    {
        $sortedUsers = $users;

        usort($sortedUsers, static function (array $left, array $right) use ($assignedProgramStudiMap, $sort): int {
            $leftId = (int) ($left['id'] ?? 0);
            $rightId = (int) ($right['id'] ?? 0);
            $leftAssignmentCount = count($assignedProgramStudiMap[$leftId] ?? []);
            $rightAssignmentCount = count($assignedProgramStudiMap[$rightId] ?? []);

            $compareByNameThenId = static function () use ($left, $right, $leftId, $rightId): int {
                $byName = strcasecmp((string) ($left['nama_lengkap'] ?? ''), (string) ($right['nama_lengkap'] ?? ''));
                if ($byName !== 0) {
                    return $byName;
                }

                return $leftId <=> $rightId;
            };

            $compareByNameThenIdDesc = static function () use ($left, $right, $leftId, $rightId): int {
                $byName = strcasecmp((string) ($right['nama_lengkap'] ?? ''), (string) ($left['nama_lengkap'] ?? ''));
                if ($byName !== 0) {
                    return $byName;
                }

                return $rightId <=> $leftId;
            };

            if ($sort === 'nama_asc') {
                return $compareByNameThenId();
            }

            if ($sort === 'nama_desc') {
                return $compareByNameThenIdDesc();
            }

            if ($sort === 'assignment_asc' || $sort === 'assignment_desc') {
                $primary = $sort === 'assignment_asc'
                    ? ($leftAssignmentCount <=> $rightAssignmentCount)
                    : ($rightAssignmentCount <=> $leftAssignmentCount);

                if ($primary !== 0) {
                    return $primary;
                }

                return $compareByNameThenId();
            }

            if ($sort === 'login_asc' || $sort === 'login_desc') {
                $primary = $sort === 'login_asc'
                    ? strcmp((string) ($left['terakhir_login'] ?? ''), (string) ($right['terakhir_login'] ?? ''))
                    : strcmp((string) ($right['terakhir_login'] ?? ''), (string) ($left['terakhir_login'] ?? ''));

                if ($primary !== 0) {
                    return $primary;
                }

                return $compareByNameThenId();
            }

            return $rightId <=> $leftId;
        });

        return $sortedUsers;
    }

    private function paginateUsersForIndex(array $users, int $page, int $perPage): array
    {
        $totalItems = count($users);
        if ($perPage <= 0) {
            $perPage = 10;
        }

        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $safePage = max(1, min($page, $totalPages));
        $offset = ($safePage - 1) * $perPage;

        return [
            'items' => array_slice($users, $offset, $perPage),
            'page' => $safePage,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'offset' => $offset,
        ];
    }

    public function index()
    {
        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        $session = session();
        $adminId = (int) ($session->get('user_id') ?? 0);
        $perPageSessionKey = 'users_index_per_page_admin_' . $adminId;
        $allowedPerPageFilters = [10, 25, 50];

        $storedPerPage = (int) ($session->get($perPageSessionKey) ?? 10);
        if (! in_array($storedPerPage, $allowedPerPageFilters, true)) {
            $storedPerPage = 10;
        }

        $rawPerPage = $this->request->getGet('per_page');
        $resolvedPerPage = $storedPerPage;
        if ($rawPerPage !== null) {
            $requestedPerPage = (int) $rawPerPage;
            if (in_array($requestedPerPage, $allowedPerPageFilters, true)) {
                $resolvedPerPage = $requestedPerPage;
            }
        }

        $session->set($perPageSessionKey, $resolvedPerPage);

        $users = $this->userModel->getWithRoles();
        $assignedProgramStudiMap = $this->userProgramStudiAssignmentModel->getAssignmentNamesMapByUserIds(array_map(
            static fn ($user) => (int) ($user['id'] ?? 0),
            $users
        ));
        $filter = [
            'keyword' => trim((string) ($this->request->getGet('q') ?? '')),
            'assignment' => trim((string) ($this->request->getGet('assignment') ?? '')),
            'sort' => trim((string) ($this->request->getGet('sort') ?? 'assignment_desc')),
            'per_page' => $resolvedPerPage,
            'page' => (int) ($this->request->getGet('page') ?? 1),
        ];
        $allowedAssignmentFilters = ['', 'dosen_only', 'dosen_multi_prodi', 'non_multi_prodi'];
        $allowedSortFilters = ['default', 'nama_asc', 'nama_desc', 'assignment_asc', 'assignment_desc', 'login_asc', 'login_desc'];
        if (! in_array($filter['assignment'], $allowedAssignmentFilters, true)) {
            $filter['assignment'] = '';
        }
        if (! in_array($filter['sort'], $allowedSortFilters, true)) {
            $filter['sort'] = 'assignment_desc';
        }
        if (! in_array($filter['per_page'], $allowedPerPageFilters, true)) {
            $filter['per_page'] = 10;
        }
        if ($filter['page'] <= 0) {
            $filter['page'] = 1;
        }

        $users = $this->filterUsersForIndex($users, $assignedProgramStudiMap, $filter['keyword'], $filter['assignment']);
        $users = $this->sortUsersForIndex($users, $assignedProgramStudiMap, $filter['sort']);
        $paging = $this->paginateUsersForIndex($users, $filter['page'], $filter['per_page']);
        $multiProdiDosenCount = count(array_filter(
            $assignedProgramStudiMap,
            static fn ($assignmentNames) => ! empty($assignmentNames)
        ));

        return view('users/index', [
            'title' => 'Manajemen User',
            'users' => $paging['items'],
            'assignedProgramStudiMap' => $assignedProgramStudiMap,
            'filter' => $filter,
            'multiProdiDosenCount' => $multiProdiDosenCount,
            'pagination' => $paging,
        ]);
    }

    public function templateExcel()
    {
        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        $templatePath = FCPATH . 'uploads/template_import_user.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheetData = $spreadsheet->getActiveSheet();
        $sheetData->setTitle('Data Import');
        $sheetData->setCellValue('A1', 'Nama Lengkap');
        $sheetData->setCellValue('B1', 'NUPTK/NIDN');
        $sheetData->setCellValue('C1', 'Username');
        $sheetData->setCellValue('D1', 'Email (Opsional)');
        $sheetData->setCellValue('E1', 'Password (Opsional)');
        $sheetData->setCellValue('F1', 'Status Akun (Aktif/Nonaktif)');

        $sheetData->setCellValue('A2', 'Dosen Contoh');
        $sheetData->setCellValue('B2', '1987654321');
        $sheetData->setCellValue('C2', 'dosen.contoh');
        $sheetData->setCellValue('D2', 'dosen.contoh@kampus.ac.id');
        $sheetData->setCellValue('E2', 'rahasia123');
        $sheetData->setCellValue('F2', 'Aktif');

        $statusValidationFormula = '"Aktif,Nonaktif"';
        for ($row = 2; $row <= 500; $row++) {
            $validation = $sheetData->getCell('F' . $row)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Status tidak valid');
            $validation->setError('Isi kolom Status Akun hanya dengan Aktif atau Nonaktif.');
            $validation->setPromptTitle('Pilih Status Akun');
            $validation->setPrompt('Gunakan salah satu nilai: Aktif atau Nonaktif.');
            $validation->setFormula1($statusValidationFormula);
        }

        $sheetGuide = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Petunjuk');
        $spreadsheet->addSheet($sheetGuide);
        $sheetGuide->setCellValue('A1', 'Petunjuk Pengisian Template Import User');
        $sheetGuide->setCellValue('A3', '1. Isi data pada sheet "Data Import".');
        $sheetGuide->setCellValue('A4', '2. Kolom wajib: Nama Lengkap, NUPTK/NIDN, Username, Status Akun.');
        $sheetGuide->setCellValue('A5', '3. Email opsional. Jika kosong, sistem mengisi otomatis: username@sipadukar.local.');
        $sheetGuide->setCellValue('A6', '4. Password opsional. Jika kosong, sistem mengisi otomatis: sipadukar123.');
        $sheetGuide->setCellValue('A7', '5. Status Akun wajib: Aktif atau Nonaktif.');
        $sheetGuide->setCellValue('A8', '6. Username dan email harus unik (tidak boleh duplikat).');
        $sheetGuide->setCellValue('A9', '7. User hasil import otomatis memakai role Dosen.');

        if (! is_dir(FCPATH . 'uploads')) {
            mkdir(FCPATH . 'uploads', 0777, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($templatePath);

        return $this->response
            ->download($templatePath, null)
            ->setFileName('template_import_user.xlsx');
    }

    public function impor()
    {
        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau gagal diunggah.');
        }

        $extension = strtolower((string) ($file->getClientExtension() ?: $file->getExtension()));
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan file Excel (.xlsx/.xls).');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            log_message('error', 'Gagal membaca file import user: {message}', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'File Excel tidak dapat dibaca. Pastikan file tidak rusak dan formatnya benar.');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        $header = $rows[1] ?? [];

        $map = [
            'nama_lengkap' => null,
            'nip' => null,
            'username' => null,
            'email' => null,
            'password' => null,
            'status' => null,
        ];

        $headerAliases = [
            'nama_lengkap' => ['nama lengkap'],
            'nip' => ['nuptk/nidn', 'nuptk', 'nidn'],
            'username' => ['username'],
            'email' => ['email', 'email (opsional)'],
            'password' => ['password', 'password (opsional)'],
            'status' => ['status akun (aktif/nonaktif)', 'status akun', 'status'],
        ];

        foreach ($header as $col => $val) {
            $normalizedHeader = $this->normalizeImportHeader((string) $val);
            if ($normalizedHeader === '') {
                continue;
            }

            foreach ($headerAliases as $key => $aliases) {
                if ($map[$key] !== null) {
                    continue;
                }

                if (in_array($normalizedHeader, $aliases, true)) {
                    $map[$key] = $col;
                    break;
                }
            }
        }

        if (! $map['nama_lengkap'] || ! $map['nip'] || ! $map['username'] || ! $map['status']) {
            return redirect()->back()->with('error', 'Kolom wajib (Nama Lengkap, NUPTK/NIDN, Username, Status Akun) tidak ditemukan.');
        }

        $defaultRole = $this->roleModel->where('slug_role', 'dosen')->where('is_aktif', 1)->first();
        if (! $defaultRole) {
            return redirect()->back()->with('error', 'Role default dosen tidak ditemukan. Pastikan data role sudah tersedia.');
        }
        $defaultRoleId = (int) ($defaultRole['id'] ?? 0);

        $success = 0;
        $failed = 0;
        $failedRows = [];
        $seenUsername = [];
        $seenEmail = [];

        for ($i = 2; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? [];
            if ($this->isImportRowEmpty($row)) {
                continue;
            }

            $namaLengkap = trim((string) ($row[$map['nama_lengkap']] ?? ''));
            $nip = trim((string) ($row[$map['nip']] ?? ''));
            $username = trim((string) ($row[$map['username']] ?? ''));
            $emailRaw = trim((string) ($row[$map['email']] ?? ''));
            $passwordRaw = trim((string) ($row[$map['password']] ?? ''));
            $statusRaw = trim((string) ($row[$map['status']] ?? ''));

            if ($namaLengkap === '' || $nip === '' || $username === '' || $statusRaw === '') {
                $failed++;
                $failedRows[] = $i . ' (kolom wajib kosong)';
                continue;
            }

            $status = $this->normalizeImportUserStatus($statusRaw);
            if ($status === null) {
                $failed++;
                $failedRows[] = $i . ' (status akun harus Aktif/Nonaktif)';
                continue;
            }

            if (strlen($namaLengkap) < 3 || strlen($namaLengkap) > 150) {
                $failed++;
                $failedRows[] = $i . ' (nama lengkap harus 3-150 karakter)';
                continue;
            }

            if (strlen($nip) > 50) {
                $failed++;
                $failedRows[] = $i . ' (NUPTK/NIDN maksimal 50 karakter)';
                continue;
            }

            if (strlen($username) < 3 || strlen($username) > 100) {
                $failed++;
                $failedRows[] = $i . ' (username harus 3-100 karakter)';
                continue;
            }

            $usernameLower = strtolower($username);
            if (isset($seenUsername[$usernameLower])) {
                $failed++;
                $failedRows[] = $i . ' (username duplikat di file)';
                continue;
            }

            if ($this->userModel->where('username', $username)->first()) {
                $failed++;
                $failedRows[] = $i . ' (username sudah terdaftar)';
                continue;
            }

            $email = $emailRaw;
            if ($email === '') {
                $email = $this->buildUniqueGeneratedEmail($username, $seenEmail);
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $failed++;
                $failedRows[] = $i . ' (format email tidak valid)';
                continue;
            }

            $emailLower = strtolower($email);
            if (isset($seenEmail[$emailLower])) {
                $failed++;
                $failedRows[] = $i . ' (email duplikat di file)';
                continue;
            }

            if ($this->userModel->where('email', $email)->first()) {
                $failed++;
                $failedRows[] = $i . ' (email sudah terdaftar)';
                continue;
            }

            $password = $passwordRaw !== '' ? $passwordRaw : 'sipadukar123';
            if (strlen($password) < 6) {
                $failed++;
                $failedRows[] = $i . ' (password minimal 6 karakter)';
                continue;
            }

            $inserted = $this->userModel->insert([
                'nama_lengkap' => $namaLengkap,
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'nip' => $nip,
                'unit_kerja' => '',
                'program_studi_id' => null,
                'upps_id' => null,
                'jabatan' => '',
                'is_aktif' => $status,
            ], true);

            if ($inserted === false) {
                $failed++;
                $reason = implode('; ', array_values($this->userModel->errors()));
                if ($reason === '') {
                    $dbError = $this->userModel->db->error();
                    $reason = (string) ($dbError['message'] ?? 'gagal menyimpan data');
                }

                $failedRows[] = $i . ' (' . $reason . ')';
                continue;
            }

            $userId = (int) $this->userModel->getInsertID();
            $this->userRoleModel->setRoles($userId, [$defaultRoleId]);

            $seenUsername[$usernameLower] = true;
            $seenEmail[$emailLower] = true;
            $success++;
        }

        $msg = $success . ' baris berhasil diimpor. ' . $failed . ' baris gagal.';
        if ($failed > 0) {
            $msg .= ' Detail gagal: ' . implode(', ', $failedRows);
        }

        if ($success > 0) {
            return redirect()->to('/users')->with('success', $msg);
        }

        return redirect()->to('/users')->with('error', $msg);
    }

    public function create()
    {
        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        $roles = $this->roleModel->getAktif();

        return view('users/form', [
            'title' => 'Tambah User',
            'mode'  => 'create',
            'data'  => null,
            'assignedProgramStudiIds' => [],
            'roles' => $roles,
            'uppsList' => $this->uppsModel->orderBy('nama_upps', 'ASC')->findAll(),
            'programStudiList' => $this->programStudiModel->orderBy('nama_program_studi', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[150]',
            'username'     => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
            'email'        => 'required|valid_email|max_length[150]|is_unique[users.email]',
            'password'     => 'required|min_length[6]',
            'nip'          => 'permit_empty|max_length[50]',
            'upps_id'      => 'permit_empty|is_natural_no_zero',
            'program_studi_id' => 'permit_empty|is_natural_no_zero',
            'jabatan'      => 'permit_empty|max_length[100]',
            'is_aktif'     => 'required|in_list[0,1]',
            'role_id'      => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data user belum valid.');
        }

        $roleId = (int) ($this->request->getPost('role_id') ?? 0);
        $role = $this->roleModel->where('id', $roleId)->where('is_aktif', 1)->first();
        if (! $role) {
            return redirect()->back()->withInput()->with('error', 'Role user tidak valid.');
        }
        $roleSlug = (string) ($role['slug_role'] ?? '');

        $programStudiId = (int) ($this->request->getPost('program_studi_id') ?? 0);
        $uppsId = (int) ($this->request->getPost('upps_id') ?? 0);
        $unitKerja = '';

        if (in_array($roleSlug, ['admin', 'lpm'], true)) {
            $programStudiId = 0;
            $uppsId = 0;
        } elseif ($roleSlug === 'dekan') {
            $programStudiId = 0;
            if ($uppsId <= 0 || ! $this->uppsModel->find($uppsId)) {
                return redirect()->back()->withInput()->with('error', 'Role Dekan wajib memilih UPPS yang valid.');
            }

            $upps = $this->uppsModel->find($uppsId);
            $unitKerja = trim((string) ($upps['nama_upps'] ?? ''));
        } elseif (in_array($roleSlug, ['kaprodi', 'dosen'], true)) {
            if ($programStudiId <= 0) {
                return redirect()->back()->withInput()->with('error', 'Role Kaprodi/Dosen wajib memilih Program Studi.');
            }

            $prodi = $this->programStudiModel->find($programStudiId);
            if (! $prodi) {
                return redirect()->back()->withInput()->with('error', 'Program Studi yang dipilih tidak valid.');
            }

            $uppsId = (int) ($prodi['upps_id'] ?? 0);
            $unitKerja = trim((string) ($prodi['nama_program_studi'] ?? ''));
        } else {
            return redirect()->back()->withInput()->with('error', 'Role belum didukung sistem.');
        }

        $rolesWithAdditionalAssignments = ['dosen', 'kaprodi', 'dekan'];
        $assignedProgramStudiIds = in_array($roleSlug, $rolesWithAdditionalAssignments, true)
            ? $this->sanitizeAssignedProgramStudiIds($programStudiId)
            : [];

        $this->userModel->insert([
            'nama_lengkap'  => trim((string) $this->request->getPost('nama_lengkap')),
            'username'      => trim((string) $this->request->getPost('username')),
            'email'         => trim((string) $this->request->getPost('email')),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'nip'           => trim((string) $this->request->getPost('nip')),
            'upps_id'       => $uppsId > 0 ? $uppsId : null,
            'program_studi_id' => $programStudiId > 0 ? $programStudiId : null,
            'unit_kerja'    => $unitKerja,
            'jabatan'       => trim((string) $this->request->getPost('jabatan')),
            'is_aktif'      => (int) $this->request->getPost('is_aktif'),
        ]);

        $userId = $this->userModel->getInsertID();
        $this->userRoleModel->setRoles((int) $userId, [$roleId]);
        $this->userProgramStudiAssignmentModel->syncAssignments((int) $userId, $assignedProgramStudiIds);

        catat_audit(
            'tambah_user',
            'user',
            (int) $userId,
            'Menambahkan user baru: '
                . trim((string) $this->request->getPost('nama_lengkap'))
                . ' (role=' . ($roleSlug !== '' ? $roleSlug : '-') . ')'
                . (in_array($roleSlug, $rolesWithAdditionalAssignments, true) && ! empty($assignedProgramStudiIds)
                    ? ', assignment_prodi=' . implode(',', $assignedProgramStudiIds)
                    : '')
        );

        return redirect()->to('/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        $user = $this->userModel->getDetailWithRoles((int) $id);

        if (! $user) {
            return redirect()->to('/users')->with('error', 'Data user tidak ditemukan.');
        }

        $roles = $this->roleModel->getAktif();

        return view('users/form', [
            'title' => 'Edit User',
            'mode'  => 'edit',
            'data'  => $user,
            'assignedProgramStudiIds' => $this->userProgramStudiAssignmentModel->getProgramStudiIdsByUserId((int) $id),
            'roles' => $roles,
            'uppsList' => $this->uppsModel->orderBy('nama_upps', 'ASC')->findAll(),
            'programStudiList' => $this->programStudiModel->orderBy('nama_program_studi', 'ASC')->findAll(),
        ]);
    }

    public function update($id)
    {
        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        $user = $this->userModel->find((int) $id);

        if (! $user) {
            return redirect()->to('/users')->with('error', 'Data user tidak ditemukan.');
        }

        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[150]',
            'username'     => 'required|min_length[3]|max_length[100]',
            'email'        => 'required|valid_email|max_length[150]',
            'password'     => 'permit_empty|min_length[6]',
            'nip'          => 'permit_empty|max_length[50]',
            'upps_id'      => 'permit_empty|is_natural_no_zero',
            'program_studi_id' => 'permit_empty|is_natural_no_zero',
            'jabatan'      => 'permit_empty|max_length[100]',
            'is_aktif'     => 'required|in_list[0,1]',
            'role_id'      => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data user belum valid.');
        }

        $usernameBaru = trim((string) $this->request->getPost('username'));
        $emailBaru    = trim((string) $this->request->getPost('email'));

        $cekUsername = $this->userModel
            ->where('username', $usernameBaru)
            ->where('id !=', (int) $id)
            ->first();

        if ($cekUsername) {
            return redirect()->back()->withInput()->with('error', 'Username sudah digunakan.');
        }

        $cekEmail = $this->userModel
            ->where('email', $emailBaru)
            ->where('id !=', (int) $id)
            ->first();

        if ($cekEmail) {
            return redirect()->back()->withInput()->with('error', 'Email sudah digunakan.');
        }

        $roleId = (int) ($this->request->getPost('role_id') ?? 0);
        $role = $this->roleModel->where('id', $roleId)->where('is_aktif', 1)->first();
        if (! $role) {
            return redirect()->back()->withInput()->with('error', 'Role user tidak valid.');
        }
        $roleSlug = (string) ($role['slug_role'] ?? '');

        $programStudiId = (int) ($this->request->getPost('program_studi_id') ?? 0);
        $uppsId = (int) ($this->request->getPost('upps_id') ?? 0);
        $unitKerja = trim((string) ($user['unit_kerja'] ?? ''));

        if (in_array($roleSlug, ['admin', 'lpm'], true)) {
            $programStudiId = 0;
            $uppsId = 0;
            $unitKerja = '';
        } elseif ($roleSlug === 'dekan') {
            $programStudiId = 0;
            if ($uppsId <= 0 || ! $this->uppsModel->find($uppsId)) {
                return redirect()->back()->withInput()->with('error', 'Role Dekan wajib memilih UPPS yang valid.');
            }
            $upps = $this->uppsModel->find($uppsId);
            $unitKerja = trim((string) ($upps['nama_upps'] ?? ''));
        } elseif (in_array($roleSlug, ['kaprodi', 'dosen'], true)) {
            if ($programStudiId <= 0) {
                return redirect()->back()->withInput()->with('error', 'Role Kaprodi/Dosen wajib memilih Program Studi.');
            }

            $prodi = $this->programStudiModel->find($programStudiId);
            if (! $prodi) {
                return redirect()->back()->withInput()->with('error', 'Program Studi yang dipilih tidak valid.');
            }
            $uppsId = (int) ($prodi['upps_id'] ?? 0);
            $unitKerja = trim((string) ($prodi['nama_program_studi'] ?? ''));
        } else {
            return redirect()->back()->withInput()->with('error', 'Role belum didukung sistem.');
        }

        $rolesWithAdditionalAssignments = ['dosen', 'kaprodi', 'dekan'];
        $assignedProgramStudiIds = in_array($roleSlug, $rolesWithAdditionalAssignments, true)
            ? $this->sanitizeAssignedProgramStudiIds($programStudiId)
            : [];

        $dataUpdate = [
            'nama_lengkap' => trim((string) $this->request->getPost('nama_lengkap')),
            'username'     => $usernameBaru,
            'email'        => $emailBaru,
            'nip'          => trim((string) $this->request->getPost('nip')),
            'upps_id'      => $uppsId > 0 ? $uppsId : null,
            'program_studi_id' => $programStudiId > 0 ? $programStudiId : null,
            'unit_kerja'   => $unitKerja,
            'jabatan'      => trim((string) $this->request->getPost('jabatan')),
            'is_aktif'     => (int) $this->request->getPost('is_aktif'),
        ];

        $passwordBaru = (string) $this->request->getPost('password');
        if ($passwordBaru !== '') {
            $dataUpdate['password_hash'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }

        $existingRoles = $this->userModel->getRolesByUserId((int) $id);
        $oldRoleSlugs = array_values(array_unique(array_filter(array_map(
            static fn ($role) => trim((string) ($role['slug_role'] ?? '')),
            $existingRoles
        ))));
        sort($oldRoleSlugs);

        $this->userModel->update((int) $id, $dataUpdate);
        $this->userRoleModel->setRoles((int) $id, [$roleId]);
        $this->userProgramStudiAssignmentModel->syncAssignments((int) $id, $assignedProgramStudiIds);

        $newRoleSlugs = $oldRoleSlugs;
        $newRoleSlugs = [$roleSlug];
        $newRoleSlugs = array_values(array_unique(array_filter(array_map(
            static fn ($slug) => trim((string) $slug),
            $newRoleSlugs
        ))));
        sort($newRoleSlugs);

        catat_audit(
            'edit_user',
            'user',
            (int) $id,
            'Memperbarui user: '
                . $dataUpdate['nama_lengkap']
                . ' (role=' . ($roleSlug !== '' ? $roleSlug : '-') . ')'
                . (in_array($roleSlug, $rolesWithAdditionalAssignments, true) && ! empty($assignedProgramStudiIds)
                    ? ', assignment_prodi=' . implode(',', $assignedProgramStudiIds)
                    : '')
        );

        if ($oldRoleSlugs !== $newRoleSlugs) {
            catat_audit(
                'ubah_role_user',
                'user',
                (int) $id,
                'Perubahan role user dari ['
                    . (empty($oldRoleSlugs) ? '-' : implode(',', $oldRoleSlugs))
                    . '] menjadi ['
                    . (empty($newRoleSlugs) ? '-' : implode(',', $newRoleSlugs))
                    . ']'
            );
        }

        return redirect()->to('/users')->with('success', 'User berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        $user = $this->userModel->find((int) $id);

        if (! $user) {
            return redirect()->to('/users')->with('error', 'Data user tidak ditemukan.');
        }

        if ((int) $user['id'] === (int) session()->get('user_id')) {
            return redirect()->to('/users')->with('error', 'Anda tidak bisa menghapus akun yang sedang dipakai login.');
        }

        $nama = $user['nama_lengkap'] ?? ('ID ' . $id);

        $this->userModel->delete((int) $id);

        catat_audit(
            'hapus_user',
            'user',
            (int) $id,
            'Menghapus user: ' . $nama
        );

        return redirect()->to('/users')->with('success', 'User berhasil dihapus.');
    }

    public function impersonate($id)
    {
        $session = session();

        if ($guard = $this->ensureAdmin()) {
            return $guard;
        }

        if ((bool) $session->get('is_impersonating')) {
            return redirect()->to('/users')->with('error', 'Anda sedang dalam mode Masuk Sebagai. Kembali ke Admin terlebih dahulu.');
        }

        $targetId = (int) $id;
        $adminId = (int) ($session->get('user_id') ?? 0);
        if ($targetId <= 0 || $targetId === $adminId) {
            return redirect()->to('/users')->with('error', 'Target user tidak valid untuk Masuk Sebagai.');
        }

        $targetUser = $this->userModel
            ->where('id', $targetId)
            ->where('is_aktif', 1)
            ->first();

        if (! $targetUser) {
            return redirect()->to('/users')->with('error', 'User target tidak ditemukan atau tidak aktif.');
        }

        $adminSessionBackup = [
            'user_id' => (int) ($session->get('user_id') ?? 0),
            'nama_lengkap' => (string) ($session->get('nama_lengkap') ?? ''),
            'username' => (string) ($session->get('username') ?? ''),
            'email' => (string) ($session->get('email') ?? ''),
            'unit_kerja' => (string) ($session->get('unit_kerja') ?? ''),
            'program_studi_id' => $session->get('program_studi_id'),
            'assigned_program_studi_ids' => is_array($session->get('assigned_program_studi_ids')) ? $session->get('assigned_program_studi_ids') : [],
            'upps_id' => $session->get('upps_id'),
            'roles' => $session->get('roles') ?? [],
            'role_names' => $session->get('role_names') ?? [],
        ];

        catat_audit(
            'masuk_sebagai',
            'user',
            (int) $targetUser['id'],
            'Admin melakukan impersonasi ke user: ' . (string) ($targetUser['nama_lengkap'] ?? ('ID ' . $targetId))
        );

        // Rotate session ID when privilege context changes to reduce fixation risk.
        $session->regenerate(true);

        $targetSession = $this->buildSessionPayloadFromUser($targetUser);
        $session->set(array_merge($targetSession, [
            'is_impersonating' => true,
            'impersonator' => $adminSessionBackup,
        ]));

        return redirect()->to('/dashboard')->with('success', 'Mode Masuk Sebagai aktif. Anda sekarang login sebagai ' . (string) ($targetUser['nama_lengkap'] ?? 'user target') . '.');
    }

    public function stopImpersonation()
    {
        $session = session();
        $isImpersonating = (bool) $session->get('is_impersonating');
        $impersonator = $session->get('impersonator');

        if (! $isImpersonating || ! is_array($impersonator) || empty($impersonator['user_id'])) {
            return redirect()->to('/dashboard')->with('error', 'Mode Masuk Sebagai tidak aktif.');
        }

        $impersonatedName = (string) ($session->get('nama_lengkap') ?? '');
        $adminName = (string) ($impersonator['nama_lengkap'] ?? 'Admin');

        // Rotate session ID when leaving impersonation to avoid session fixation.
        $session->regenerate(true);

        $session->set([
            'isLoggedIn'   => true,
            'user_id'      => (int) ($impersonator['user_id'] ?? 0),
            'nama_lengkap' => (string) ($impersonator['nama_lengkap'] ?? ''),
            'username'     => (string) ($impersonator['username'] ?? ''),
            'email'        => (string) ($impersonator['email'] ?? ''),
            'unit_kerja'   => (string) ($impersonator['unit_kerja'] ?? ''),
            'program_studi_id' => $impersonator['program_studi_id'] ?? null,
            'assigned_program_studi_ids' => is_array($impersonator['assigned_program_studi_ids'] ?? null) ? $impersonator['assigned_program_studi_ids'] : [],
            'upps_id'      => $impersonator['upps_id'] ?? null,
            'roles'        => is_array($impersonator['roles'] ?? null) ? $impersonator['roles'] : [],
            'role_names'   => is_array($impersonator['role_names'] ?? null) ? $impersonator['role_names'] : [],
        ]);
        $session->remove(['is_impersonating', 'impersonator']);

        catat_audit(
            'kembali_admin',
            'user',
            (int) ($session->get('user_id') ?? 0),
            'Admin kembali dari impersonasi user: ' . ($impersonatedName !== '' ? $impersonatedName : '-')
        );

        return redirect()->to('/users')->with('success', 'Berhasil kembali ke akun admin: ' . $adminName . '.');
    }

    private function normalizeImportHeader(string $header): string
    {
        $header = trim(str_replace("\xC2\xA0", ' ', $header));
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;
        return strtolower($header);
    }

    private function isImportRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeImportUserStatus(string $value): ?int
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/\s+/', '', $value) ?? $value;

        if (in_array($value, ['1', 'aktif', 'active', 'yes', 'ya'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'nonaktif', 'non-aktif', 'inactive', 'no', 'tidak'], true)) {
            return 0;
        }

        return null;
    }

    private function buildUniqueGeneratedEmail(string $username, array $seenEmail): string
    {
        $base = strtolower(trim($username));
        $base = preg_replace('/[^a-z0-9._-]/', '', $base) ?? $base;
        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base . '@sipadukar.local';
        $suffix = 1;

        while (isset($seenEmail[strtolower($candidate)]) || $this->userModel->where('email', $candidate)->first()) {
            $candidate = $base . $suffix . '@sipadukar.local';
            $suffix++;
        }

        return $candidate;
    }
}
