<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueCounterModel extends Model
{
    protected $table            = 'queue_counter';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'kode_antrian',
        'tanggal',
        'last_number'
    ];

    public function updateCounter($kodeAntrian, $tanggal, $nomorAntrian)
    {
        $existing = $this->where([
            'kode_antrian' => $kodeAntrian,
            'tanggal'      => $tanggal
        ])->first();

        if ($existing) {
            // Update if new number is greater
            if ($nomorAntrian > $existing['last_number']) {
                $this->update($existing['id'], [
                    'last_number' => $nomorAntrian
                ]);
            }
        } else {
            // Insert new counter
            $this->insert([
                'kode_antrian' => $kodeAntrian,
                'tanggal'      => $tanggal,
                'last_number'  => $nomorAntrian
            ]);
        }
    }

    public function getLastNumber($kodeAntrian, $tanggal)
    {
        $counter = $this->where([
            'kode_antrian' => $kodeAntrian,
            'tanggal'      => $tanggal
        ])->first();

        return $counter ? $counter['last_number'] : 0;
    }
}