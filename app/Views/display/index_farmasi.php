<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian Farmasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(180deg, #75d159 0%, #75d159 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            height: 100vh;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-section img {
            height: 50px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .title-section {
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .clock-display {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-size: 1.8rem;
            font-weight: bold;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .container-main {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px 30px;
            height: calc(100vh - 90px);
            overflow-y: auto;
        }
        
        .status-column {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .column-header {
            padding: 20px;
            color: white;
            font-weight: bold;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: space-between;
        }
        
        .column-header.preparing {
            background: linear-gradient(135deg, #6c6ee9 0%, #3538dc 100%);
        }
        
        .column-header.serving {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
        }
        
        .column-header.completed {
            background: linear-gradient(135deg, #8e959b 0%, #6c757d 100%);
        }
        
        .column-count {
            background: rgba(255, 255, 255, 0.3);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 1rem;
        }
        
        .queue-items {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }
        
        .queue-items::-webkit-scrollbar {
            width: 8px;
        }
        
        .queue-items::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .queue-items::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 10px;
        }
        
        .queue-items::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
        
        .queue-item {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-left: 5px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .queue-item.preparing {
            border-left-color: #3538dc;
        }
        
        .queue-item.serving {
            border-left-color: #4caf50;
        }
        
        .queue-item.completed {
            border-left-color: #2196f3;
        }
        
        .queue-item.highlight {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.5);
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1.05);
            }
            50% {
                transform: scale(1.08);
            }
        }
        
        .queue-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            line-height: 1;
        }
        
        .queue-item.highlight .queue-number {
            color: white;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        .queue-patient {
            font-size: 0.95rem;
            color: #666;
            margin-top: 8px;
            font-weight: 500;
        }
        
        .queue-item.highlight .queue-patient {
            color: rgba(255, 255, 255, 0.95);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #ccc;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            font-size: 0.9rem;
        }

        @media (max-width: 1920px) {
            .queue-number {
                font-size: 2rem;
            }
            
            .column-header {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="<?= base_url('assets/img/logotebetputih.png') ?>" style="width: 200px; height: auto;" alt="Logo">
            <img src="<?= base_url('assets/img/logo-putih.png') ?>" alt="Logo SIAP">
            <!-- <div class="title-section"><i class="bi bi-capsule"></i> FARMASI</div> -->
        </div>
        <div class="clock-display">
            <i class="bi bi-clock"></i>
            <span id="currentTime">00:00:00</span>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="container-main">
        <!-- Obat Sedang Disiapkan -->
        <div class="status-column">
            <div class="column-header preparing">
                <span><i class="bi bi-hourglass-split"></i> Obat Sedang Disiapkan</span>
                <span class="column-count" id="countPreparing">0</span>
            </div>
            <div class="queue-items" id="farmasiPreparing">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Tidak ada antrian</p>
                </div>
            </div>
        </div>
        
        <!-- Penyerahan Obat -->
        <div class="status-column">
            <div class="column-header serving">
                <span><i class="bi bi-capsule"></i> Penyerahan Obat</span>
                <span class="column-count" id="countServing">0</span>
            </div>
            <div class="queue-items" id="farmasiServing">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Tidak ada antrian</p>
                </div>
            </div>
        </div>
        
        <!-- Obat Sudah Diambil -->
        <div class="status-column">
            <div class="column-header completed">
                <span><i class="bi bi-check-circle"></i> Obat Sudah Diambil</span>
                <span class="column-count" id="countCompleted">0</span>
            </div>
            <div class="queue-items" id="farmasiCompleted">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Tidak ada antrian</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Audio for call sound -->
    <audio id="callSound" preload="auto">
        <source src="<?= base_url('assets/sounds/ding2.wav') ?>" type="audio/wav">
    </audio>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const BASE_URL = '<?= base_url() ?>';
        let lastHighlightedId = null;
        let serviceStates = {};

        // Update clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            $('#currentTime').text(`${hours}.${minutes}.${seconds}`);
        }

        // Render queue items
        function renderQueueColumn(containerId, items, type, highlightId) {
            let html = '';
            
            if (items.length === 0) {
                html = `<div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Tidak ada antrian</p>
                </div>`;
            } else {
                items.forEach(item => {
                    const queueNumber = String(item.nomor_antrian).padStart(4, '0');
                    const patientName = item.nama_pasien || '-';
                    const isHighlight = (item.id === highlightId && type === 'serving');
                    const highlightClass = isHighlight ? 'highlight' : '';
                    
                    html += `<div class="queue-item ${type} ${highlightClass}">
                        <div class="queue-number">${queueNumber}</div>
                        <div class="queue-patient">${patientName}</div>
                    </div>`;
                });
            }
            
            $(`#${containerId}`).html(html);
        }

        // Fetch and render farmasi queues
        function fetchAndRenderQueues() {
            const tanggal = new Date().toISOString().split('T')[0];
            
            $.ajax({
                url: BASE_URL + 'api/farmasi/queues',
                method: 'GET',
                data: { tanggal: tanggal },
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        renderFarmasi(response.queues);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching queues:', error);
                }
            });
        }

        // Render farmasi display
        function renderFarmasi(queues) {
            const preparing = queues.penyiapan_obat || [];
            const serving = queues.penyerahan_obat || [];
            const completed = queues.obat_diambil || [];
            
            // Update counts
            $('#countPreparing').text(preparing.length);
            $('#countServing').text(serving.length);
            $('#countCompleted').text(completed.length);

            // Check if there's a new call in serving queue
            let newHighlight = null;
            if (serving.length > 0) {
                const firstServing = serving[0];
                if (firstServing.id !== lastHighlightedId) {
                    newHighlight = firstServing;
                    lastHighlightedId = firstServing.id;
                    playCallNotification(firstServing);
                }
            }

            // Render columns
            renderQueueColumn('farmasiPreparing', preparing, 'preparing', null);
            renderQueueColumn('farmasiServing', serving, 'serving', lastHighlightedId);
            renderQueueColumn('farmasiCompleted', completed, 'completed', null);
        }

        // Play call notification
        function playCallNotification(queue) {
            // Play sound
            const audio = document.getElementById('callSound');
            if (audio) {
                audio.play().catch(e => console.warn('Audio play failed:', e));
            }
            
            // Speak queue number
            setTimeout(() => {
                const queueNumber = String(queue.nomor_antrian).padStart(4, '0');
                const patientName = queue.nama_pasien || '';
                
                const utterance = new SpeechSynthesisUtterance();
                let text = `Nomor antrian ${numberToIndonesian(queue.nomor_antrian)} di farmasi`;
                
                if (patientName) {
                    text += `. Atas nama ${patientName}`;
                }
                
                utterance.text = text;
                utterance.lang = 'id-ID';
                utterance.rate = 0.7;
                utterance.pitch = 1;
                utterance.volume = 1;
                
                console.log('🔊 Speaking:', text);
                window.speechSynthesis.speak(utterance);
            }, 500);
        }

        // Convert number to Indonesian
        function numberToIndonesian(num) {
            const ones = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
            const teens = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 
                           'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
            
            if (num === 0) return 'nol';
            if (num < 10) return ones[num];
            if (num >= 10 && num < 20) return teens[num - 10];
            if (num >= 20 && num < 100) {
                const tens = Math.floor(num / 10);
                const remainder = num % 10;
                return ones[tens] + ' puluh' + (remainder > 0 ? ' ' + ones[remainder] : '');
            }
            if (num >= 100 && num < 1000) {
                const hundreds = Math.floor(num / 100);
                const remainder = num % 100;
                return (hundreds === 1 ? 'seratus' : ones[hundreds] + ' ratus') + 
                       (remainder > 0 ? ' ' + numberToIndonesian(remainder) : '');
            }
            if (num >= 1000 && num < 10000) {
                const thousands = Math.floor(num / 1000);
                const remainder = num % 1000;
                return (thousands === 1 ? 'seribu' : numberToIndonesian(thousands) + ' ribu') + 
                       (remainder > 0 ? ' ' + numberToIndonesian(remainder) : '');
            }
            return num.toString();
        }

        // Initialize
        $(document).ready(function() {
            updateClock();
            setInterval(updateClock, 1000);
            
            // Initial fetch
            fetchAndRenderQueues();
            
            // Auto refresh every 5 seconds
            setInterval(fetchAndRenderQueues, 5000);
        });
    </script>
</body>
</html>