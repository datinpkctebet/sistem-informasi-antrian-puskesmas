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
    .status-menunggu {
        background-color: #ffc107;
        color: #000;
    }
    .status-dipanggil {
        background-color: #28a745;
        color: #fff;
    }
    .status-selesai {
        background-color: #6c757d;
        color: #fff;
    }
    .status-dilewati {
        background-color: #3538dc;
        color: #fff;
    }
    .table-responsive {
        border-radius: 8px;
    }
    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-megaphone"></i> Nurse Station - Antrian Pasien</h2>
    <!-- <div>
        <button class="btn btn-outline-primary me-2" id="btnNurseStation">
            <i class="bi bi-hospital"></i> Nurse Station<br>
            <small>Aktif</small>
        </button>
        <button class="btn btn-outline-secondary" id="btnNurseStation24">
            <i class="bi bi-hospital"></i> Nurse Station 24 Jam<br>
            <small>Aktif</small>
        </button>
    </div> -->
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
                <option value="waiting">Menunggu</option>
                <option value="calling">Dipanggil</option>
                <option value="called">Selesai</option>
                <!-- <option value="skip">Dilewati</option> -->
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
                        <!-- <th style="width: 120px;">Waktu Ambil</th> -->
                        <th style="width: 200px;">Dipanggil Oleh</th>
                        <th style="width: 250px;">Aksi</th>
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

<!-- Modal Edit Nama Pasien -->
<div class="modal fade" id="modalEditNama" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Nama Pasien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editQueueId">
                <div class="mb-3">
                    <label class="form-label">Nomor Antrian</label>
                    <input type="text" class="form-control" id="editFullNumber" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" class="form-control" id="editNamaPasien" placeholder="Masukkan nama pasien">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveNama">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let allQueues = [];
let currentPage = 1;
const itemsPerPage = 200;

// Preview TTS function (no sound, just show text)
function previewQueue(fullNumber, pelayanan, namaPasien = '') {
    // Parse queue number (e.g., "A0200" -> "200")
    const match = fullNumber.match(/^([A-Z]+)(\d+)$/);
    
    let text = 'Nomor antrian ';
    
    if (match) {
        const numbers = match[2];
        const numberInt = parseInt(numbers, 10);
        text += numberToIndonesian(numberInt);
    } else {
        const numberInt = parseInt(fullNumber.replace(/[A-Z]/g, ''), 10);
        text += numberToIndonesian(numberInt);
    }
    
    text += ' di ' + pelayanan;
    
    if (namaPasien && namaPasien.trim() !== '') {
        text += '. Atas nama ' + namaPasien;
    }
    
    // Show preview in modal/alert
    Swal.fire({
        icon: 'info',
        title: 'Preview Panggilan',
        html: `<p class="fs-5">${text}</p><small class="text-muted">Suara akan keluar di Display TV</small>`,
        timer: 3000,
        showConfirmButton: false
    });
}

// Convert number to Indonesian words
function numberToIndonesian(num) {
    if (num === 0) return 'nol';
    
    const ones = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
    const teens = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 
                   'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
    
    if (num < 10) {
        return ones[num];
    } else if (num >= 10 && num < 20) {
        return teens[num - 10];
    } else if (num >= 20 && num < 100) {
        const tens = Math.floor(num / 10);
        const remainder = num % 10;
        return ones[tens] + ' puluh' + (remainder > 0 ? ' ' + ones[remainder] : '');
    } else if (num >= 100 && num < 200) {
        const remainder = num % 100;
        return 'seratus' + (remainder > 0 ? ' ' + numberToIndonesian(remainder) : '');
    } else if (num >= 200 && num < 1000) {
        const hundreds = Math.floor(num / 100);
        const remainder = num % 100;
        return ones[hundreds] + ' ratus' + (remainder > 0 ? ' ' + numberToIndonesian(remainder) : '');
    } else if (num >= 1000 && num < 2000) {
        const remainder = num % 1000;
        return 'seribu' + (remainder > 0 ? ' ' + numberToIndonesian(remainder) : '');
    } else if (num >= 2000 && num < 1000000) {
        const thousands = Math.floor(num / 1000);
        const remainder = num % 1000;
        return numberToIndonesian(thousands) + ' ribu' + (remainder > 0 ? ' ' + numberToIndonesian(remainder) : '');
    } else if (num >= 1000000) {
        const millions = Math.floor(num / 1000000);
        const remainder = num % 1000000;
        return numberToIndonesian(millions) + ' juta' + (remainder > 0 ? ' ' + numberToIndonesian(remainder) : '');
    }
    
    return num.toString();
}

