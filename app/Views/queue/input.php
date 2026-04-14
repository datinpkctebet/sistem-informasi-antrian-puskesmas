<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Input Antrian Baru</h4>
            </div>
            <div class="card-body">
                <form id="formInputAntrian">
                    <div class="mb-3">
                        <label class="form-label">Pilih Layanan <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" id="kode_antrian" name="kode_antrian" required>
                            <option value="">-- Pilih Layanan --</option>
                            <?php foreach ($services as $service): ?>
                            <option value="<?= $service['kode_antrian'] ?>">
                                <?= $service['nama_pelayanan'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor Antrian <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg" id="nomor_antrian" name="nomor_antrian" min="1" max="999" required placeholder="Contoh: 1">
                        <small class="text-muted">Masukkan nomor antrian (1-999)</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Petunjuk:</strong><br>
                        1. Pilih jenis pelayanan<br>
                        2. Masukkan nomor antrian yang akan dibuat<br>
                        3. Sistem akan membuat nomor antrian otomatis (contoh: A001, B045)
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Tambah Antrian
                        </button>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Recent Queue Display -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Antrian Terakhir Ditambahkan</h5>
            </div>
            <div class="card-body">
                <div id="recentQueues">
                    <p class="text-muted text-center">Belum ada antrian ditambahkan</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let recentQueues = [];

$('#formInputAntrian').on('submit', function(e) {
    e.preventDefault();
    
    const kodeAntrian = $('#kode_antrian').val();
    const nomorAntrian = $('#nomor_antrian').val();
    
    if (!kodeAntrian || !nomorAntrian) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Semua field harus diisi!'
        });
        return;
    }
    
    $.ajax({
        url: BASE_URL + 'queue/add',
        method: 'POST',
        data: {
            kode_antrian: kodeAntrian,
            nomor_antrian: nomorAntrian
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `Antrian <strong>${response.full_number}</strong> berhasil ditambahkan`,
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Add to recent list
                recentQueues.unshift({
                    full_number: response.full_number,
                    time: new Date().toLocaleTimeString('id-ID')
                });
                
                if (recentQueues.length > 5) recentQueues.pop();
                updateRecentList();
                
                // Reset form
                $('#formInputAntrian')[0].reset();
                $('#kode_antrian').focus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat menambahkan antrian'
            });
        }
    });
});

function updateRecentList() {
    if (recentQueues.length === 0) {
        $('#recentQueues').html('<p class="text-muted text-center">Belum ada antrian ditambahkan</p>');
        return;
    }
    
    let html = '<div class="list-group">';
    recentQueues.forEach(function(queue) {
        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span><strong>${queue.full_number}</strong></span>
                <span class="badge bg-success">${queue.time}</span>
            </div>
        `;
    });
    html += '</div>';
    
    $('#recentQueues').html(html);
}

$(document).ready(function() {
    $('#kode_antrian').focus();
});
</script>
<?= $this->endSection() ?>