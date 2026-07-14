<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToQueue extends Migration
{
    public function up()
    {
        // Add individual indexes for commonly filtered columns
        $this->db->disableForeignKeyChecks();
        
        // Index for tanggal (frequently used in WHERE clauses)
        $this->db->query('CREATE INDEX idx_queue_tanggal ON queue(tanggal)');
        
        // Index for status (frequently used in WHERE clauses)
        $this->db->query('CREATE INDEX idx_queue_status ON queue(status)');
        
        // Composite index for lantai + tanggal + status (most common query pattern)
        $this->db->query('CREATE INDEX idx_queue_lantai_tanggal_status ON queue(lantai, tanggal, status)');
        
        // Composite index for kode_antrian + tanggal (used in callNext and related queries)
        $this->db->query('CREATE INDEX idx_queue_kode_antrian_tanggal ON queue(kode_antrian, tanggal)');
        
        // Index for petugas_id (used in where clauses for staff queries)
        $this->db->query('CREATE INDEX idx_queue_petugas_id ON queue(petugas_id)');
        
        // Composite index for nomor_antrian + kode_antrian (for ordering)
        $this->db->query('CREATE INDEX idx_queue_nomor_antrian ON queue(nomor_antrian)');
        
        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        
        $this->db->query('DROP INDEX idx_queue_tanggal ON queue');
        $this->db->query('DROP INDEX idx_queue_status ON queue');
        $this->db->query('DROP INDEX idx_queue_lantai_tanggal_status ON queue');
        $this->db->query('DROP INDEX idx_queue_kode_antrian_tanggal ON queue');
        $this->db->query('DROP INDEX idx_queue_petugas_id ON queue');
        $this->db->query('DROP INDEX idx_queue_nomor_antrian ON queue');
        
        $this->db->enableForeignKeyChecks();
    }
}