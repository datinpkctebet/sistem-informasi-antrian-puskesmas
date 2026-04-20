<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table            = 'queue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode_antrian',
        'nomor_antrian',
        'full_number',
        'nama_pasien',
        'lantai',
        'status',
        'loket',
        'petugas_id',
        'tanggal',
        'waktu_masuk',
        'waktu_panggil',
        'waktu_selesai',
        'is_warning'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Disable validation for batch insert
    protected $skipValidation = false;

    protected $validationRules = [
        'kode_antrian'  => 'required',
        'nomor_antrian' => 'required|numeric',
        'lantai'        => 'required',
        'tanggal'       => 'required|valid_date',
    ];

    public function addQueue($kodeAntrian, $nomorAntrian, $lantai, $namaPasien = null)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $tanggal = date('Y-m-d');
            $fullNumber = $kodeAntrian . str_pad($nomorAntrian, 4, '0', STR_PAD_LEFT);

            // Check if already exists
            $exists = $this->where([
                'kode_antrian'  => $kodeAntrian,
                'nomor_antrian' => $nomorAntrian,
                'tanggal'       => $tanggal
            ])->first();

            if ($exists) {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Nomor antrian sudah ada'
                ];
            }

            // Insert queue
            $this->insert([
                'kode_antrian'  => $kodeAntrian,
                'nomor_antrian' => $nomorAntrian,
                'full_number'   => $fullNumber,
                'nama_pasien'   => $namaPasien,
                'lantai'        => $lantai,
                'tanggal'       => $tanggal,
                'waktu_masuk'   => date('Y-m-d H:i:s'),
                'status'        => 'waiting'
            ]);

            // Update counter
            $counterModel = new \App\Models\QueueCounterModel();
            $counterModel->updateCounter($kodeAntrian, $tanggal, $nomorAntrian);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Gagal menambahkan antrian'
                ];
            }

            return [
                'success'     => true,
                'message'     => 'Antrian berhasil ditambahkan',
                'full_number' => $fullNumber
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getQueueList($lantai, $tanggal = null, $pelayanan = null, $status = null)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        $builder = $this->select('queue.*, services.nama_pelayanan, users.nama as petugas_nama')
                        ->join('services', 'services.kode_antrian = queue.kode_antrian')
                        ->join('users', 'users.id = queue.petugas_id', 'left')
                        ->where('queue.lantai', $lantai)
                        ->where('queue.tanggal', $tanggal);

        if ($pelayanan) {
            $builder->where('queue.kode_antrian', $pelayanan);
        }

        if ($status) {
            $builder->where('queue.status', $status);
        }

        $builder->orderBy('queue.nomor_antrian', 'ASC');

        return $builder->findAll();
    }

    public function getWaitingByLantai($lantai, $tanggal = null)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        return $this->select('queue.*, services.nama_pelayanan')
                    ->join('services', 'services.kode_antrian = queue.kode_antrian')
                    ->where([
                        'queue.lantai'  => $lantai,
                        'queue.tanggal' => $tanggal,
                        'queue.status'  => 'waiting'
                    ])
                    ->orderBy('queue.kode_antrian', 'ASC')
                    ->orderBy('queue.nomor_antrian', 'ASC')
                    ->findAll();
    }

    public function getCurrentCalling($lantai, $tanggal = null)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        $result = $this->select('queue.*, services.nama_pelayanan, users.nama as petugas_nama')
                    ->join('services', 'services.kode_antrian = queue.kode_antrian')
                    ->join('users', 'users.id = queue.petugas_id', 'left')
                    ->where([
                        'queue.lantai'  => $lantai,
                        'queue.tanggal' => $tanggal,
                        'queue.status'  => 'calling'
                    ])
                    ->orderBy('queue.waktu_panggil', 'DESC')
                    ->first();

        // Ensure nama_pasien exists even if null
        if ($result && !isset($result['nama_pasien'])) {
            $result['nama_pasien'] = '';
        }

        return $result;
    }

    public function callNext($kodeAntrian, $petugasId, $loket)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $tanggal = date('Y-m-d');

            // Get next waiting queue
            $nextQueue = $this->where([
                'kode_antrian' => $kodeAntrian,
                'tanggal'      => $tanggal,
                'status'       => 'waiting'
            ])
            ->orderBy('nomor_antrian', 'ASC')
            ->first();

            if (!$nextQueue) {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Tidak ada antrian'
                ];
            }

            // Update previous calling to done
            $this->where([
                'kode_antrian' => $kodeAntrian,
                'tanggal'      => $tanggal,
                'status'       => 'calling'
            ])->set([
                'status'        => 'done',
                'waktu_selesai' => date('Y-m-d H:i:s')
            ])->update();

            // Update next queue to calling
            $this->update($nextQueue['id'], [
                'status'        => 'calling',
                'petugas_id'    => $petugasId,
                'loket'         => $loket,
                'waktu_panggil' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            return [
                'success'     => true,
                'full_number' => $nextQueue['full_number'],
                'nomor_antrian' => str_pad($nextQueue['nomor_antrian'], 4, '0', STR_PAD_LEFT),
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function callSpecificQueue($queueId, $petugasId)
    {
        $queue = $this->find($queueId);

        if (!$queue || $queue['status'] !== 'waiting') {
            return [
                'success' => false,
                'message' => 'Antrian tidak valid'
            ];
        }

        $loket = 'Loket ' . $petugasId; // Default loket

        // Reset status queue lain di service yang sama menjadi 'called'
        $this->where([
            'kode_antrian'  => $queue['kode_antrian'],
            'lantai'        => $queue['lantai'],
            'tanggal'       => $queue['tanggal'],
            'status'        => 'calling'
        ])->set([
            'status' => 'called'
        ])->update();

        if ($this->update($queueId, [
            'status'        => 'calling',
            'petugas_id'    => $petugasId,
            'loket'         => $loket,
            'waktu_panggil' => date('Y-m-d H:i:s')
        ])) {
            return [
                'success'     => true,
                'full_number' => $queue['full_number'],
                'nomor_antrian' => str_pad($queue['nomor_antrian'], 4, '0', STR_PAD_LEFT),
            ];
        }

        return [
            'success' => false,
            'message' => 'Gagal memanggil antrian'
        ];
    }

    public function finishQueue($queueId)
    {
        return $this->update($queueId, [
            'status'        => 'done',
            'waktu_selesai' => date('Y-m-d H:i:s')
        ]);
    }

    public function skipQueue($queueId)
    {
        return $this->update($queueId, [
            'status' => 'skip'
        ]);
    }

    public function updateNamaPasien($queueId, $namaPasien)
    {
        return $this->update($queueId, [
            'nama_pasien' => $namaPasien
        ]);
    }

    public function triggerWarning($queueId)
    {
        $queue = $this->find($queueId);

        if (!$queue) {
            return [
                'success' => false,
                'message' => 'Antrian tidak ditemukan'
            ];
        }

        // Set warning flag with timestamp
        $this->update($queueId, [
            'is_warning' => time()
        ]);

        return [
            'success' => true,
            'full_number' => $queue['full_number'],
            'nomor_antrian' => str_pad($queue['nomor_antrian'], 4, '0', STR_PAD_LEFT),
            'message' => 'Peringatan terkirim'
        ];
    }

    public function getCurrentCallingByService($kodeAntrian, $lantai, $tanggal = null)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        $result = $this->select('queue.*')
                    ->where([
                        'queue.kode_antrian' => $kodeAntrian,
                        'queue.lantai'  => $lantai,
                        'queue.tanggal' => $tanggal,
                        'queue.status'  => 'calling'
                    ])
                    ->orderBy('queue.last_called_at', 'DESC')
                    ->first();

        // Ensure nama_pasien exists
        if ($result && !isset($result['nama_pasien'])) {
            $result['nama_pasien'] = '';
        }

        return $result;
    }

    public function getWaitingCountByServiceCode($kodeAntrian, $lantai, $tanggal = null)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        return $this->where([
                        'kode_antrian' => $kodeAntrian,
                        'lantai'  => $lantai,
                        'tanggal' => $tanggal,
                        'status'  => 'waiting'
                    ])
                    ->countAllResults();
    }

    public function getStatistics($tanggal = null)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        $result = $this->select('
            COUNT(*) as total,
            SUM(CASE WHEN status = "waiting" THEN 1 ELSE 0 END) as waiting,
            SUM(CASE WHEN status = "calling" THEN 1 ELSE 0 END) as calling,
            SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as done,
            SUM(CASE WHEN status = "skip" THEN 1 ELSE 0 END) as skip
        ')
        ->where('tanggal', $tanggal)
        ->first();

        return $result;
    }

    public function getQueueHistory($lantai, $tanggal = null, $limit = 50)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        return $this->select('queue.*, services.nama_pelayanan, users.nama as petugas_nama')
                    ->join('services', 'services.kode_antrian = queue.kode_antrian')
                    ->join('users', 'users.id = queue.petugas_id', 'left')
                    ->where([
                        'queue.lantai'  => $lantai,
                        'queue.tanggal' => $tanggal
                    ])
                    ->orderBy('queue.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getWaitingCountByService($lantai, $tanggal = null)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        $results = $this->select('kode_antrian, COUNT(*) as count')
                        ->where([
                            'lantai'  => $lantai,
                            'tanggal' => $tanggal,
                            'status'  => 'waiting'
                        ])
                        ->groupBy('kode_antrian')
                        ->findAll();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['kode_antrian']] = $result['count'];
        }

        return $counts;
    }

    // Fungsi untuk get history queue (sudah dipanggil sebelumnya)
    public function getCalledHistoryByService($kodeAntrian, $lantai, $tanggal = null, $limit = 10)
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        $results = $this->select('queue.*')
                    ->where([
                        'queue.kode_antrian' => $kodeAntrian,
                        'queue.lantai'  => $lantai,
                        'queue.tanggal' => $tanggal
                    ])
                    ->whereIn('queue.status', ['calling', 'called', 'completed'])
                    ->orderBy('queue.last_called_at', 'DESC')
                    ->limit($limit)
                    ->findAll();

        return $results;
    }

    // Fungsi untuk recall queue sebelumnya
    public function recallQueue($id)
    {
        $queue = $this->find($id);
        
        if (!$queue) {
            return false;
        }

        // Reset status queue lain di service yang sama menjadi 'called'
        $this->where([
            'kode_antrian' => $queue['kode_antrian'],
            'lantai' => $queue['lantai'],
            'tanggal' => $queue['tanggal'],
            'status' => 'calling'
        ])->set([
            'status' => 'called'
        ])->update();

        // Update queue menjadi 'calling' lagi
        $result = $this->update($id, [
            'status' => 'calling',
            'last_called_at' => date('Y-m-d H:i:s'),
            'call_count' => ($queue['call_count'] ?? 0) + 1
        ]);

        if ($result) {
            return [
                'success'     => true,
                'full_number' => $queue['full_number'],
                'nomor_antrian' => str_pad($queue['nomor_antrian'], 4, '0', STR_PAD_LEFT),
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal memanggil ulang antrian'
            ];
        }
    }
}