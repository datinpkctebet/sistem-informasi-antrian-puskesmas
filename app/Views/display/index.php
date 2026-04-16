<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian - <?= ucfirst(str_replace('_', ' ', $lantai)) ?></title>
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
            background: rgba(255, 255, 255, 0.1);
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
        
        .puskesmas-name {
            color: white;
            font-size: 1.5rem;
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
        
        .main-container {
            padding: 20px 30px;
            height: calc(100vh - 85px);
            overflow-y: auto;
            display: flex;
            gap: 20px;
        }
        
        .nurse-station-section {
            flex: 0 0 280px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .nurse-station-section .service-card {
            background: linear-gradient(135deg, #16a085 0%, #1abc9c 100%);
            min-height: 180px;
        }
        
        .nurse-station-section .service-card.active {
            background: linear-gradient(135deg, #1e8a4b 0%, #094120 100%);
        }
        
        .nurse-station-section .service-name {
            font-size: 1.7rem;
        }
        
        .services-section {
            flex: 1;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .service-card {
            background: linear-gradient(135deg, #8ea14a 0%, #60721e 100%);
            border-radius: 15px;
            padding: 30px;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .service-card.active {
            background: linear-gradient(135deg, #1e8a4b 0%, #094120 100%);
            transform: scale(1.05);
            box-shadow: 0 12px 30px rgba(39, 174, 96, 0.5);
            animation: pulse 1.5s infinite;
            z-index: 10;
        }
        
        .service-card.active::before {
            opacity: 1;
        }
        
        .service-card.dimmed {
            opacity: 0.5;
            filter: grayscale(30%);
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1.05); }
            50% { transform: scale(1.08); }
        }
        
        .service-name {
            color: white;
            font-size: 1.7rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 15px;
        }

        .queue-name {
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 15px;
        }
        
        .queue-numbers {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .current-number {
            color: white;
            font-size: 8rem;
            font-weight: bold;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
            line-height: 1;
        }
        
        .waiting-count {
            color: rgba(255, 255, 255, 0.9);
            font-size: 2rem;
            font-weight: bold;
            text-align: right;
        }
        
        .waiting-count small {
            display: block;
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        .no-data {
            color: rgba(255, 255, 255, 0.6);
            font-size: 2rem;
        }
        
        /* Scrollbar styling */
        .main-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .main-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .main-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        
        .main-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .recall-badge {
            display: inline-block;
            background: #ff9800;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="<?= base_url('assets/img/logotebetputih.png') ?>" style="width: 200px; height: auto;" alt="Logo Puskesmas">
            <img src="<?= base_url('assets/img/logo-putih.png') ?>" alt="Logo SIAP">
            <!-- <div class="puskesmas-name">Puskesmas Tebet</div> -->
        </div>
        <div class="clock-display">
            <i class="bi bi-clock"></i>
            <span id="currentTime">00:00:00</span>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Nurse Station Section (Left) -->
        <div class="nurse-station-section" id="nurseStationSection">
            <!-- Will be populated by JavaScript -->
        </div>
        
        <!-- Services Section (Right) -->
        <div class="services-section">
            <div class="services-grid" id="servicesGrid">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Audio element for notification sound -->
    <audio id="callSound" preload="auto">
        <source src="<?= base_url('assets/sounds/ding2.wav') ?>" type="audio/mpeg">
    </audio>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const BASE_URL = '<?= base_url() ?>';
        const LANTAI = '<?= $lantai ?>';
        let serviceStates = {}; // Track state for each service
        let lastWarningTimes = {}; // Track warning time per service
        let currentActiveService = null; // Track currently active service
        let lastCallTime = {}; // Track when each service was last called

        // Speech & Highlight Queue System
        let callQueue = []; // Combined queue untuk speech + highlight
        let isProcessingQueue = false;
        let currentActiveCard = null;

        // Render Services
        function renderServices(services) {
            let nurseStationHtml = '';
            let servicesHtml = '';
            let newCalls = []; // Track all new calls in this render cycle
            
            // First pass: Detect ALL new queue calls
            services.forEach(service => {
                const kode = service.kode_antrian;
                const current = service.current_queue;
                const oldState = serviceStates[kode] || {};
                
                if (current) {
                    const newQueueId = current.id;
                    const isWarning = current.is_warning;
                    const callCount = current.call_count || 0;
                    
                    let isNewCall = false;
                    let callPriority = 0;
                    
                    // Check warning trigger (highest priority)
                    if (isWarning && isWarning !== lastWarningTimes[kode]) {
                        isNewCall = true;
                        callPriority = 1;
                        lastWarningTimes[kode] = isWarning;
                    }
                    // Check if queue changed OR recall
                    else if (!oldState.isFirstLoad && 
                            (oldState.queueId !== newQueueId || oldState.callCount !== callCount)) {
                        isNewCall = true;
                        callPriority = 2;
                    }
                    
                    // Add to new calls array
                    if (isNewCall) {
                        const timestamp = Date.now();
                        newCalls.push({
                            kode: kode,
                            service: service,
                            current: current,
                            timestamp: timestamp,
                            priority: callPriority
                        });
                        lastCallTime[kode] = timestamp;
                    }
                    
                    // Update state
                    serviceStates[kode] = {
                        queueId: newQueueId,
                        callCount: callCount,
                        isFirstLoad: false
                    };
                } else {
                    serviceStates[kode] = {
                        queueId: null,
                        callCount: 0,
                        isFirstLoad: oldState.isFirstLoad || false
                    };
                }
            });
            
            // Process new calls if any
            if (newCalls.length > 0) {
                // Sort by priority then timestamp
                newCalls.sort((a, b) => {
                    if (a.priority !== b.priority) return a.priority - b.priority;
                    return a.timestamp - b.timestamp;
                });
                
                console.log('🔔 New calls detected:', newCalls.length);
                
                // Add to call queue
                newCalls.forEach(call => {
                    callQueue.push({
                        kode: call.kode,
                        fullNumber: call.current.full_number,
                        pelayanan: call.service.nama_pelayanan,
                        namaPasien: call.current.nama_pasien || '',
                        callCount: call.current.call_count || 0
                    });
                });
                
                // Start processing if not already processing
                if (!isProcessingQueue) {
                    processCallQueue();
                }
            }
            
            // Second pass: Render cards
            services.forEach(service => {
                const kode = service.kode_antrian;
                const current = service.current_queue;
                const waitingCount = service.waiting_count || 0;
                
                const displayNumber = current ? String(current.nomor_antrian).padStart(4, '0') : '-';
                const displayName = current ? current.nama_pasien : '';
                const callCount = current ? (current.call_count || 0) : 0;
                const callBadge = callCount > 1 ? `<span class="recall-badge">Panggilan ke-${callCount}</span>` : '';

                // Determine if this card should be active
                const isActive = (currentActiveCard === kode);
                const activeClass = isActive ? 'active' : '';
                const dimmedClass = (currentActiveCard !== null && !isActive) ? 'dimmed' : '';
                
                const cardHtml = `
                ${service.nama_pelayanan.toLowerCase().includes('lansia') ? `<div class="service-card ${activeClass} ${dimmedClass}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" data-service-code="${kode}">` : `<div class="service-card ${activeClass} ${dimmedClass}" data-service-code="${kode}">`}
                    <div class="service-name">${service.nama_pelayanan.toUpperCase()}</div>
                    <div class="queue-name">${displayName}</div>
                    <div class="queue-numbers">
                        <div class="current-number">${displayNumber}</div>
                            ${callBadge}
                        </div>
                    </div>
                `;
                
                // Separate Nurse Station and regular services
                if (service.nama_pelayanan.toLowerCase().includes('nurse station')) {
                    nurseStationHtml += cardHtml;
                } else {
                    servicesHtml += cardHtml;
                }
            });
            
            if (nurseStationHtml === '') {
                nurseStationHtml = '<div class="text-center text-white fs-6 mt-3">Tidak ada Nurse Station</div>';
            }
            
            if (servicesHtml === '') {
                servicesHtml = '<div class="text-center text-white fs-3 mt-5">Tidak ada layanan</div>';
            }
            
            $('#nurseStationSection').html(nurseStationHtml);
            $('#servicesGrid').html(servicesHtml);
        }

        // NEW: Process call queue sequentially dengan sinkronisasi penuh
        function processCallQueue() {
            if (callQueue.length === 0) {
                isProcessingQueue = false;
                console.log('✅ Queue processing complete');

                // Clear highlight after a delay (5 seconds after last call)
                setTimeout(() => {
                    if (callQueue.length === 0) { // Double check no new calls came in
                        currentActiveCard = null;
                        clearAllHighlights();
                    }
                }, 5000);
                return;
            }
            
            isProcessingQueue = true;
            const call = callQueue.shift();
            
            console.log('📢 Processing call:', call.pelayanan, call.fullNumber);
            console.log('   Remaining in queue:', callQueue.length);
            
            // CRITICAL FIX: Set current active card dan update highlight
            currentActiveCard = call.kode;
            // Step 1: Highlight the card FIRST
            highlightCard(call.kode);
            
            // Step 2: Then play the speech
            // Speech akan auto-trigger next call via callback
            speakQueueWithCallback(
                call.fullNumber,
                call.pelayanan,
                call.namaPasien,
                call.kode,
                () => {
                    // Callback dipanggil ketika speech BENAR-BENAR selesai
                    console.log('✓ Speech completed for:', call.pelayanan);
                    
                    // Small delay before next call untuk breathing room
                    setTimeout(() => {
                        processCallQueue(); // Process next in queue
                    }, 800); // 800ms gap between calls
                }
            );
        }

        // NEW: Highlight specific card
        function highlightCard(kode) {
            console.log('🎯 Highlighting card:', kode);
            
            const allCards = document.querySelectorAll('.service-card');
            let cardFound = false;
            
            allCards.forEach(card => {
                const cardKode = card.getAttribute('data-service-code');
                
                if (cardKode === kode) {
                    card.classList.add('active');
                    card.classList.remove('dimmed');
                    cardFound = true;
                    console.log('   ✓ Card highlighted:', kode);
                } else {
                    card.classList.remove('active');
                    card.classList.add('dimmed');
                }
            });
            
            if (!cardFound) {
                console.warn('   ⚠️ Card not found for kode:', kode);
            }
        }

        // Clear all highlights
        function clearAllHighlights() {
            console.log('🧹 Clearing all highlights');
            const allCards = document.querySelectorAll('.service-card');
            allCards.forEach(card => {
                card.classList.remove('active', 'dimmed');
            });
        }

        // NEW: Speech function dengan callback yang akurat
        function speakQueueWithCallback(fullNumber, pelayanan, namaPasien, kode, onComplete) {
            // Cancel any ongoing speech
            window.speechSynthesis.cancel();
            
            // Play sound effect
            const audio = document.getElementById('callSound');
            if (audio) {
                audio.play().catch(e => console.warn('Audio play failed:', e));
            }
            
            // Wait for sound effect, then start speech
            setTimeout(() => {
                const utterance = new SpeechSynthesisUtterance();
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
                
                if (pelayanan.toLowerCase().includes('nurse station')) {
                    text += ' di ' + formatTextForSpeech(pelayanan).replace('Nurse', 'Nurs');
                } else {
                    text += ' di Ruang ' + formatTextForSpeech(pelayanan);
                }
                
                if (namaPasien && namaPasien.trim() !== '') {
                    text += '. Atas nama ' + formatTextForSpeech(namaPasien);
                }
                
                utterance.text = text;
                utterance.lang = 'id-ID';
                utterance.rate = 0.7;
                utterance.pitch = 1;
                utterance.volume = 1;
                
                console.log('🔊 Speaking:', text);
                
                let callbackFired = false;
                
                // Primary: onend event
                utterance.onend = () => {
                    if (!callbackFired) {
                        callbackFired = true;
                        console.log('   Speech onend event fired');
                        if (onComplete) onComplete();
                    }
                };
                
                // Fallback: onerror event
                utterance.onerror = (event) => {
                    if (!callbackFired) {
                        callbackFired = true;
                        console.error('   Speech error:', event.error);
                        if (onComplete) onComplete();
                    }
                };
                
                // Safety: Timeout based on text length
                // Rumus: (jumlah kata * 600ms per kata) + buffer 2 detik
                const wordCount = text.split(' ').length;
                const estimatedDuration = (wordCount * 600) + 2000;
                
                setTimeout(() => {
                    if (!callbackFired) {
                        callbackFired = true;
                        console.warn('   Speech timeout triggered after', estimatedDuration, 'ms');
                        if (onComplete) onComplete();
                    }
                }, estimatedDuration);
                
                // Start speaking
                window.speechSynthesis.speak(utterance);
                
            }, 500); // Delay after sound effect
        }

        // Keep original speakQueue for backward compatibility if needed
        function speakQueue(fullNumber, pelayanan, namaPasien = '') {
            speakQueueWithCallback(fullNumber, pelayanan, namaPasien, null, null);
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
            }
            
            return num.toString();
        }

        // Convert to lowercase and apply title case for better speech synthesis
        function formatTextForSpeech(text) {
            return text
                .toLowerCase()
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }
        
        // Update clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            $('#currentTime').text(`${hours}.${minutes}.${seconds}`);
        }
        
        // Update display
        function updateDisplay() {
            $.ajax({
                url: BASE_URL + 'api/queue/by-services/' + LANTAI,
                method: 'GET',
                dataType: 'json',
                cache: false,
                success: function(response) {
                    // console.log('Display update:', response);
                    
                    if (response.success) {
                        renderServices(response.services);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
        
        // Initialize
        $(document).ready(function() {
            updateClock();
            setInterval(updateClock, 1000);
            
            updateDisplay();
            setInterval(updateDisplay, 5000);
        });
    </script>
</body>
</html>