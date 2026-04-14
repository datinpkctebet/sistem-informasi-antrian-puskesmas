<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\ServiceModel;

class QueueController extends BaseController
{
    protected $queueModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
        $this->serviceModel = new ServiceModel();
    }

    // Input antrian (perawat only)
    public function input()
    {
        $lantai = session()->get('lantai');

        $data = [
            'title'    => 'Input Antrian',
            'services' => $this->serviceModel->getByLantai($lantai),
            'lantai'   => $lantai
        ];

        return view('queue/input', $data);
    }

    public function add()
    {
        $kodeAntrian = $this->request->getPost('kode_antrian');
        $nomorAntrian = $this->request->getPost('nomor_antrian');
        $lantai = session()->get('lantai');

        if (empty($kodeAntrian) || empty($nomorAntrian)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap'
            ]);
        }

        $result = $this->queueModel->addQueue($kodeAntrian, $nomorAntrian, $lantai);

        return $this->response->setJSON($result);
    }

    // Panggil antrian (perawat & dokter)
    public function call()
    {
        $lantai = session()->get('lantai');

        $data = [
            'title'          => 'Panggil Antrian',
            'services'       => $this->serviceModel->getByLantai($lantai),
            'currentCalling' => $this->queueModel->getCurrentCalling($lantai),
            'waitingCounts'  => $this->queueModel->getWaitingCountByService($lantai),
            'lantai'         => $lantai
        ];

        return view('queue/call', $data);
    }

    public function callNext()
    {
        $kodeAntrian = $this->request->getPost('kode_antrian');
        $loket = $this->request->getPost('loket');
        $petugasId = session()->get('user_id');

        if (empty($kodeAntrian) || empty($loket)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap'
            ]);
        }

        $result = $this->queueModel->callNext($kodeAntrian, $petugasId, $loket);

        return $this->response->setJSON($result);
    }

    // Call specific queue by ID
    public function callSpecific()
    {
        $queueId = $this->request->getPost('queue_id');
        $petugasId = session()->get('user_id');

        if (empty($queueId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue ID tidak valid'
            ]);
        }

        $result = $this->queueModel->callSpecificQueue($queueId, $petugasId);

        return $this->response->setJSON($result);
    }

    public function finish($queueId)
    {
        if ($this->queueModel->finishQueue($queueId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Antrian selesai'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal menyelesaikan antrian'
        ]);
    }

    public function skip($queueId)
    {
        if ($this->queueModel->skipQueue($queueId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Antrian dilewati'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal melewati antrian'
        ]);
    }

    // Update nama pasien
    public function updateNama()
    {
        $queueId = $this->request->getPost('queue_id');
        $namaPasien = $this->request->getPost('nama_pasien');

        if (empty($queueId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue ID tidak valid'
            ]);
        }

        if ($this->queueModel->updateNamaPasien($queueId, $namaPasien)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Nama pasien berhasil diupdate'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal mengupdate nama pasien'
        ]);
    }

    // Trigger warning (sound at display)
    public function warn()
    {
        $queueId = $this->request->getPost('queue_id');

        if (empty($queueId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue ID tidak valid'
            ]);
        }

        $result = $this->queueModel->triggerWarning($queueId);

        return $this->response->setJSON($result);
    }

    // Generate queue numbers (manual/backup)
    public function generate()
    {
        $lantai = session()->get('lantai');

        $data = [
            'title'    => 'Generate Nomor Antrian',
            'services' => $this->serviceModel->where('is_active', 1)->where('lantai', $lantai)->findAll(),
            'lantai'   => $lantai
        ];

        return view('queue/generate', $data);
    }

    // Process generate
    public function processGenerate()
    {
        $tanggal = $this->request->getPost('tanggal');
        $lantai = session()->get('lantai');

        if (empty($tanggal)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal tidak valid'
            ]);
        }

        $result = $this->generatorModel->generateDailyQueues($tanggal, $lantai);

        return $this->response->setJSON($result);
    }

    // Reset and regenerate (admin only)
    public function resetGenerate()
    {
        $tanggal = $this->request->getPost('tanggal');
        $lantai = session()->get('lantai');

        if (empty($tanggal)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal tidak valid'
            ]);
        }

        $result = $this->generatorModel->resetAndRegenerate($tanggal, $lantai);

        return $this->response->setJSON($result);
    }

    // Check status (for display on generate page)
    public function checkStatus()
    {
        $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');
        $lantai = $this->request->getGet('lantai');

        if (empty($lantai)) {
            $lantai = session()->get('lantai');
        }

        $status = $this->generatorModel->getGenerationStatus($tanggal, $lantai);

        return $this->response->setJSON([
            'success' => true,
            'data' => $status
        ]);
    }

    // Recall a specific queue
    public function recallQueue()
    {
        $queueId = $this->request->getPost('queue_id');
        
        if (!$queueId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue ID required'
            ]);
        }

        $result = $this->queueModel->recallQueue($queueId);
        
        return $this->response->setJSON($result);
    }
}