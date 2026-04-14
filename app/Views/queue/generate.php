<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .generate-card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .info-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .service-info {
        background: white;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 10px;
        border-left: 4px solid #007bff;
    }
    .status-card {
        background: white;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 10px;
        border-left: 4px solid #28a745;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-gear-fill"></i> Generate Nomor Antrian</h2>
    <a href="<?= base_url('queue/call') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Info Box -->
<div class="info-box">
    <h5><i class="bi bi-info-circle-fill text-primary"></i> Informasi</h5>
    <ul class="mb-0">
        <li>Nomor antrian akan di-generate otomatis setiap hari pukul 00:00 via cron job</li>
        <li>Gunakan form ini hanya jika auto-generate gagal atau untuk generate tanggal tertentu</li>
        <li>Setiap pelayanan memiliki jumlah nomor yang berbeda sesuai kebutuhan</li>
        <li>Format nomor: <strong>Kode + 3 digit</strong> (contoh: A001, E300)</li>
    </ul>
</div>

<!-- Service Information -->
<div class="card generate-card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Pelayanan & Jumlah Nomor</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 mb-3">
                    <div class="service-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= $service['nama_pelayanan'] ?></strong>
                                <br>
                                <small class="text-muted">Kode: <strong><?= $service['kode_antrian'] ?></strong></small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary fs-6">
                                    <?php
                                    // Display queue limits based on code
                                    if ($service['kode_antrian'] === 'E') {
                                        echo '300 Nomor';
                                    } elseif ($service['kode_antrian'] === 'A') {
                                        echo '250 Nomor';
                                    } else {
                                        echo '100 Nomor';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Generate Form -->
<div class="card generate-card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Generate Nomor Antrian</h5>
    </div>
    <div class="card-body">
        <form id="formGenerate">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" class="form-control" id="tanggalGenerate" 
                               value="<?= date('Y-m-d') ?>" required>
                        <small class="text-muted">Pilih tanggal untuk generate antrian</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lantai</label>
                        <input type="text" class="form-control" value="Lantai <?= $lantai ?>" readonly>
                        <small class="text-muted">Lantai saat ini</small>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-lightning-fill"></i> Generate Sekarang
                </button>
            </div>
        </form>

        <hr class="my-4">

        <!-- Reset & Regenerate (Admin only) -->
        <div class="alert alert-warning">
            <h6><i class="bi bi-exclamation-triangle-fill"></i> Reset & Generate Ulang</h6>
            <p class="mb-2">Gunakan ini jika ingin menghapus dan generate ulang nomor antrian yang sudah ada.</p>
            <p class="mb-3"><strong>Perhatian:</strong> Hanya nomor dengan status "Menunggu" yang akan dihapus. Nomor yang sudah dipanggil/selesai tidak akan terpengaruh.</p>
            <button type="button" class="btn btn-warning" id="btnReset">
                <i class="bi bi-arrow-clockwise"></i> Reset & Generate Ulang
            </button>
        </div>
    </div>
</div>

<!-- Current Status -->
<div class="card generate-card" id="statusCard">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Status Antrian Hari Ini</h5>
    </div>
    <div class="card-body">
        <button type="button" class="btn btn-info btn-sm mb-3" id="btnCheckStatus">
            <i class="bi bi-arrow-clockwise"></i> Refresh Status
        </button>
        <div id="statusContent">
            <p class="text-muted">Klik tombol "Refresh Status" untuk melihat status antrian hari ini</p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Generate form submit
$('#formGenerate').on('submit', function(e) {
    e.preventDefault();
    
    const tanggal = $('#tanggalGenerate').val();
    
    if (!tanggal) {
        Swal.fire('Error', 'Pilih tanggal terlebih dahulu', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Generate Antrian?',
        html: `Generate nomor antrian untuk tanggal <strong>${tanggal}</strong>?<br><small>Proses ini mungkin membutuhkan waktu beberapa detik</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Generate',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: BASE_URL + 'queue/process-generate',
                method: 'POST',
                data: { tanggal: tanggal },
                dataType: 'json'
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            const response = result.value;
            
            if (response.success) {
                // Show success with details
                let detailsHtml = '<div class="text-start mt-3">';
                detailsHtml += '<h6 class="mb-3">Detail Generate:</h6>';
                
                if (response.details && response.details.length > 0) {
                    detailsHtml += '<table class="table table-sm table-bordered">';
                    detailsHtml += '<thead><tr><th>Kode</th><th>Pelayanan</th><th>Jumlah</th></tr></thead>';
                    detailsHtml += '<tbody>';
                    response.details.forEach(detail => {
                        detailsHtml += `<tr>
                            <td><strong>${detail.kode}</strong></td>
                            <td>${detail.nama}</td>
                            <td><span class="badge bg-primary">${detail.jumlah}</span></td>
                        </tr>`;
                    });
                    detailsHtml += '</tbody>';
                    detailsHtml += `<tfoot><tr class="table-success"><td colspan="2"><strong>Total</strong></td><td><strong>${response.total}</strong></td></tr></tfoot>`;
                    detailsHtml += '</table>';
                }
                
                if (response.skipped && response.skipped.length > 0) {
                    detailsHtml += '<h6 class="mt-3 mb-2 text-warning">Dilewati (Sudah Ada):</h6>';
                    detailsHtml += '<ul class="small">';
                    response.skipped.forEach(skip => {
                        detailsHtml += `<li>${skip.nama} (${skip.kode}): ${skip.existing} nomor sudah ada</li>`;
                    });
                    detailsHtml += '</ul>';
                }
                
                detailsHtml += '</div>';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: response.message + detailsHtml,
                    width: '600px',
                    confirmButtonText: 'OK'
                }).then(() => {
                    checkStatus();
                });
            } else {
                Swal.fire('Gagal', response.message, 'error');
            }
        }
    });
});

// Reset button
$('#btnReset').on('click', function() {
    const tanggal = $('#tanggalGenerate').val();
    
    if (!tanggal) {
        Swal.fire('Error', 'Pilih tanggal terlebih dahulu', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Reset & Generate Ulang?',
        html: `<div class="alert alert-danger mb-0">
            <strong>PERHATIAN!</strong><br>
            Tindakan ini akan menghapus semua nomor antrian dengan status "Menunggu" 
            pada tanggal <strong>${tanggal}</strong> dan generate ulang dari awal.
            <br><br>
            Nomor yang sudah dipanggil, sedang dipanggil, atau selesai tidak akan terpengaruh.
        </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset & Generate',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: BASE_URL + 'queue/reset-generate',
                method: 'POST',
                data: { tanggal: tanggal },
                dataType: 'json'
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            const response = result.value;
            
            if (response.success) {
                let detailsHtml = '<div class="text-start mt-3">';
                
                if (response.deleted) {
                    detailsHtml += `<p class="alert alert-info">Dihapus: <strong>${response.deleted}</strong> nomor antrian</p>`;
                }
                
                detailsHtml += '<h6 class="mb-3">Detail Generate Baru:</h6>';
                
                if (response.details && response.details.length > 0) {
                    detailsHtml += '<table class="table table-sm table-bordered">';
                    detailsHtml += '<thead><tr><th>Kode</th><th>Pelayanan</th><th>Jumlah</th></tr></thead>';
                    detailsHtml += '<tbody>';
                    response.details.forEach(detail => {
                        detailsHtml += `<tr>
                            <td><strong>${detail.kode}</strong></td>
                            <td>${detail.nama}</td>
                            <td><span class="badge bg-primary">${detail.jumlah}</span></td>
                        </tr>`;
                    });
                    detailsHtml += '</tbody>';
                    detailsHtml += `<tfoot><tr class="table-success"><td colspan="2"><strong>Total</strong></td><td><strong>${response.total}</strong></td></tr></tfoot>`;
                    detailsHtml += '</table>';
                }
                
                detailsHtml += '</div>';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Reset & Generate Berhasil!',
                    html: response.message + detailsHtml,
                    width: '600px',
                    confirmButtonText: 'OK'
                }).then(() => {
                    checkStatus();
                });
            } else {
                Swal.fire('Gagal', response.message, 'error');
            }
        }
    });
});

