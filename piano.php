<?php
require_once __DIR__ . '/config/database.php';
// Mengambil nada berdasarkan pemetaan gesture
$stmt = $pdo->query("
    SELECT
        g.finger_count,
        n.name,
        n.code,
        n.frequency
    FROM gestures g
    JOIN notes n ON g.note_id = n.id
    WHERE g.is_active = 1
      AND g.finger_count BETWEEN 1 AND 8
    ORDER BY g.finger_count ASC
");
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Virtual Piano - GestureMelody</title>

    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0f1020;
            color: white;
        }

        /* NAVBAR */
        nav {
            padding: 22px 8%;
            background: #191a30;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        nav a {
            color: white;
            text-decoration: none;
        }
        .logo {
            color: #a78bfa;
            font-size: 24px;
            font-weight: bold;
        }

        /* KONTEN UTAMA */
        main {
            max-width: 1100px;
            margin: 70px auto;
            padding: 0 20px;
            text-align: center;
        }
        h1 {
            font-size: 42px;
            margin-bottom: 12px;
        }
        .subtitle {
            color: #b7b8ce;
            margin-bottom: 45px;
        }

        /* TAMPILAN NADA */
        .note-display {
            background: #1c1d35;
            border: 1px solid #373454;
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .note-display h2 {
            color: #a78bfa;
            font-size: 38px;
            margin: 12px 0;
        }

        /* AREA KAMERA */
        .camera-section {
            background: #1c1d35;
            border: 1px solid #373454;
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 35px;
        }
        .camera-section h2 {
            color: #a78bfa;
            margin-bottom: 10px;
        }
        .camera-section p {
            color: #b7b8ce;
            line-height: 1.6;
        }
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            aspect-ratio: 4 / 3;
            margin: 20px auto;
            background: #090a16;
            border: 2px solid #4c3c78;
            border-radius: 15px;
            overflow: hidden;
        }
        #webcam,
        #handCanvas {
            position: absolute;
            inset: 0;
            display: block;
            width: 100%;
            height: 100%;
        }
        #webcam {
            object-fit: cover;
            transform: scaleX(-1);
        }
        #handCanvas {
            pointer-events: none;
            transform: scaleX(-1);
        }
        #startCamera {
            background: #8b5cf6;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        #startCamera:hover {
            background: #7c3aed;
        }
        #startCamera:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        #cameraStatus {
            margin-top: 15px;
        }

        /* PIANO VIRTUAL */
        .piano {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 6px;
            margin-top: 25px;
        }
        .key {
            min-width: 0;
            height: 220px;
            border: 2px solid #ccc;
            border-radius: 0 0 12px 12px;
            background: #f8f8ff;
            color: #222;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            padding: 15px 4px;
            transition: background 0.12s, transform 0.12s;
            touch-action: manipulation;
        }
        .key:hover {
            background: #ded5ff;
        }
        .key.active {
            background: #a78bfa;
            transform: translateY(4px);
        }
        .key span {
            font-weight: bold;
            font-size: 16px;
        }
        .key small {
            margin-top: 8px;
            color: #555;
        }
        .info {
            color: #b7b8ce;
            margin-top: 30px;
            line-height: 1.7;
        }

        /* RESPONSIVE */
        @media (max-width: 600px) {
            nav {
                padding: 18px 5%;
            }
            .logo {
                font-size: 19px;
            }
            main {
                margin: 35px auto;
            }
            h1 {
                font-size: 30px;
            }
            .camera-section {
                padding: 15px;
            }
            .piano {
                gap: 3px;
            }
            .key {
                height: 150px;
                padding: 8px 2px;
            }
            .key span {
                font-size: 11px;
            }
            .key small {
                font-size: 9px;
            }
        }

    </style>
</head>

<body>
<nav>
    <div class="logo">GestureMelody</div>
    <a href="index.php">Kembali ke Home</a>

</nav>

<main>
    <h1>Virtual Piano</h1>
    <p class="subtitle">
        Mainkan musik menggunakan piano virtual!
    </p>
    <div class="note-display">
        <p>Nada yang dimainkan:</p>
        <h2 id="currentNote">Belum Ada Nada</h2>
        <p id="currentFrequency">Frekuensi: -</p>

    </div>
        <div class="camera-section">
            <h2>Hand Gesture Detection</h2>
            <p>
                Aktifkan kamera untuk mulai mendeteksi tangan.
            </p>
            <div class="camera-container">
                <video id="webcam" autoplay playsinline muted></video>
                <canvas id="handCanvas"></canvas>

            </div>
            <button id="startCamera" type="button">
                Aktifkan Kamera
            </button>
            <p id="cameraStatus">
                Kamera belum aktif
            </p>

        </div>
    <div class="piano">
        <?php foreach ($notes as $note): ?>
            <button
                type="button"
                class="key"
                data-note="<?= htmlspecialchars(
                    $note['name'], ENT_QUOTES, 'UTF-8'
                ) ?>"
                data-frequency="<?= htmlspecialchars(
                    (string)$note['frequency'], ENT_QUOTES, 'UTF-8'
                ) ?>"
            >
                <span>
                    <?= htmlspecialchars($note['name']) ?>
                </span>
                <small>
                    <?= htmlspecialchars($note['code']) ?>
                </small>
            </button>
        <?php endforeach; ?>

    </div>
    <p class="info">
        Klik salah satu tuts piano untuk memainkan nada.
        <br>
        Fitur hand gesture akan ditambahkan berikutnya.
    </p>

