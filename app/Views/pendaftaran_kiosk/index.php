<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f0f7f0 0%, #e8f0e8 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }
        .header img { height: 60px; }
        .header h1 { color: #1b5e20; font-size: 1.4rem; }
        .subtitle {
            color: #555;
            margin-bottom: 30px;
            font-size: 1.1rem;
            text-align: center;
        }
        .kategori-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            width: 100%;
            max-width: 1100px;
        }
        .btn-kategori {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 22px 28px;
            text-align: left;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
            transition: transform 0.15s, box-shadow 0.15s;
            position: relative;
            overflow: hidden;
        }
        .btn-kategori:active { transform: scale(0.97); }
        .btn-kategori .kode {
            position: absolute;
            top: 15px; right: 18px;
            background: rgba(255,255,255,0.25);
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.1rem;
        }
        .btn-kategori .nama {
            font-size: 1.15rem;
            font-weight: bold;
            margin-bottom: 8px;
            padding-right: 50px;
        }
        .btn-kategori .sub {
            font-size: 0.8rem;
            opacity: 0.9;
            line-height: 1.4;
        }
        .btn-kategori.full-width { grid-column: span 2; }

        /* Modal Tiket */
        #modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.65);
            align-items: center; justify-content: center;
            z-index: 999;
        }
        #modal-overlay.show { display: flex; }
        #modal-box {
            background: white;
            border-radius: 18px;
            padding: 40px 50px;
            text-align: center;
            width: 420px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        #modal-box .label { color: #777; font-size: 1rem; margin-bottom: 8px; }
        #modal-box .kategori-nama { color: #1b5e20; font-weight: bold; margin-bottom: 15px; }
        #nomor-tiket {
            font-size: 4.5rem;
            font-weight: bold;
            color: #1b5e20;
            letter-spacing: 2px;
            margin: 10px 0;
        }
        #modal-box .waktu { color: #888; font-size: 0.9rem; margin-top: 10px; }
        #modal-box .sisa { color: #888; font-size: 0.9rem; margin-top: 5px; }
        #modal-box .print-status {
            margin-top: 20px;
            color: #2e7d32;
            font-weight: bold;
            font-size: 0.95rem;
        }
        .loading-dots::after {
            content: '...';
            animation: dots 1.2s steps(4, end) infinite;
        }
        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="<?= base_url('assets/img/logotebet.png') ?>" alt="Logo">
        <h1>Puskesmas Tebet</h1>
    </div>
    <p class="subtitle">Silakan pilih pendaftaran sesuai dengan kategori pelayanan</p>

    <div class="kategori-grid">
        <?php foreach ($kategori as $kode => $item): ?>
            <button
                class="btn-kategori <?= $kode === 'E' ? 'full-width' : '' ?>"
                onclick="ambilAntrian('<?= $kode ?>')"
                data-kode="<?= $kode ?>"
            >
                <span class="kode"><?= $kode ?></span>
                <div class="nama"><?= esc($item['nama']) ?></div>
                <?php if (! empty($item['sub'])): ?>
                    <div class="sub"><?= esc(implode(' &middot; ', $item['sub'])) ?></div>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?= $this->include('pendaftaran_kiosk/partials/modal_tiket') ?>

    <script>
        const API_AMBIL_ANTRIAN = "<?= site_url('pendaftaranKiosk/ambil-antrian') ?>";
        const PRINT_BRIDGE_URL  = "http://127.0.0.1:9100/print";

        let isProcessing = false;

        async function ambilAntrian(kategori) {
            if (isProcessing) return;
            isProcessing = true;

            document.querySelectorAll('.btn-kategori').forEach(b => b.style.pointerEvents = 'none');

            try {
                const res = await fetch(API_AMBIL_ANTRIAN, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ kategori })
                });

                const data = await res.json();

                if (!data.success) {
                    alert(data.message || 'Gagal mengambil nomor antrian.');
                    return;
                }

                tampilkanModal(data);
                await kirimKePrintBridge(data);

            } catch (err) {
                console.error(err);
                alert('Tidak dapat terhubung ke server. Silakan coba lagi.');
            } finally {
                setTimeout(() => {
                    document.querySelectorAll('.btn-kategori').forEach(b => b.style.pointerEvents = 'auto');
                    isProcessing = false;
                }, 1000);
            }
        }

        function tampilkanModal(data) {
            document.getElementById('modal-kategori-nama').textContent = data.kategori_nama;
            document.getElementById('nomor-tiket').textContent = data.full_number;
            document.getElementById('modal-waktu').textContent = data.waktu;
            document.getElementById('modal-sisa').textContent = 'Sisa antrian sebelum Anda: ' + data.sisa_antrian;
            document.getElementById('print-status').textContent = 'Tiket sedang dicetak';
            document.getElementById('print-status').classList.add('loading-dots');

            document.getElementById('modal-overlay').classList.add('show');
        }

        async function kirimKePrintBridge(data) {
            try {
                const res = await fetch(PRINT_BRIDGE_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const statusEl = document.getElementById('print-status');
                statusEl.classList.remove('loading-dots');

                if (res.ok) {
                    statusEl.textContent = '✓ Tiket berhasil dicetak';
                } else {
                    statusEl.textContent = '⚠ Gagal mencetak, silakan ambil tiket di loket';
                }
            } catch (e) {
                const statusEl = document.getElementById('print-status');
                statusEl.classList.remove('loading-dots');
                statusEl.textContent = '⚠ Printer tidak terhubung';
                console.error('Print bridge error:', e);
            } finally {
                setTimeout(() => {
                    document.getElementById('modal-overlay').classList.remove('show');
                }, 4000);
            }
        }

        document.getElementById('modal-overlay').addEventListener('click', (e) => {
            if (e.target.id === 'modal-overlay') {
                e.target.classList.remove('show');
            }
        });
    </script>
</body>
</html>