// Get status badge HTML
function getStatusBadge(status) {
    const statusMap = {
        'waiting': '<span class="status-badge status-menunggu">Menunggu</span>',
        'calling': '<span class="status-badge status-dipanggil">Dipanggil</span>',
        'done': '<span class="status-badge status-selesai">Done</span>',
        'called': '<span class="status-badge status-selesai">Selesai</span>'
    };
    return statusMap[status] || status;
}

// Get action buttons
function getActionButtons(queue) {
    let buttons = '';
    
    // Call button (green)
    // buttons += `<button class="btn btn-primary action-btn" onclick="recallQueue(${queue.id})" title="Panggil Antrian">
    //     <i class="bi bi-volume-up"></i>
    // </button>`;

    if (queue.status === 'waiting') {
        // Call button (green)
        buttons += `<button class="btn btn-primary action-btn" onclick="callQueue(${queue.id})" title="Panggil Antrian">
            <i class="bi bi-megaphone"></i>
        </button>`;

        // Check/Done button (green checkmark)
        // buttons += `<button class="btn btn-success action-btn" onclick="doneQueue(${queue.id})" title="Selesai">
        //     <i class="bi bi-check"></i>
        // </button>`;
    }
    
    if (queue.status === 'calling') {
        // Call button (green)
        // buttons += `<button class="btn btn-primary action-btn" onclick="recallQueue(${queue.id})" title="Panggil Antrian">
        //     <i class="bi bi-volume-up"></i>
        // </button>`;
        // Next/Finish button (orange)
        // buttons += `<button class="btn btn-warning action-btn" onclick="nextQueue(${queue.id})" title="Lanjut">
        //     <i class="bi bi-skip-forward"></i>
        // </button>`;

        // Warning button (yellow)
        buttons += `<button class="btn btn-primary action-btn" onclick="warnQueue(${queue.id})" title="Peringatan">
            <i class="bi bi-volume-up"></i>
        </button>`;

        // Check/Done button (green checkmark)
        // buttons += `<button class="btn btn-success action-btn" onclick="doneQueue(${queue.id})" title="Selesai">
        //     <i class="bi bi-check"></i>
        // </button>`;
    }

    if (queue.status === 'called') {
        // Call button (green)
        buttons += `<button class="btn btn-primary action-btn" onclick="recallQueue(${queue.id})" title="Panggil Antrian">
            <i class="bi bi-volume-up"></i>
        </button>`;
    }
    
    
    return buttons;
}

