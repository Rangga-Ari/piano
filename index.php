
<?php
$namaWebsite = "GestureMelody";
$deskripsi = "Mainkan musik menggunakan gerakan tangan!";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($namaWebsite) ?></title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #0f1020;
            color: white;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 8%;
            background: #191a30;
        }

        .logo {
            color: #b69cff;
            font-size: 24px;
            font-weight: bold;
        }

        nav a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
        }

        .hero {
            min-height: 75vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 30px;
        }

        h1 {
            font-size: clamp(34px, 6vw, 60px);
            margin-bottom: 20px;
        }

        h1 span {
            color: #a78bfa;
        }

        p {
            color: #b7b8ce;
            font-size: 18px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 30px;
            background: #8b5cf6;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn:hover {
            background: #7c3aed;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #b7b8ce;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo">GestureMelody</div>
        <div>
            <a href="index.php">Home</a>
            <a href="#tentang">Tentang</a>
        </div>
    </nav>

    <section class="hero" id="tentang">
        <h1>Play Music With <span>Your Hands</span></h1>

        <p><?= htmlspecialchars($deskripsi) ?></p>

        <p>
            Ubah gesture jari menjadi nada Do, Re, Mi,
            Fa, Sol, La, Si, dan Do tinggi.
        </p>

        <a href="#tentang" class="btn">
            Virtual Piano Segera Hadir
        </a>
    </section>

    <footer>
        &copy; <?= date("Y") ?> GestureMelody
    </footer>

</body>
</html>
