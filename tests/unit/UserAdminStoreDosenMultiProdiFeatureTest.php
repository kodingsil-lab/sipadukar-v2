<?php

use App\Models\UserModel;
use App\Models\UserProgramStudiAssignmentModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use Config\Filters as FiltersConfig;

/**
 * @internal
 */
final class UserAdminStoreDosenMultiProdiFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect();
        $prefix = $this->db->getPrefix();

        foreach (['user_program_studi_assignments', 'user_roles', 'roles', 'users', 'program_studi', 'upps'] as $table) {
            $this->db->query('DROP TABLE IF EXISTS ' . $prefix . $table);
        }

        $this->db->query('CREATE TABLE ' . $prefix . 'upps (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_upps TEXT NOT NULL, nama_singkatan TEXT NULL)');
        $this->db->query('CREATE TABLE ' . $prefix . 'program_studi (id INTEGER PRIMARY KEY AUTOINCREMENT, upps_id INTEGER NULL, nama_program_studi TEXT NOT NULL, jenjang TEXT NULL, is_aktif_akreditasi INTEGER DEFAULT 1)');
        $this->db->query('CREATE TABLE ' . $prefix . 'users (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_lengkap TEXT NOT NULL, username TEXT NOT NULL, email TEXT NOT NULL, password_hash TEXT NOT NULL, nip TEXT NULL, unit_kerja TEXT NULL, program_studi_id INTEGER NULL, upps_id INTEGER NULL, jabatan TEXT NULL, foto TEXT NULL, is_aktif INTEGER DEFAULT 1, terakhir_login TEXT NULL, created_at TEXT NULL, updated_at TEXT NULL, deleted_at TEXT NULL)');
        $this->db->query('CREATE TABLE ' . $prefix . 'roles (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_role TEXT NOT NULL, slug_role TEXT NOT NULL, deskripsi TEXT NULL, is_aktif INTEGER DEFAULT 1, created_at TEXT NULL, updated_at TEXT NULL)');
        $this->db->query('CREATE TABLE ' . $prefix . 'user_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, role_id INTEGER NOT NULL, created_at TEXT NULL, updated_at TEXT NULL)');
        $this->db->query('CREATE TABLE ' . $prefix . 'user_program_studi_assignments (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, program_studi_id INTEGER NOT NULL, created_at TEXT NULL, updated_at TEXT NULL)');

        $now = date('Y-m-d H:i:s');

        $this->db->table('upps')->insert([
            'id' => 1,
            'nama_upps' => 'FKIP',
            'nama_singkatan' => 'FKIP',
        ]);

        $this->db->table('program_studi')->insertBatch([
            ['id' => 10, 'upps_id' => 1, 'nama_program_studi' => 'Pendidikan Bahasa Inggris', 'jenjang' => 'S1', 'is_aktif_akreditasi' => 1],
            ['id' => 11, 'upps_id' => 1, 'nama_program_studi' => 'Pendidikan Matematika', 'jenjang' => 'S1', 'is_aktif_akreditasi' => 1],
            ['id' => 12, 'upps_id' => 1, 'nama_program_studi' => 'PGSD', 'jenjang' => 'S1', 'is_aktif_akreditasi' => 1],
        ]);

        $this->db->table('roles')->insertBatch([
            ['id' => 1, 'nama_role' => 'Admin', 'slug_role' => 'admin', 'deskripsi' => '', 'is_aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nama_role' => 'Dosen', 'slug_role' => 'dosen', 'deskripsi' => '', 'is_aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nama_role' => 'Kaprodi', 'slug_role' => 'kaprodi', 'deskripsi' => '', 'is_aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'nama_role' => 'Dekan', 'slug_role' => 'dekan', 'deskripsi' => '', 'is_aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->db->table('users')->insert([
            'id' => 1,
            'nama_lengkap' => 'Admin Sistem',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'unit_kerja' => 'LPM',
            'is_aktif' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->db->table('user_roles')->insert([
            'user_id' => 1,
            'role_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $filtersConfig = config(FiltersConfig::class);
        $filtersConfig->globals['before'] = array_values(array_filter(
            $filtersConfig->globals['before'],
            static fn ($filter) => $filter !== 'csrf'
        ));
    }

    public function testAdminCanStoreDosenWithAdditionalAssignments(): void
    {
        $response = $this->withSession([
            'isLoggedIn' => true,
            'user_id' => 1,
            'roles' => ['admin'],
            'role_names' => ['Admin'],
        ])->post('/users/store', [
            'nama_lengkap' => 'Dosen Multi Prodi Baru',
            'username' => 'dosen-baru',
            'email' => 'dosen-baru@example.com',
            'password' => 'rahasia123',
            'nip' => '1987001',
            'program_studi_id' => 10,
            'assigned_program_studi_ids' => [11, 12],
            'jabatan' => 'Dosen',
            'is_aktif' => '1',
            'role_id' => 2,
        ]);

        $response->assertRedirectTo('/users');

        $user = (new UserModel())->where('username', 'dosen-baru')->first();

        $this->assertNotNull($user);
        $this->assertSame(10, (int) ($user['program_studi_id'] ?? 0));
        $this->assertSame(1, (int) ($user['upps_id'] ?? 0));

        $assignmentModel = new UserProgramStudiAssignmentModel();
        $this->assertSame([11, 12], $assignmentModel->getProgramStudiIdsByUserId((int) ($user['id'] ?? 0)));
    }

    public function testAdminCanUpdateDosenAndReplaceAdditionalAssignments(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('users')->insert([
            'id' => 2,
            'nama_lengkap' => 'Dosen Existing',
            'username' => 'dosen-existing',
            'email' => 'dosen-existing@example.com',
            'password_hash' => password_hash('rahasia123', PASSWORD_DEFAULT),
            'nip' => '1988002',
            'unit_kerja' => 'Pendidikan Bahasa Inggris',
            'program_studi_id' => 10,
            'upps_id' => 1,
            'jabatan' => 'Dosen',
            'is_aktif' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->db->table('user_roles')->insert([
            'user_id' => 2,
            'role_id' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $assignmentModel = new UserProgramStudiAssignmentModel();
        $assignmentModel->syncAssignments(2, [11]);

        $response = $this->withSession([
            'isLoggedIn' => true,
            'user_id' => 1,
            'roles' => ['admin'],
            'role_names' => ['Admin'],
        ])->post('/users/2/update', [
            'nama_lengkap' => 'Dosen Existing Update',
            'username' => 'dosen-existing',
            'email' => 'dosen-existing@example.com',
            'password' => '',
            'nip' => '1988002',
            'program_studi_id' => 10,
            'assigned_program_studi_ids' => [12],
            'jabatan' => 'Dosen Senior',
            'is_aktif' => '1',
            'role_id' => 2,
        ]);

        $response->assertRedirectTo('/users');

        $updatedUser = (new UserModel())->find(2);
        $this->assertSame('Dosen Existing Update', (string) ($updatedUser['nama_lengkap'] ?? ''));
        $this->assertSame('Dosen Senior', (string) ($updatedUser['jabatan'] ?? ''));
        $this->assertSame(10, (int) ($updatedUser['program_studi_id'] ?? 0));

        $this->assertSame([12], $assignmentModel->getProgramStudiIdsByUserId(2));
    }

    public function testAdminCanUpdateDosenWithEmptyAdditionalAssignments(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('users')->insert([
            'id' => 3,
            'nama_lengkap' => 'Dosen Tanpa Assignment',
            'username' => 'dosen-kosong',
            'email' => 'dosen-kosong@example.com',
            'password_hash' => password_hash('rahasia123', PASSWORD_DEFAULT),
            'nip' => '1989003',
            'unit_kerja' => 'Pendidikan Bahasa Inggris',
            'program_studi_id' => 10,
            'upps_id' => 1,
            'jabatan' => 'Dosen',
            'is_aktif' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->db->table('user_roles')->insert([
            'user_id' => 3,
            'role_id' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $assignmentModel = new UserProgramStudiAssignmentModel();
        $assignmentModel->syncAssignments(3, [11, 12]);

        $response = $this->withSession([
            'isLoggedIn' => true,
            'user_id' => 1,
            'roles' => ['admin'],
            'role_names' => ['Admin'],
        ])->post('/users/3/update', [
            'nama_lengkap' => 'Dosen Tanpa Assignment Update',
            'username' => 'dosen-kosong',
            'email' => 'dosen-kosong@example.com',
            'password' => '',
            'nip' => '1989003',
            'program_studi_id' => 10,
            'jabatan' => 'Dosen',
            'is_aktif' => '1',
            'role_id' => 2,
        ]);

        $response->assertRedirectTo('/users');

        $this->assertSame([], $assignmentModel->getProgramStudiIdsByUserId(3));

        $editResponse = $this->withSession([
            'isLoggedIn' => true,
            'user_id' => 1,
            'roles' => ['admin'],
            'role_names' => ['Admin'],
        ])->get('/users/3/edit');

        $editResponse->assertOK();
        $html = (string) $editResponse->getBody();
        $this->assertStringContainsString('name="assigned_program_studi_ids[]"', $html);
        $this->assertStringContainsString('Khusus untuk role Dosen, Kaprodi, dan Dekan', $html);
    }

    public function testAdminCanStoreKaprodiWithAdditionalAssignments(): void
    {
        $response = $this->withSession([
            'isLoggedIn' => true,
            'user_id' => 1,
            'roles' => ['admin'],
            'role_names' => ['Admin'],
        ])->post('/users/store', [
            'nama_lengkap' => 'Kaprodi Multi Prodi Baru',
            'username' => 'kaprodi-baru',
            'email' => 'kaprodi-baru@example.com',
            'password' => 'rahasia123',
            'nip' => '1987002',
            'program_studi_id' => 10,
            'assigned_program_studi_ids' => [11, 12],
            'jabatan' => 'Kaprodi',
            'is_aktif' => '1',
            'role_id' => 3,
        ]);

        $response->assertRedirectTo('/users');

        $user = (new UserModel())->where('username', 'kaprodi-baru')->first();
        $this->assertNotNull($user);

        $assignmentModel = new UserProgramStudiAssignmentModel();
        $this->assertSame([11, 12], $assignmentModel->getProgramStudiIdsByUserId((int) ($user['id'] ?? 0)));
    }

    public function testAdminCanStoreDekanWithAdditionalAssignments(): void
    {
        $response = $this->withSession([
            'isLoggedIn' => true,
            'user_id' => 1,
            'roles' => ['admin'],
            'role_names' => ['Admin'],
        ])->post('/users/store', [
            'nama_lengkap' => 'Dekan Multi Prodi Baru',
            'username' => 'dekan-baru',
            'email' => 'dekan-baru@example.com',
            'password' => 'rahasia123',
            'nip' => '1987003',
            'upps_id' => 1,
            'assigned_program_studi_ids' => [10, 12],
            'jabatan' => 'Dekan',
            'is_aktif' => '1',
            'role_id' => 4,
        ]);

        $response->assertRedirectTo('/users');

        $user = (new UserModel())->where('username', 'dekan-baru')->first();
        $this->assertNotNull($user);
        $this->assertSame(1, (int) ($user['upps_id'] ?? 0));
        $this->assertSame(0, (int) ($user['program_studi_id'] ?? 0));

        $assignmentModel = new UserProgramStudiAssignmentModel();
        $this->assertSame([10, 12], $assignmentModel->getProgramStudiIdsByUserId((int) ($user['id'] ?? 0)));
    }

    public function testUserIndexSortingAndPaginationStableForMultiProdi(): void
    {
        $now = date('Y-m-d H:i:s');
        $assignmentModel = new UserProgramStudiAssignmentModel();

        for ($i = 1; $i <= 15; $i++) {
            $userId = 100 + $i;
            $name = sprintf('Dosen Sort %02d', $i);
            $username = sprintf('dosen-sort-%02d', $i);
            $email = sprintf('dosen-sort-%02d@example.com', $i);

            $this->db->table('users')->insert([
                'id' => $userId,
                'nama_lengkap' => $name,
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash('rahasia123', PASSWORD_DEFAULT),
                'nip' => (string) (2000000 + $i),
                'unit_kerja' => 'Pendidikan Bahasa Inggris',
                'program_studi_id' => 10,
                'upps_id' => 1,
                'jabatan' => 'Dosen',
                'is_aktif' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $assignmentIds = $i <= 12 ? [11, 12] : [11];
            $assignmentModel->syncAssignments($userId, $assignmentIds);
        }

        $response = $this->withSession([
            'isLoggedIn' => true,
            'user_id' => 1,
            'roles' => ['admin'],
            'role_names' => ['Admin'],
        ])->get('/users?assignment=dosen_multi_prodi&sort=assignment_desc&per_page=10&page=2');

        $response->assertOK();
        $html = (string) $response->getBody();

        $this->assertStringContainsString('Dosen Sort 11', $html);
        $this->assertStringContainsString('Dosen Sort 12', $html);
        $this->assertStringContainsString('Dosen Sort 15', $html);
        $this->assertStringNotContainsString('Dosen Sort 01', $html);
        $this->assertStringContainsString('name="page"', $html);
        $this->assertMatchesRegularExpression('/>\s*2\s*</', $html);
    }

    public function testUserIndexUsesPerPageFromSessionWhenQueryIsMissing(): void
    {
        $response = $this->withSession([
            'isLoggedIn' => true,
            'user_id' => 1,
            'roles' => ['admin'],
            'role_names' => ['Admin'],
            'users_index_per_page_admin_1' => 25,
        ])->get('/users?assignment=dosen_multi_prodi&sort=assignment_desc&page=1');

        $response->assertOK();
        $html = (string) $response->getBody();

        $this->assertStringContainsString('<option value="25" selected>', $html);
    }
}