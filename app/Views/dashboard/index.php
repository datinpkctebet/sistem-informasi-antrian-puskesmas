<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Dashboard</h2>
        <p class="text-muted mb-0">
            <i class="bi bi-calendar"></i> <?= date('l, d F Y') ?>
        </p>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #667eea;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Antrian</p>
                        <h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3>
                    </div>
                    <i class="bi bi-people-fill text-primary" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #ffc107;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Menunggu</p>
                        <h3 class="mb-0"><?= $stats['waiting'] ?? 0 ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Selesai</p>
                        <h3 class="mb-0"><?= $stats['done'] ?? 0 ?></h3>
                    </div>
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #dc3545;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Dilewati</p>
                        <h3 class="mb-0"><?= $stats['skip'] ?? 0 ?></h3>
                    </div>
                    <i class="bi bi-skip-forward-fill text-danger" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Current Calling -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
                <?php if ($currentCalling): ?>
                    <h5 class="mb-3">Sedang Dipanggil</h5>
                    <div style="font-size: 5rem; font-weight: bold;"><?= $currentCalling['full_number'] ?></div>
                    <p class="mb-0 fs-5"><?= $currentCalling['nama_pelayanan'] ?></p>
                    <p class="mb-0">Loket: <?= $currentCalling['loket'] ?? '-' ?></p>
                <?php else: ?>
                    <h5>Belum Ada Antrian Dipanggil</h5>
                    <p class="mb-0">Silakan panggil antrian berikutnya</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Waiting Queue -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Antrian Menunggu</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover" id="queueTable">
            <thead>
                <tr>
                    <th>No. Antrian</th>
                    <th>Layanan</th>
                    <th>Waktu Masuk</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($waitingQueues as $queue): ?>
                <tr>
                    <td><strong><?= $queue['full_number'] ?></strong></td>
                    <td><?= $queue['nama_pelayanan'] ?></td>
                    <td><?= date('H:i', strtotime($queue['waktu_masuk'])) ?></td>
                    <td><span class="badge bg-warning">Menunggu</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#queueTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        }
    });
    
    // Auto refresh every 5 seconds
    setInterval(function() {
        location.reload();
    }, 5000);
});
</script>
<?= $this->endSection() ?>