</main>

<script>

    // =====================================

    // 1. ELEMENT VIRTUAL PIANO

    // =====================================
    const keys = document.querySelectorAll(".key");
    const currentNote =
        document.getElementById("currentNote");
    const currentFrequency =
        document.getElementById("currentFrequency");
    let audioContext = null;

    // =====================================

    // 2. FUNGSI MEMAINKAN NADA

    // =====================================
    async function playNote(frequency, noteName, key) {
        if (!Number.isFinite(frequency) || frequency <= 0) {
            console.error("Frekuensi tidak valid");
            return;
        }
        if (!audioContext) {
            audioContext = new (
                window.AudioContext ||
                window.webkitAudioContext
            )();
        }
        if (audioContext.state === "suspended") {
            await audioContext.resume();
        }
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        oscillator.type = "sine";
        oscillator.frequency.value = frequency;
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        const now = audioContext.currentTime;
        // Volume naik perlahan lalu turun
        gainNode.gain.setValueAtTime(0.0001, now);
        gainNode.gain.exponentialRampToValueAtTime(
            0.25,
            now + 0.02
        );
        gainNode.gain.exponentialRampToValueAtTime(
            0.0001,
            now + 0.45
        );
        oscillator.start(now);
        oscillator.stop(now + 0.46);
        // Menampilkan nada yang dimainkan
        currentNote.textContent = noteName;
        currentFrequency.textContent =
            "Frekuensi: " + frequency + " Hz";
        // Animasi tuts aktif
        if (key) {
            key.classList.add("active");
            setTimeout(() => {
                key.classList.remove("active");
            }, 300);
        }
    }

    // =====================================

    // 3. EVENT KLIK TUTS PIANO

    // =====================================
    keys.forEach(key => {
        key.addEventListener("click", () => {
            const frequency =
                Number(key.dataset.frequency);
            const noteName =
                key.dataset.note;
            playNote(frequency, noteName, key)
                .catch(console.error);
        });
    });

    // =====================================

    // 4. ELEMENT KAMERA

    // =====================================
    const webcam = document.getElementById("webcam");
    const startCameraButton = document.getElementById("startCamera");
    const cameraStatus = document.getElementById("cameraStatus");
    const handCanvas = document.getElementById("handCanvas");
    let cameraStream = null;
    let cameraStarting = false;

    // 5. MEMATIKAN KAMERA
    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        if (webcam.srcObject) {
            webcam.srcObject.getTracks().forEach(track => track.stop());
        }
        webcam.pause();
        webcam.srcObject = null;
        // Bersihkan landmark saat kamera dimatikan
        if (handCanvas) {
            const ctx = handCanvas.getContext("2d");
            ctx.clearRect(0, 0, handCanvas.width, handCanvas.height);
        }
        startCameraButton.disabled = false;
        startCameraButton.textContent = "Aktifkan Kamera";
        cameraStatus.textContent = "Kamera tidak aktif";
    }

    // 6. MENYALAKAN KAMERA
    async function startCamera() {
        if (cameraStarting || cameraStream) return;
        cameraStarting = true;
        startCameraButton.disabled = true;
        cameraStatus.textContent = "Mengaktifkan kamera...";
        let stream = null;
        try {
            if (!navigator.mediaDevices?.getUserMedia) {
                throw new Error("Browser tidak mendukung kamera atau halaman tidak aman.");
            }
            stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480, facingMode: "user" },
                audio: false
            });
            webcam.srcObject = stream;
            await webcam.play();
            // metadata dimensi kadang tersedia sesaat setelah play()
            if (!webcam.videoWidth || !webcam.videoHeight) {
                throw new Error("Video belum memiliki ukuran valid.");
            }
            cameraStream = stream;
            startCameraButton.textContent = "Matikan Kamera";
            cameraStatus.textContent =
                `Kamera aktif (${webcam.videoWidth} x ${webcam.videoHeight})`;
        } catch (error) {
            console.error("Error kamera:", error);
            if (stream) stream.getTracks().forEach(track => track.stop());
            webcam.pause();
            webcam.srcObject = null;
            cameraStream = null;
            startCameraButton.textContent = "Coba Lagi";
            cameraStatus.textContent = "Gagal mengaktifkan kamera: " + error.message;
        } finally {
            cameraStarting = false;
            startCameraButton.disabled = false;
        }
    }
    if (webcam && startCameraButton && cameraStatus) {
        // SATU tombol untuk menyalakan dan mematikan
        startCameraButton.addEventListener("click", () => {
            if (cameraStarting) return;
            if (cameraStream) stopCamera();
            else startCamera();
        });
        window.addEventListener("pagehide", () => {
            if (cameraStream) stopCamera();
        });
    } else {
        console.error("Elemen HTML kamera belum ditemukan.");
    }

</script>
<script type="module" src="assets/js/hand-detection.js"></script>
</body>
</html>