// Load queues
function loadQueues() {
    const lantai = '<?= $lantai ?>';
    const tanggal = $('#filterTanggal').val() || '<?= date('Y-m-d') ?>';
    const pelayanan = $('#filterPelayanan').val();
    const status = $('#filterStatus').val();
    
    $.ajax({
        url: BASE_URL + 'api/queue/list',
        method: 'GET',
        data: {
            lantai: lantai,
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

// Render table
function renderTable() {
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageQueues = allQueues.slice(start, end);
    
    let html = '';
    
    if (pageQueues.length === 0) {
        html = '<tr><td colspan="8" class="text-center text-muted">Tidak ada data</td></tr>';
    } else {
        pageQueues.forEach((queue, index) => {
            html += `
                <tr>
                    <td>${start + index + 1}</td>
                    <td><strong>${String(queue.nomor_antrian).padStart(4, '0')}</strong></td>
                    <td>
                        <input type="text" class="form-control form-control-sm" 
                               value="${queue.nama_pasien || ''}" 
                               placeholder="Masukkan nama"
                               onblur="updateNamaPasien(${queue.id}, this.value)">
                    </td>
                    <td>${queue.nama_pelayanan}</td>
                    <td>${getStatusBadge(queue.status)}</td>
                    <td>${queue.petugas_nama || '-'}</td>
                    <td>${getActionButtons(queue)}</td>
                    </tr>
                    `;
                });
                // <td>${queue.waktu_masuk ? new Date(queue.waktu_masuk).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '-'}</td>
    }
    
    $('#queueTableBody').html(html);
    
    // Update showing info
    const totalItems = allQueues.length;
    const showingStart = totalItems > 0 ? start + 1 : 0;
    const showingEnd = Math.min(end, totalItems);
    $('#showingInfo').text(`${showingStart}-${showingEnd} dari ${totalItems}`);
    
    // Render pagination
    renderPagination();
}

// Render pagination
function renderPagination() {
    const totalPages = Math.ceil(allQueues.length / itemsPerPage);
    let html = '';
    
    // Previous button
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a>
    </li>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
        }
    }
    
    // Next button
    html += `<li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a>
    </li>`;
    
    $('#pagination').html(html);
}

// Change page
function changePage(page) {
    const totalPages = Math.ceil(allQueues.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderTable();
    }
}

// Update nama pasien
function updateNamaPasien(queueId, nama) {
    $.ajax({
        url: BASE_URL + 'queue/update-nama',
        method: 'POST',
        data: {
            queue_id: queueId,
            nama_pasien: nama
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update local data
                const queue = allQueues.find(q => q.id === queueId);
                if (queue) {
                    queue.nama_pasien = nama;
                }
            }
        }
    });
}

// Call queue
function callQueue(queueId) {
    Swal.fire({
        title: 'Panggil Antrian?',
        text: 'Antrian ini akan dipanggil dan suara keluar di Display TV',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Panggil',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Update status - Display TV will handle the sound
            $.ajax({
                url: BASE_URL + 'queue/call-specific',
                method: 'POST',
                data: { queue_id: queueId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Antrian Dipanggil!',
                            html: `<h3 class="text-primary">${response.nomor_antrian}</h3><small>Suara keluar di Display TV</small>`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadQueues();
                    }
                }
            });
        }
    });
}

// Warn queue (trigger sound at display by updating is_warning flag)
function warnQueue(queueId) {
    Swal.fire({
        title: 'Peringatan Antrian?',
        text: 'Suara akan keluar di Display TV sebagai peringatan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Peringatkan',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            // Trigger warning sound at display
            $.ajax({
                url: BASE_URL + 'queue/warn',
                method: 'POST',
                data: { queue_id: queueId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Peringatan Terkirim!',
                            html: `<h3 class="text-warning">${response.nomor_antrian}</h3><small>Suara keluar di Display TV</small>`,
                            timer: 2000,
                            showConfirmButton: false
                        });
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
                        text: 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}

// Next queue
function nextQueue(queueId) {
    Swal.fire({
        title: 'Lanjut ke Antrian Berikutnya?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjut',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'queue/finish/' + queueId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadQueues();
                    }
                }
            });
        }
    });
}

// Done queue
function doneQueue(queueId) {
    Swal.fire({
        title: 'Selesaikan Antrian?',
        text: 'Antrian ini akan ditandai selesai',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'queue/finish/' + queueId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', 'Antrian selesai', 'success');
                        loadQueues();
                    }
                }
            });
        }
    });
}

// Recall queue
function recallQueue(queueId) {
    Swal.fire({
        title: 'Panggil Antrian?',
        text: 'Antrian ini akan dipanggil dan suara keluar di Display TV',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Panggil',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Perform recall
            $.ajax({
                url: BASE_URL + 'queue/recallQueue',
                method: 'POST',
                data: { queue_id: queueId },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Antrian Dipanggil!',
                            html: `<h3 class="text-primary">${response.nomor_antrian}</h3><small>Suara keluar di Display TV</small>`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadQueues();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                }
            });
        }
    });
}

// Event listeners
$(document).ready(function() {
    loadQueues();
    
    // Filter changes
    $('#filterPelayanan, #filterStatus, #filterTanggal').on('change', function() {
        currentPage = 1;
        loadQueues();
    });
    
    // Reset filter
    $('#btnResetFilter').on('click', function() {
        $('#filterPelayanan').val('');
        $('#filterStatus').val('');
        $('#filterTanggal').val('<?= date('Y-m-d') ?>');
        currentPage = 1;
        loadQueues();
    });
    
    // Auto refresh every 10 seconds
    setInterval(loadQueues, 10000);
});
</script>
<?= $this->endSection() ?>