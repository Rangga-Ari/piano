
<?php
require_once __DIR__ . '/config/database.php';

$stmt = $pdo->query("
    SELECT
        g.finger_count,
        n.name,
        n.code,
        n.frequency
    FROM gestures g
    JOIN notes n ON g.note_id = n.id
    ORDER BY g.finger_count ASC
");

$gestures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Test Database GestureMelody</title>

    <style>
        body {
            background: #0f1020;
            color: white;
            font-family: Arial, sans-serif;
            padding: 40px;
        }

        h1 {
            color: #a78bfa;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 650px;
        }

        th, td {
            border: 1px solid #555;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #302b4a;
        }
    </style>
</head>
<body>

    <h1>GestureMelody</h1>
    <h2>Pengujian Database</h2>

    <p>PHP berhasil terhubung ke MySQL!</p>

    <table>
        <tr>
            <th>Jumlah Jari</th>
            <th>Nada</th>
            <th>Kode</th>
            <th>Frekuensi</th>
        </tr>

        <?php foreach ($gestures as $gesture): ?>
            <tr>
                <td><?= (int)$gesture['finger_count'] ?></td>
                <td><?= htmlspecialchars($gesture['name']) ?></td>
                <td><?= htmlspecialchars($gesture['code']) ?></td>
                <td><?= htmlspecialchars((string)$gesture['frequency']) ?> Hz</td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
