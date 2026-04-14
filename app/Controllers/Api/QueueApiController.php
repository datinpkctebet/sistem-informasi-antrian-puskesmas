<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\QueueModel;

class QueueApiController extends BaseController
{
    protected $queueModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
    }

    public function getCurrent($lantai)
    {
        $current = $this->queueModel->getCurrentCalling($lantai);
        $waiting = $this->queueModel->getWaitingByLantai($lantai);

        return $this->response->setJSON([
            'success' => true,
            'current' => $current,
            'waiting' => $waiting,
            'timestamp' => time()
        ]);
    }

    public function getWaiting($lantai)
    {
        $waiting = $this->queueModel->getWaitingByLantai($lantai);
        $counts = $this->queueModel->getWaitingCountByService($lantai);

        return $this->response->setJSON([
            'success' => true,
            'waiting' => $waiting,
            'counts' => $counts
        ]);
    }

    public function getStatistics()
    {
        $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');
        $stats = $this->queueModel->getStatistics($tanggal);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }

    // Get queue list with filters
    public function getList()
    {
        $lantai = $this->request->getGet('lantai');
        $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');
        $pelayanan = $this->request->getGet('pelayanan');
        $status = $this->request->getGet('status');

        if (!$lantai) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lantai tidak valid'
            ]);
        }

        $queues = $this->queueModel->getQueueList($lantai, $tanggal, $pelayanan, $status);

        return $this->response->setJSON([
            'success' => true,
            'data' => $queues
        ]);
    }
}