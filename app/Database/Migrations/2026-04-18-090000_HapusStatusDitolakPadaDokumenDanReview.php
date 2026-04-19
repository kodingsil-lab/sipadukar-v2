<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HapusStatusDitolakPadaDokumenDanReview extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('dokumen')) {
            if ($this->db->fieldExists('status_dokumen', 'dokumen')) {
                $this->db->query("UPDATE dokumen SET status_dokumen = 'perlu_revisi' WHERE status_dokumen = 'ditolak'");
                $this->db->query("
                    ALTER TABLE dokumen
                    MODIFY status_dokumen ENUM('draft','diajukan','ditinjau','perlu_revisi','disubmit_ulang','tervalidasi')
                    NOT NULL DEFAULT 'draft'
                ");
            }
        }

        if ($this->db->tableExists('review_dokumen')) {
            if ($this->db->fieldExists('status_review', 'review_dokumen')) {
                $this->db->query("UPDATE review_dokumen SET status_review = 'perlu_revisi' WHERE status_review = 'ditolak'");
                $this->db->query("
                    ALTER TABLE review_dokumen
                    MODIFY status_review ENUM('diajukan','ditinjau','perlu_revisi','tervalidasi')
                    NOT NULL DEFAULT 'ditinjau'
                ");
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('dokumen') && $this->db->fieldExists('status_dokumen', 'dokumen')) {
            $this->db->query("
                ALTER TABLE dokumen
                MODIFY status_dokumen ENUM('draft','diajukan','ditinjau','perlu_revisi','disubmit_ulang','tervalidasi','ditolak')
                NOT NULL DEFAULT 'draft'
            ");
        }

        if ($this->db->tableExists('review_dokumen') && $this->db->fieldExists('status_review', 'review_dokumen')) {
            $this->db->query("
                ALTER TABLE review_dokumen
                MODIFY status_review ENUM('diajukan','ditinjau','perlu_revisi','tervalidasi','ditolak')
                NOT NULL DEFAULT 'ditinjau'
            ");
        }
    }
}
