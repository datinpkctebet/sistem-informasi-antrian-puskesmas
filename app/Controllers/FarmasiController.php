<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\ServiceModel;

class FarmasiController extends BaseController
{
    protected $queueModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
        $this->serviceModel = new ServiceModel();
    }

    // Admin Farmasi - List antrian dengan 3 status
    public function call()
    {
        $data = [
            'title'     => 'Admin Farmasi - Antrian Obat',
            'services'  => $this->serviceModel->getActiveServices()
        ];
        
        return view('farmasi/call', $data);
    }

    // API: Get farmasi queues grouped by status
    public function getQueuesByStatus()
    {
        $farmasi_lantai = 'farmasi';
        $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');

        // Get queues by status
        $penyiapanObat = $this->queueModel
            ->select('queue.*, services.nama_pelayanan')
            ->join('services', 'LEFT(queue.full_number, 1) = services.kode_antrian', 'left')
            ->where([
                'queue.lantai' => $farmasi_lantai,
                'status' => 'farmasi_preparing',
                'tanggal' => $tanggal
            ])
            ->orderBy('queue.id', 'ASC')
            ->findAll();

        $penyerahanObat = $this->queueModel
            ->select('queue.*, services.nama_pelayanan')
            ->join('services', 'LEFT(queue.full_number, 1) = services.kode_antrian', 'left')
            ->where([
                'queue.lantai' => $farmasi_lantai,
                'status' => 'farmasi_serving',
                'tanggal' => $tanggal
            ])
            ->orderBy('queue.id', 'ASC')
            ->findAll();

        $obatDiambil = $this->queueModel
            ->select('queue.*, services.nama_pelayanan')
            ->join('services', 'LEFT(queue.full_number, 1) = services.kode_antrian', 'left')
            ->where([
                'queue.lantai' => $farmasi_lantai,
                'status' => 'farmasi_completed',
                'tanggal' => $tanggal
            ])
            ->orderBy('queue.id', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'queues' => [
                'penyiapan_obat' => $penyiapanObat,
                'penyerahan_obat' => $penyerahanObat,
                'obat_diambil' => $obatDiambil
            ]
        ]);
    }

    // API: Panggil obat (update farmasi_preparing → farmasi_serving)
    public function callNext()
    {
        $queueId = $this->request->getPost('queue_id');

        if (!$queueId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue ID tidak valid'
            ]);
        }

        $queue = $this->queueModel->find($queueId);

        if (!$queue || $queue['status'] !== 'farmasi_preparing') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Antrian tidak valid atau sudah dipanggil'
            ]);
        }

        // Update queue status from farmasi_preparing to farmasi_serving
        $updated = $this->queueModel->update($queueId, [
            'status' => 'farmasi_serving',
            'waktu_panggil' => date('Y-m-d H:i:s')
        ]);

        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Antrian berhasil dipanggil',
                'queue' => $this->queueModel->find($queueId)
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal memanggil antrian'
        ]);
    }

    // API: Selesai obat (update farmasi_serving → farmasi_completed)
    public function finish()
    {
        $queueId = $this->request->getPost('queue_id');

        if (!$queueId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue ID tidak valid'
            ]);
        }

        $queue = $this->queueModel->find($queueId);

        if (!$queue || $queue['status'] !== 'farmasi_serving') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Antrian tidak dalam status penyerahan obat'
            ]);
        }

        // Update queue status from farmasi_serving to farmasi_completed
        $updated = $this->queueModel->update($queueId, [
            'status' => 'farmasi_completed',
            'waktu_selesai' => date('Y-m-d H:i:s')
        ]);

        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Obat berhasil diambil',
                'queue' => $this->queueModel->find($queueId)
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal menyelesaikan antrian'
        ]);
    }
}