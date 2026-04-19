<?php

use App\Models\UserProgramStudiAssignmentModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class UserProgramStudiAssignmentModelTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect();
        $table = $this->db->getPrefix() . 'user_program_studi_assignments';
        $this->db->query('DROP TABLE IF EXISTS ' . $table);
        $this->db->query('CREATE TABLE ' . $table . ' (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, program_studi_id INTEGER NOT NULL, created_at TEXT NULL, updated_at TEXT NULL)');
    }

    public function testSyncAssignmentsReplacesExistingAssignments(): void
    {
        $model = new UserProgramStudiAssignmentModel();

        $model->syncAssignments(77, [3, 4, 4, 0, 5]);
        $this->assertSame([3, 4, 5], $model->getProgramStudiIdsByUserId(77));

        $model->syncAssignments(77, [4, 6]);
        $this->assertSame([4, 6], $model->getProgramStudiIdsByUserId(77));
    }
}