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

        html, body {
            height: 100dvh;
            width: 100vw;
        }

        body {
            background: linear-gradient(180deg, #ffffff 0%, #def7d6 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        /* ===== HEADER (fluid sizing so it never eats too much vertical space) ===== */
        .header {
            background: rgba(255, 255, 255, 0.1);
            padding: clamp(6px, 1.2vh, 15px) clamp(14px, 2vw, 30px);
            height: clamp(55px, 9vh, 85px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: clamp(8px, 1vw, 15px);
            min-width: 0;
        }

        .logo-section img {
            height: clamp(28px, 5.5vh, 50px);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .clock-display {
            background: rgba(255, 255, 255, 0.2);
            color: black;
            padding: clamp(5px, 1vh, 10px) clamp(14px, 1.8vw, 25px);
            border-radius: 50px;
            font-size: clamp(1rem, 2.6vh, 1.8rem);
            font-weight: bold;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        /* ===== MAIN LAYOUT: two stacked sections (Poli / Nurse Station) ===== */
        .main-container {
            padding: clamp(8px, 1.5vh, 20px) clamp(14px, 2vw, 30px);
            height: calc(100dvh - clamp(55px, 9vh, 85px));
            display: flex;
            flex-direction: column;
            gap: clamp(6px, 1.2vh, 15px);
            overflow: hidden;
        }

        .section {
            display: flex;
            gap: clamp(10px, 1.4vw, 20px);
            min-height: 0;
            overflow: hidden;
        }

        .poli-section {
            flex: 3;
        }

        .nurse-section {
            flex: 2;
            border-top: 2px solid rgba(0,0,0,0.12);
            padding-top: clamp(6px, 1.2vh, 15px);
        }

        /* ===== LEFT: Panggilan Antrian panel (shared style, used twice) ===== */
        .call-panel {
            flex: 0 0 clamp(170px, 16vw, 280px);
            background: linear-gradient(135deg, #1e8a4b 0%, #094120 100%);
            border-radius: 20px;
            padding: clamp(8px, 1.6vh, 20px) clamp(8px, 1.2vw, 18px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
            min-height: 0;
        }

        .nurse-section .call-panel {
            background: linear-gradient(135deg, #2b6ea8 0%, #0d2e4a 100%);
        }

        .call-panel-label {
            color: rgba(255,255,255,0.85);
            font-size: clamp(0.7rem, 1.7vh, 1.1rem);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.25;
            margin-bottom: clamp(4px, 1.2vh, 15px);
        }

        .call-panel-number {
            color: white;
            font-size: clamp(1.8rem, 6vh, 4rem);
            font-weight: bold;
            text-shadow: 3px 3px 8px rgba(0,0,0,0.4);
            line-height: 1;
            margin-bottom: clamp(4px, 1vh, 12px);
            word-break: break-word;
        }

        .call-panel-service {
            color: white;
            font-size: clamp(0.65rem, 1.4vh, 1.05rem);
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: clamp(2px, 0.8vh, 8px);
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .call-panel-patient {
            color: rgba(255,255,255,0.9);
            font-size: clamp(0.6rem, 1.2vh, 0.95rem);
            font-weight: 500;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        .call-panel.flash {
            animation: flashPanel 1.5s infinite;
        }

        @keyframes flashPanel {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); box-shadow: 0 12px 30px rgba(39, 174, 96, 0.6); }
        }

        .nurse-section .call-panel.flash {
            animation: flashPanelBlue 1.5s infinite;
        }

        @keyframes flashPanelBlue {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); box-shadow: 0 12px 30px rgba(43, 110, 168, 0.6); }
        }

        /* ===== RIGHT: grid (shared for both Poli & Nurse Station) =====
           grid-auto-rows: 1fr makes rows split the available height equally
           no matter how many services/rows exist, so nothing is ever cut off. */
        .services-section {
            flex: 1;
            min-width: 0;
            min-height: 0;
            overflow: hidden;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(140px, 15vw, 260px), 1fr));
            grid-auto-rows: 1fr;
            gap: clamp(6px, 1vw, 15px);
            height: 100%;
        }

        .nurse-services-grid {
            grid-template-columns: repeat(auto-fit, minmax(clamp(110px, 11vw, 190px), 1fr));
        }

        /* ===== Shared service card style ===== */
        .service-card {
            background: linear-gradient(135deg, #4aa171 0%, #186e2b 100%);
            border-radius: clamp(8px, 1vw, 15px);
            padding: clamp(6px, 1.4vh, 22px) clamp(8px, 1.2vw, 22px);
            min-height: 0;
            min-width: 0;
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
            transform: scale(1.04);
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
            0%, 100% { transform: scale(1.04); }
            50% { transform: scale(1.07); }
        }

        .service-name {
            color: white;
            font-size: clamp(0.62rem, 1.7vh, 1.15rem);
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: clamp(3px, 0.8vh, 10px);
            line-height: 1.2;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .queue-name {
            color: white;
            font-size: clamp(0.55rem, 1.3vh, 0.9rem);
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: clamp(3px, 0.8vh, 10px);
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .queue-numbers {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            min-width: 0;
        }

        .current-number {
            color: white;
            font-size: clamp(1.3rem, 4.8vh, 3.4rem);
            font-weight: bold;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
            line-height: 1;
        }

        /* Nurse Station cards: more compact (matches sketch's smaller boxes) */
        .compact-card .service-name {
            font-size: clamp(0.55rem, 1.35vh, 0.95rem);
        }

        .compact-card .queue-name {
            font-size: clamp(0.5rem, 1.05vh, 0.75rem);
        }

        .compact-card .current-number {
            font-size: clamp(1.1rem, 3.4vh, 2.3rem);
        }

        .recall-badge {
            display: inline-block;
            background: #ff9800;
            color: white;
            padding: 2px 7px;
            border-radius: 12px;
            font-size: clamp(0.5rem, 1vh, 0.75rem);
            margin-left: 8px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="<?= base_url('assets/img/logotebethitam.png') ?>" style="width: 200px; height: auto;" alt="Logo Puskesmas">
            <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo SIAP">
        </div>
        <div class="clock-display">
            <i class="bi bi-clock"></i>
            <span id="currentTime">00:00:00</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Top: Nurse Station section -->
        <div class="section nurse-section">
            <div class="call-panel" id="callPanelNurse">
                <div class="call-panel-label">Nurse Station<br>Sedang Dipanggil</div>
                <div class="call-panel-number" id="callPanelNumberNurse">-</div>
                <div class="call-panel-service" id="callPanelServiceNurse"></div>
                <div class="call-panel-patient" id="callPanelPatientNurse"></div>
            </div>
            <div class="services-section">
                <div class="services-grid nurse-services-grid" id="nurseStationGrid">
                    <!-- Nurse Station cards -->
                </div>
            </div>
        </div>

        <!-- Bottom: Poli section -->
        <div class="section poli-section">
            <div class="call-panel" id="callPanelPoli">
                <div class="call-panel-label">Poli<br>Sedang Dipanggil</div>
                <div class="call-panel-number" id="callPanelNumberPoli">-</div>
                <div class="call-panel-service" id="callPanelServicePoli"></div>
                <div class="call-panel-patient" id="callPanelPatientPoli"></div>
            </div>
            <div class="services-section">
                <div class="services-grid" id="servicesGrid">
                    <!-- Poli cards -->
                </div>
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
                        nomorAntrian: call.current.nomor_antrian,
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

            // Second pass: Render cards - split into Nurse Station (bottom section) vs Poli (top section)
            services.forEach(service => {
                const kode = service.kode_antrian;
                const current = service.current_queue;
                const isNurse = service.nama_pelayanan.toLowerCase().includes('nurse station');

                const displayNumber = current ? String(current.nomor_antrian).padStart(4, '0') : '-';
                const displayName = current ? current.nama_pasien : '';
                const callCount = current ? (current.call_count || 0) : 0;
                const callBadge = callCount > 1 ? `<span class="recall-badge">Panggilan ke-${callCount}</span>` : '';

                // Determine if this card should be active
                const isActive = (currentActiveCard === kode);
                const activeClass = isActive ? 'active' : '';
                const dimmedClass = (currentActiveCard !== null && !isActive) ? 'dimmed' : '';
                const compactClass = isNurse ? 'compact-card' : '';

                const cardHtml = `
                ${service.nama_pelayanan.toLowerCase().includes('nurse station') ? `<div class="service-card ${compactClass} ${activeClass} ${dimmedClass}" style="background: linear-gradient(135deg, #2b6ea8 0%, #0d2e4a 100%);" data-service-code="${kode}">` : `<div class="service-card ${compactClass} ${activeClass} ${dimmedClass}" data-service-code="${kode}">`}

                    <div class="service-name">${service.nama_pelayanan.toUpperCase()}</div>
                    <div class="queue-name">${displayName}</div>
                    <div class="queue-numbers">
                        <div class="current-number">${displayNumber}</div>
                            ${callBadge}
                        </div>
                    </div>
                `;

                if (isNurse) {
                    nurseStationHtml += cardHtml;
                } else {
                    servicesHtml += cardHtml;
                }
            });

            if (nurseStationHtml === '') {
                nurseStationHtml = '<div class="text-center text-muted fs-6">Tidak ada Nurse Station</div>';
            }

            if (servicesHtml === '') {
                servicesHtml = '<div class="text-center text-muted fs-6">Tidak ada layanan</div>';
            }

            $('#nurseStationGrid').html(nurseStationHtml);
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

            // Step 1: Update the correct "Panggilan Antrian" panel (Poli or Nurse Station)
            updateCallPanel(call);

            // Step 2: Highlight the card
            highlightCard(call.kode);

            // Step 3: Then play the speech
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

        // NEW: Update the correct big "Panggilan Antrian" panel (Poli vs Nurse Station) with the latest call
        function updateCallPanel(call) {
            const isNurse = call.pelayanan.toLowerCase().includes('nurse station');
            const suffix = isNurse ? 'Nurse' : 'Poli';
            const panelId = isNurse ? 'callPanelNurse' : 'callPanelPoli';

            const panel = document.getElementById(panelId);
            $('#callPanelNumber' + suffix).text(call.nomorAntrian.toString().padStart(4, '0'));
            $('#callPanelService' + suffix).text(call.pelayanan.toUpperCase());
            $('#callPanelPatient' + suffix).text(call.namaPasien || '');

            // Small flash animation to draw attention
            panel.classList.remove('flash');
            void panel.offsetWidth; // force reflow so animation can restart
            panel.classList.add('flash');
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
                    text += ' di Ruang ' + formatTextForSpeech(pelayanan).replace('R.', '').trim();;
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