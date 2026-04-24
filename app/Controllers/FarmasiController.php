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
            ->where([
                'lantai' => $farmasi_lantai,
                'status' => 'farmasi_preparing',
                'tanggal' => $tanggal
            ])
            ->orderBy('nomor_antrian', 'ASC')
            ->findAll();

        $penyerahanObat = $this->queueModel
            ->where([
                'lantai' => $farmasi_lantai,
                'status' => 'farmasi_serving',
                'tanggal' => $tanggal
            ])
            ->orderBy('nomor_antrian', 'ASC')
            ->findAll();

        $obatDiambil = $this->queueModel
            ->where([
                'lantai' => $farmasi_lantai,
                'status' => 'farmasi_completed',
                'tanggal' => $tanggal
            ])
            ->orderBy('nomor_antrian', 'ASC')
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

    // API: Panggil obat (update waiting → calling)
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

        if (!$queue || $queue['status'] !== 'waiting') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Antrian tidak valid atau sudah dipanggil'
            ]);
        }

        // Update queue status from waiting to calling
        $updated = $this->queueModel->update($queueId, [
            'status' => 'calling',
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

    // API: Selesai obat (update calling → done)
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

        if (!$queue || $queue['status'] !== 'calling') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Antrian tidak dalam status penyerahan obat'
            ]);
        }

        // Update queue status from calling to done
        $updated = $this->queueModel->update($queueId, [
            'status' => 'done',
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