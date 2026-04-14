<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistem Antrian' ?> - Puskesmas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar text-white">
        <div class="p-4">
            <img src="<?= base_url('assets/img/logo-putih.png') ?>" alt="Puskesmas Logo" class="img-fluid mb-3" />
        </div>
        
        <nav class="nav flex-column mt-3">
            <a class="nav-link <?= url_is('dashboard*') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <?php if (session()->get('role') == 'perawat'): ?>
                <a class="nav-link <?= url_is('queue/input*') ? 'active' : '' ?>" href="<?= base_url('queue/input') ?>">
                    <i class="bi bi-plus-circle"></i> Input Antrian
                </a>
            <?php endif; ?>
            
            <?php if (in_array(session()->get('role'), ['perawat', 'dokter'])): ?>
                <a class="nav-link <?= url_is('queue/call*') ? 'active' : '' ?>" href="<?= base_url('queue/call') ?>">
                    <i class="bi bi-megaphone"></i> Panggil Antrian
                </a>
            <?php endif; ?>
            
            <?php if (session()->get('lantai') == '1'): ?>
                <a class="nav-link" href="<?= base_url('display') ?>" target="_blank">
                    <i class="bi bi-tv"></i> Display Antrian
                </a>
            <?php elseif (session()->get('lantai') == '2_kanan'): ?>
                <a class="nav-link" href="<?= base_url('display') ?>/2_kanan" target="_blank">
                    <i class="bi bi-tv"></i> Display Antrian
                </a>
            <?php elseif (session()->get('lantai') == '2_kiri'): ?>
                <a class="nav-link" href="<?= base_url('display') ?>/2_kiri" target="_blank">
                    <i class="bi bi-tv"></i> Display Antrian
                </a>
            <?php elseif (session()->get('lantai') == '3'): ?>
                <a class="nav-link" href="<?= base_url('display') ?>/3" target="_blank">
                    <i class="bi bi-tv"></i> Display Antrian
                </a>
            <?php endif; ?>

            <?php if (session()->get('role') == 'admin'): ?>
                <div class="mt-3 px-3">
                    <small class="text-white-50">ADMIN</small>
                </div>
                <a class="nav-link <?= url_is('admin/users*') ? 'active' : '' ?>" href="<?= base_url('admin/users') ?>">
                    <i class="bi bi-people"></i> Kelola User
                </a>
                <a class="nav-link <?= url_is('admin/services*') ? 'active' : '' ?>" href="<?= base_url('admin/services') ?>">
                    <i class="bi bi-list-check"></i> Kelola Layanan
                </a>
                <a class="nav-link <?= url_is('admin/laporan*') ? 'active' : '' ?>" href="<?= base_url('admin/laporan') ?>">
                    <i class="bi bi-file-earmark-text"></i> Laporan
                </a>
            <?php endif; ?>
        </nav>
        
        <div class="position-absolute bottom-0 w-100 p-3">
            <div class="text-center mb-3">
                <p class="mb-1"><strong><?= session()->get('nama') ?></strong></p>
                <small><?= ucfirst(session()->get('role')) ?></small>
                <?php if (session()->get('lantai')): ?>
                    <br><small><?= str_replace('_', ' ', ucwords(session()->get('lantai'))) ?></small>
                <?php endif; ?>
            </div>
            <a href="<?= base_url('auth/logout') ?>" class="btn btn-light w-100">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const BASE_URL = '<?= base_url() ?>';
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>