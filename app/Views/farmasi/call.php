<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .action-btn {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 2px;
    }
    .action-btn i {
        font-size: 1.2rem;
    }
    .status-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .status-penyiapan {
        background-color: #ff9800;
        color: #fff;
    }
    .status-penyerahan {
        background-color: #28a745;
        color: #fff;
    }
    .status-diambil {
        background-color: #6c757d;
        color: #fff;
    }
    .column-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    .status-column {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border-top: 4px solid;
    }
    .status-column.penyiapan {
        border-top-color: #ff9800;
    }
    .status-column.penyerahan {
        border-top-color: #28a745;
    }
    .status-column.diambil {
        border-top-color: #6c757d;
    }
    .status-header {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 2px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .queue-count {
        background: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: bold;
    }
    .queue-list {
        max-height: 500px;
        overflow-y: auto;
    }
    .queue-row {
        background: white;
        border-left: 4px solid;
        padding: 12px;
        margin-bottom: 10px;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
    }
    .queue-row:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .queue-row.penyiapan {
        border-left-color: #ff9800;
    }
    .queue-row.penyerahan {
        border-left-color: #28a745;
    }
    .queue-row.diambil {
        border-left-color: #6c757d;
    }
    .queue-info {
        flex: 1;
    }
    .queue-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }
    .queue-patient {
        font-size: 0.9rem;
        color: #666;
        margin-top: 5px;
    }
    .queue-actions {
        display: flex;
        gap: 5px;
    }
    .empty-state {
        text-align: center;
        padding: 30px 20px;
        color: #ccc;
    }
    .empty-state i {
        font-size: 2rem;
        margin-bottom: 10px;
    }
    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .table-responsive {
        border-radius: 8px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-capsule"></i> Admin Farmasi - Antrian Obat</h2>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-bold">Pelayanan</label>
            <select class="form-select" id="filterPelayanan">
                <option value="">Semua Pelayanan</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['kode_antrian'] ?>">
                        <?= $service['nama_pelayanan'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-3">
            <label class="form-label fw-bold">Status</label>
            <select class="form-select" id="filterStatus">
                <option value="">Semua Status</option>
                <option value="farmasi_preparing">Penyiapan Obat</option>
                <option value="farmasi_serving">Penyerahan Obat</option>
                <option value="farmasi_completed">Obat Diambil</option>
            </select>
        </div>
        
        <div class="col-md-3">
            <label class="form-label fw-bold">Tanggal</label>
            <input type="date" class="form-control" id="filterTanggal" value="<?= date('Y-m-d') ?>">
        </div>
        
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-secondary w-100" id="btnResetFilter">
                <i class="bi bi-arrow-clockwise"></i> Reset Filter
            </button>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="queueTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 120px;">Nomor Antrian</th>
                        <th style="width: 250px;">Nama</th>
                        <th style="width: 150px;">Poli</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 200px;">Dipanggil Oleh</th>
                        <th style="width: 250px;">Panggilan</th>
                    </tr>
                </thead>
                <tbody id="queueTableBody">
                    <!-- Will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">Menampilkan <span id="showingInfo">0</span> data</small>
            </div>
            <nav>
                <ul class="pagination mb-0" id="pagination">
                    <!-- Will be populated by JavaScript -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Status Columns -->
<div class="column-container">
    <!-- Penyiapan Obat -->
    <div class="status-column penyiapan">
        <div class="status-header">
            <span><i class="bi bi-hourglass-split"></i> Penyiapan Obat</span>
            <span class="queue-count" id="countPenyiapan">0</span>
        </div>
        <div class="queue-list" id="queuePenyiapan">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Tidak ada antrian</p>
            </div>
        </div>
    </div>

    <!-- Penyerahan Obat -->
    <div class="status-column penyerahan">
        <div class="status-header">
            <span><i class="bi bi-hand-index"></i> Penyerahan Obat</span>
            <span class="queue-count" id="countPenyerahan">0</span>
        </div>
        <div class="queue-list" id="queuePenyerahan">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Tidak ada antrian</p>
            </div>
        </div>
    </div>

    <!-- Obat Diambil -->
    <div class="status-column diambil">
        <div class="status-header">
            <span><i class="bi bi-check-circle"></i> Obat Telah Diambil</span>
            <span class="queue-count" id="countDiambil">0</span>
        </div>
        <div class="queue-list" id="queueDiambil">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Tidak ada antrian</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const BASE_URL = '<?= base_url() ?>';
let farmasiQueues = {
    penyiapan_obat: [],
    penyerahan_obat: [],
    obat_diambil: []
};

// Load queues
function loadQueues() {
    const tanggal = $('#filterTanggal').val() || '<?= date('Y-m-d') ?>';
    const pelayanan = $('#filterPelayanan').val();
    const status = $('#filterStatus').val();
    
    $.ajax({
        url: BASE_URL + 'api/queue/list',
        method: 'GET',
        data: {
            tanggal: tanggal,
            pelayanan: pelayanan,
            status: status
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                allQueues = response.data;
                renderTable();
            }
        },
        error: function() {
            Swal.fire('Error', 'Gagal memuat data antrian', 'error');
        }
    });
}

// Load farmasi queues
function loadQueues() {
    const tanggal = new Date().toISOString().split('T')[0];
    
    $.ajax({
        url: BASE_URL + 'api/farmasi/queues',
        method: 'GET',
        data: { tanggal: tanggal },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                farmasiQueues = response.queues;
                renderQueues();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading queues:', error);
        }
    });
}

