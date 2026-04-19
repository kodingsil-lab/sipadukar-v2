<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TambahDiajukanPadaStatusReviewDokumen extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('review_dokumen')) {
            return;
        }

        $this->db->query("
            ALTER TABLE review_dokumen
            MODIFY status_review ENUM('diajukan','ditinjau','perlu_revisi','tervalidasi','ditolak')
            NOT NULL DEFAULT 'ditinjau'
        ");
    }

    public function down()
    {
        if (! $this->db->tableExists('review_dokumen')) {
            return;
        }

        // pastikan data tetap valid saat enum diajukan dihapus
        $this->db->query("
            UPDATE review_dokumen
            SET status_review = 'ditinjau'
            WHERE status_review = 'diajukan'
        ");

        $this->db->query("
            ALTER TABLE review_dokumen
            MODIFY status_review ENUM('ditinjau','perlu_revisi','tervalidasi','ditolak')
            NOT NULL DEFAULT 'ditinjau'
        ");
    }
}

