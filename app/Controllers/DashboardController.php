<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\ServiceModel;

class DashboardController extends BaseController
{
    protected $queueModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
        $this->serviceModel = new ServiceModel();
    }

    public function index()
    {
        $lantai = session()->get('lantai') ?? '1';

        $data = [
            'title'          => 'Dashboard',
            'stats'          => $this->queueModel->getStatistics(),
            'services'       => $this->serviceModel->getByLantai($lantai),
            'currentCalling' => $this->queueModel->getCurrentCalling($lantai),
            'waitingQueues'  => $this->queueModel->getWaitingByLantai($lantai),
            'lantai'         => $lantai
        ];

        return view('dashboard/index', $data);
    }
}