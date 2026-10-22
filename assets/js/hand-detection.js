
import {
    HandLandmarker,
    FilesetResolver,
    DrawingUtils
} from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision/+esm";


// Mengambil elemen dari piano.php
const webcam = document.getElementById("webcam");
const canvas = document.getElementById("handCanvas");
const ctx = canvas.getContext("2d");

let handLandmarker = null;
let lastVideoTime = -1;
let animationId = null;

// 1. Memuat model MediaPipe
async function initializeHandDetection() {
    try {
        console.log("Memuat MediaPipe...");

        const vision = await FilesetResolver.forVisionTasks(
            "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision/wasm"
        );

        handLandmarker =
            await HandLandmarker.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath:
                        "https://storage.googleapis.com/mediapipe-models/hand_landmarker/hand_landmarker/float16/1/hand_landmarker.task",
                    delegate: "CPU"
                },
                runningMode: "VIDEO",
                numHands: 2
            });

        console.log("MediaPipe siap mendeteksi tangan!");

    } catch (error) {
        console.error("Gagal memuat MediaPipe:", error);
    }
}


// =====================================
// MENGHITUNG JARI YANG TERANGKAT
// =====================================

// Jari telunjuk, tengah, manis, dan kelingking
const FINGER_JOINTS = [
    [8, 6, 5],    // Telunjuk
    [12, 10, 9],  // Tengah
    [16, 14, 13], // Manis
    [20, 18, 17]  // Kelingking
];

// Menghitung jarak antara dua landmark
function distance(a, b) {
    return Math.hypot(
        a.x - b.x,
        a.y - b.y
    );
}

// Menghitung jari pada satu tangan
function countFingers(landmarks) {
    let count = 0;

    const wrist = landmarks[0];

    // Hitung empat jari selain ibu jari
    for (const [tip, pip, mcp] of FINGER_JOINTS) {
        const ujung = landmarks[tip];
        const sendi = landmarks[pip];
        const pangkal = landmarks[mcp];

        const tipDistance = distance(ujung, wrist);
        const pipDistance = distance(sendi, wrist);

        const fingerLength = distance(pangkal, sendi);

        // Jari dianggap terbuka jika ujungnya
        // cukup jauh dari pergelangan dibanding sendinya
        if (tipDistance > pipDistance + fingerLength * 0.3) {
            count++;
        }
    }

    // Menghitung ibu jari
    const thumbTip = landmarks[4];
    const thumbJoint = landmarks[3];
    const indexBase = landmarks[5];

    const thumbOpen =
        distance(thumbTip, indexBase) >
        distance(thumbJoint, indexBase) * 1.35;

    if (thumbOpen) {
        count++;
    }

    return count;
}

function detectHands() {
    // Kamera OFF: hentikan proses deteksi
    if (!webcam.srcObject ||
        !webcam.srcObject.active ||
        webcam.paused) {

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        animationId = null;
        lastVideoTime = -1;
        return;
    }

    if (handLandmarker &&
        webcam.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA &&
        webcam.videoWidth > 0) {

        // Samakan ukuran canvas dan video
        if (canvas.width !== webcam.videoWidth ||
            canvas.height !== webcam.videoHeight) {
            canvas.width = webcam.videoWidth;
            canvas.height = webcam.videoHeight;
        }

        if (webcam.currentTime !== lastVideoTime) {
            lastVideoTime = webcam.currentTime;

            const results = handLandmarker.detectForVideo(
                webcam,
                performance.now()
            );

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const drawingUtils = new DrawingUtils(ctx);


let totalFingers = 0;

for (const landmarks of results.landmarks) {

    // Menggambar garis tangan
    drawingUtils.drawConnectors(
        landmarks,
        HandLandmarker.HAND_CONNECTIONS,
        { color: "#00FF88", lineWidth: 3 }
    );
                
                drawingUtils.drawLandmarks(
                    landmarks,
                    { color: "#FF3366", radius: 4 }
                );

                // Menghitung jari tangan ini
                const fingerCount = countFingers(landmarks);

                // Menjumlahkan dengan tangan lainnya
                totalFingers += fingerCount;
            }

            // Tampilkan hasil di Console,
            // hanya ketika jumlahnya berubah
            if (window.lastFingerCount !== totalFingers) {
                window.lastFingerCount = totalFingers;

                console.log(
                    "Jumlah tangan:",
                    results.landmarks.length,
                    "| Total jari:",
                    totalFingers
                );
            }

        }
    }

    animationId = requestAnimationFrame(detectHands);
}

// 3. Mulai deteksi saat video berjalan
webcam.addEventListener("playing", () => {
    if (animationId === null) {
        lastVideoTime = -1;
        detectHands();
    }
});

// 4. Bersihkan canvas jika kamera berhenti
function stopDetection() {
    if (animationId !== null) {
        cancelAnimationFrame(animationId);
        animationId = null;
    }

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    lastVideoTime = -1;
}

webcam.addEventListener("pause", stopDetection);
webcam.addEventListener("emptied", stopDetection);

// 5. Jalankan inisialisasi
initializeHandDetection().then(() => {
    // Jika kamera sudah aktif sebelum model selesai dimuat
    if (webcam.srcObject?.active && !webcam.paused &&
        animationId === null) {
        detectHands();
    }
});
