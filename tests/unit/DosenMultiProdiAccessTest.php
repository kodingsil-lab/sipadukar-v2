<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DosenMultiProdiAccessTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $session = service('session');
        $session->remove([
            'isLoggedIn',
            'user_id',
            'roles',
            'program_studi_id',
            'assigned_program_studi_ids',
            'upps_id',
        ]);
    }

    public function testDosenCanAccessAssignedProgramStudi(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'user_id' => 12,
            'roles' => ['dosen'],
            'program_studi_id' => 3,
            'assigned_program_studi_ids' => [5, 7],
        ]);

        $this->assertTrue(can_access_program_studi(3));
        $this->assertTrue(can_access_program_studi(5));
        $this->assertTrue(can_access_program_studi(7));
        $this->assertFalse(can_access_program_studi(9));
    }

    public function testDosenCanAccessDocumentFromAssignedProgramStudi(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'user_id' => 12,
            'roles' => ['dosen'],
            'program_studi_id' => 3,
            'assigned_program_studi_ids' => [5],
        ]);

        $this->assertTrue(can_access_dokumen([
            'program_studi_id' => 5,
            'uploaded_by' => 99,
        ]));

        $this->assertFalse(can_access_dokumen([
            'program_studi_id' => 8,
            'uploaded_by' => 99,
        ]));
    }

    public function testDosenCanStillManageOwnUploadedDocumentOnly(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'user_id' => 12,
            'roles' => ['dosen'],
            'program_studi_id' => 3,
            'assigned_program_studi_ids' => [5],
        ]);

        $this->assertTrue(can_manage_dokumen([
            'program_studi_id' => 5,
            'uploaded_by' => 12,
        ]));

        $this->assertFalse(can_manage_dokumen([
            'program_studi_id' => 5,
            'uploaded_by' => 21,
        ]));
    }
}