// Render all queues
function renderQueues() {
    renderQueueColumn('queuePenyiapan', farmasiQueues.penyiapan_obat, 'penyiapan', 'call');
    renderQueueColumn('queuePenyerahan', farmasiQueues.penyerahan_obat, 'penyerahan', 'finish');
    renderQueueColumn('queueDiambil', farmasiQueues.obat_diambil, 'diambil', 'none');

    // Update counts
    $('#countPenyiapan').text(farmasiQueues.penyiapan_obat.length);
    $('#countPenyerahan').text(farmasiQueues.penyerahan_obat.length);
    $('#countDiambil').text(farmasiQueues.obat_diambil.length);
}

// Render single column
function renderQueueColumn(elementId, queues, status, actionType) {
    let html = '';

    if (queues.length === 0) {
        html = `<div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Tidak ada antrian</p>
        </div>`;
    } else {
        queues.forEach(queue => {
            const queueNumber = String(queue.nomor_antrian).padStart(4, '0');
            const patientName = queue.nama_pasien || '-';
            
            let actionButtons = '';
            if (actionType === 'call') {
                actionButtons = `<button class="btn btn-sm btn-primary action-btn" 
                    onclick="callQueue(${queue.id})" title="Panggil Obat">
                    <i class="bi bi-megaphone"></i>
                </button>`;
            } else if (actionType === 'finish') {
                actionButtons = `<button class="btn btn-sm btn-success action-btn" 
                    onclick="finishQueue(${queue.id})" title="Selesai">
                    <i class="bi bi-check-lg"></i>
                </button>`;
            }

            html += `<div class="queue-row ${status}">
                <div class="queue-info">
                    <div class="queue-number">${queueNumber}</div>
                    <div class="queue-patient">${patientName}</div>
                </div>
                <div class="queue-actions">
                    ${actionButtons}
                </div>
            </div>`;
        });
    }

    $(`#${elementId}`).html(html);
}

// Call queue (waiting → calling)
function callQueue(queueId) {
    Swal.fire({
        title: 'Panggil Obat?',
        text: 'Status antrian akan diubah dari Penyiapan menjadi Penyerahan',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Panggil',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'api/farmasi/call-next',
                method: 'POST',
                data: { queue_id: queueId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const queue = response.queue;
                        const queueNumber = String(queue.nomor_antrian).padStart(4, '0');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Obat Dipanggil!',
                            html: `<h3 class="text-success">${queueNumber}</h3>
                                   <p>${queue.nama_pasien || 'Pasien'}</p>
                                   <small class="text-muted">Status: Penyerahan Obat</small>`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadQueues();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memanggil obat', 'error');
                }
            });
        }
    });
}

// Finish queue (calling → done)
function finishQueue(queueId) {
    Swal.fire({
        title: 'Selesaikan Obat?',
        text: 'Pasien telah mengambil obatnya',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'api/farmasi/finish',
                method: 'POST',
                data: { queue_id: queueId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const queue = response.queue;
                        const queueNumber = String(queue.nomor_antrian).padStart(4, '0');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Obat Selesai!',
                            html: `<h3 class="text-success">${queueNumber}</h3>
                                   <p>${queue.nama_pasien || 'Pasien'}</p>
                                   <small class="text-muted">Status: Obat Telah Diambil</small>`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadQueues();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal menyelesaikan antrian', 'error');
                }
            });
        }
    });
}

// Initialize
$(document).ready(function() {
    loadQueues();
    
    // Refresh button
    $('#btnRefresh').on('click', function() {
        loadQueues();
    });
    
    // Auto refresh every 5 seconds
    setInterval(loadQueues, 5000);
});
</script>
<?= $this->endSection() ?>