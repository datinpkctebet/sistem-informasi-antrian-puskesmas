<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueGeneratorModel extends Model
{
    protected $queueModel;
    protected $serviceModel;
    protected $counterModel;

    public function __construct()
    {
        parent::__construct();
        $this->queueModel = new QueueModel();
        $this->serviceModel = new ServiceModel();
        $this->counterModel = new QueueCounterModel();
    }

    /**
     * Generate queue numbers for all services for a specific date
     * 
     * @param string $tanggal Date in Y-m-d format
     * @param int|null $lantai Floor number (optional, if null will generate for all)
     * @return array Result with success status and message
     */
    public function generateDailyQueues($tanggal, $lantai = null)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get active services
            $builder = $this->serviceModel->where('is_active', 1);
            
            if ($lantai !== null) {
                $builder->where('lantai', $lantai);
            }
            
            $services = $builder->findAll();

            if (empty($services)) {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Tidak ada pelayanan aktif' . ($lantai ? " untuk lantai {$lantai}" : '')
                ];
            }

            $totalGenerated = 0;
            $details = [];
            $skipped = [];

            foreach ($services as $service) {
                $kodeAntrian = $service['kode_antrian'];
                $serviceLantai = $service['lantai'];
                
                // Check if already exists
                $exists = $this->queueModel
                    ->where('kode_antrian', $kodeAntrian)
                    ->where('tanggal', $tanggal)
                    ->countAllResults();

                if ($exists > 0) {
                    $skipped[] = [
                        'kode' => $kodeAntrian,
                        'nama' => $service['nama_pelayanan'],
                        'existing' => $exists
                    ];
                    continue;
                }
                
                // Determine number of queues
                $jumlahNomor = $this->getQueueLimit($kodeAntrian);
                
                // Generate queue numbers
                for ($i = 1; $i <= $jumlahNomor; $i++) {
                    $fullNumber = $kodeAntrian . str_pad($i, 4, '0', STR_PAD_LEFT);
                    
                    $this->queueModel->insert([
                        'kode_antrian'  => $kodeAntrian,
                        'nomor_antrian' => $i,
                        'full_number'   => $fullNumber,
                        'nama_pasien'   => null,
                        'lantai'        => $serviceLantai,
                        'loket'         => 'Loket Admin',
                        'petugas_id'    => 1, // System
                        'tanggal'       => $tanggal,
                        'waktu_masuk'   => date('Y-m-d H:i:s'),
                        'status'        => 'waiting'
                    ]);
                }

                // Update counter
                $this->counterModel->updateCounter($kodeAntrian, $tanggal, $jumlahNomor);

                $totalGenerated += $jumlahNomor;
                $details[] = [
                    'kode' => $kodeAntrian,
                    'nama' => $service['nama_pelayanan'],
                    'jumlah' => $jumlahNomor
                ];
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Gagal generate antrian'
                ];
            }

            $message = 'Antrian berhasil di-generate';
            if (!empty($skipped)) {
                $message .= ' (beberapa sudah ada)';
            }

            return [
                'success' => true,
                'message' => $message,
                'total' => $totalGenerated,
                'details' => $details,
                'skipped' => $skipped
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get queue limit based on service code
     * 
     * @param string $kodeAntrian Service code
     * @return int Number of queues to generate
     */
    private function getQueueLimit($kodeAntrian)
    {
        // E = LINTAS KLASTER - LAYANAN 24 JAM
        if ($kodeAntrian === 'E') {
            return 300;
        }
        
        // A = KLASTER 3 - USIA DEWASA
        if ($kodeAntrian === 'A') {
            return 250;
        }
        
        // Default for all other services
        return 100;
    }

    /**
     * Auto-generate queues for today if not exists
     * This should be called by cron job or at midnight
     * 
     * @return array Result for all services
     */
    public function autoGenerateTodayQueues()
    {
        $tanggal = date('Y-m-d');
        
        // Generate for all lantai at once
        return $this->generateDailyQueues($tanggal, null);
    }

    /**
     * Check and generate queues if needed
     * Can be called on first access of the day
     * 
     * @param int $lantai Floor number
     * @return array Result
     */
    public function checkAndGenerate($lantai)
    {
        $tanggal = date('Y-m-d');
        
        // Check if any queues exist for this lantai today
        $exists = $this->queueModel
            ->where('tanggal', $tanggal)
            ->where('lantai', $lantai)
            ->countAllResults();

        if ($exists > 0) {
            return [
                'success' => true,
                'message' => 'Antrian sudah tersedia',
                'already_exists' => true,
                'count' => $exists
            ];
        }

        // Generate for this lantai
        return $this->generateDailyQueues($tanggal, $lantai);
    }

    /**
     * Reset and regenerate queues for a specific date
     * Only deletes queues with status 'waiting'
     * 
     * @param string $tanggal Date
     * @param int|null $lantai Floor (optional)
     * @return array Result
     */
    public function resetAndRegenerate($tanggal, $lantai = null)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Delete existing queues (only waiting status)
            $deleteBuilder = $this->queueModel
                ->where('tanggal', $tanggal)
                ->where('status', 'waiting');
            
            if ($lantai !== null) {
                $deleteBuilder->where('lantai', $lantai);
            }
            
            $deleted = $deleteBuilder->delete();

            // Generate new queues
            $result = $this->generateDailyQueues($tanggal, $lantai);

            $db->transComplete();

            if ($result['success']) {
                $result['message'] = 'Berhasil reset dan generate ulang antrian';
                $result['deleted'] = $deleted;
            }

            return $result;

        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get generation status for a specific date
     * 
     * @param string $tanggal Date
     * @param int|null $lantai Floor (optional)
     * @return array Status information
     */
    public function getGenerationStatus($tanggal, $lantai = null)
    {
        $builder = $this->queueModel
            ->select('kode_antrian, COUNT(*) as total, status')
            ->where('tanggal', $tanggal);
        
        if ($lantai !== null) {
            $builder->where('lantai', $lantai);
        }
        
        $results = $builder->groupBy(['kode_antrian', 'status'])->findAll();
        
        $status = [];
        foreach ($results as $result) {
            if (!isset($status[$result['kode_antrian']])) {
                $status[$result['kode_antrian']] = [
                    'total' => 0,
                    'waiting' => 0,
                    'calling' => 0,
                    'done' => 0,
                    'skip' => 0
                ];
            }
            
            $status[$result['kode_antrian']]['total'] += $result['total'];
            $status[$result['kode_antrian']][$result['status']] = $result['total'];
        }
        
        return $status;
    }
}