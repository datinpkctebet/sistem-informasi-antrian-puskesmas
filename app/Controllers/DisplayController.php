<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\ServiceModel;

class DisplayController extends BaseController
{
    protected $queueModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
        $this->serviceModel = new ServiceModel();
    }

    // Display by lantai (via route parameter)
    public function index($lantai = '1')
    {
        // Validate lantai
        $validLantai = ['1', '2_kiri', '2_kanan', '3', 'lt_2_kiri', 'lt_2_kanan'];
        
        // Convert lt_ prefix to standard format
        if (strpos($lantai, 'lt_') === 0) {
            $lantai = str_replace('lt_', '', $lantai);
        }
        
        if (!in_array($lantai, ['1', '2_kiri', '2_kanan', '3'])) {
            $lantai = '1'; // Default to lantai 1
        }

        $data = [
            'lantai' => $lantai
        ];

        return view('display/index', $data);
    }

    // Display Farmasi (fixed route)
    public function indexFarmasi($lantai = 'farmasi')
    {
        $data = [
            'title' => 'Display Farmasi - Lantai ' . $lantai,
            'lantai' => $lantai
        ];

        return view('display/index_farmasi', $data);
    }

    // Legacy getData for backward compatibility
    public function getData($lantai)
    {
        $currentCalling = $this->queueModel->getCurrentCalling($lantai);
        $waitingQueues = $this->queueModel->getWaitingByLantai($lantai);

        // Ensure nama_pasien is included in response
        if ($currentCalling && !isset($currentCalling['nama_pasien'])) {
            $currentCalling['nama_pasien'] = '';
        }

        return $this->response->setJSON([
            'success' => true,
            'current' => $currentCalling,
            'waiting' => $waitingQueues,
            'timestamp' => time()
        ]);
    }

    // New API: Get all services with their current queue
    public function getByServices($lantai)
    {
        // Get all services for this lantai
        $services = $this->serviceModel->getByLantai($lantai);
        
        $result = [];
        
        foreach ($services as $service) {
            $kodeAntrian = $service['kode_antrian'];
            
            // Get current calling queue for this service
            $currentQueue = $this->queueModel->getCurrentCallingByService($kodeAntrian, $lantai);
            
            // Get waiting count for this service
            $waitingCount = $this->queueModel->getWaitingCountByServiceCode($kodeAntrian, $lantai);
            
            $result[] = [
                'kode_antrian' => $kodeAntrian,
                'nama_pelayanan' => $service['nama_pelayanan'],
                'current_queue' => $currentQueue,
                'waiting_count' => $waitingCount
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'services' => $result,
            'timestamp' => time()
        ]);
    }
}