// Check status
$('#btnCheckStatus').on('click', function() {
    checkStatus();
});

function checkStatus() {
    const tanggal = $('#tanggalGenerate').val();
    const lantai = '<?= $lantai ?>';
    
    $.ajax({
        url: BASE_URL + 'queue/check-status',
        method: 'GET',
        data: { tanggal: tanggal, lantai: lantai },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayStatus(response.data);
            } else {
                $('#statusContent').html('<p class="text-danger">Gagal memuat status</p>');
            }
        },
        error: function() {
            $('#statusContent').html('<p class="text-danger">Terjadi kesalahan saat memuat status</p>');
        }
    });
}

function displayStatus(data) {
    if (!data || Object.keys(data).length === 0) {
        $('#statusContent').html('<div class="alert alert-warning">Belum ada antrian yang di-generate untuk hari ini</div>');
        return;
    }
    
    let html = '<div class="row">';
    
    for (let kode in data) {
        const status = data[kode];
        html += `
            <div class="col-md-6 mb-3">
                <div class="status-card">
                    <h6 class="mb-3"><strong>Kode ${kode}</strong></h6>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="text-muted small">Total</div>
                            <div class="fs-5 fw-bold">${status.total}</div>
                        </div>
                        <div class="col-3">
                            <div class="text-warning small">Menunggu</div>
                            <div class="fs-5 fw-bold text-warning">${status.waiting}</div>
                        </div>
                        <div class="col-3">
                            <div class="text-primary small">Dipanggil</div>
                            <div class="fs-5 fw-bold text-primary">${status.calling}</div>
                        </div>
                        <div class="col-3">
                            <div class="text-success small">Selesai</div>
                            <div class="fs-5 fw-bold text-success">${status.done}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    $('#statusContent').html(html);
}

// Auto load status on page load
$(document).ready(function() {
    checkStatus();
});
</script>
<?= $this->endSection() ?>