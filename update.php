<?php
include 'database.php';

if (!empty($_POST)) {
    $action = $_POST['action']; // 'enter' untuk penumpang masuk, 'exit' untuk penumpang keluar
    $number = (int)$_POST['number']; // Jumlah penumpang yang masuk/keluar
    $busId = 1; // ID bus, bisa disesuaikan
    $maxSeats = 50; // Jumlah maksimum kursi di bis

    $pdo = Database::connect();
    $sql = 'SELECT * FROM penumpang_table WHERE id = :busId LIMIT 1'; // Ambil data penumpang
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['busId' => $busId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika tidak ada data, masukkan data baru
    if (!$row) {
        $row = [
            'penumpang_masuk' => 0,
            'penumpang_keluar' => 0,
            'kursi_terisi' => 0,
            'total_penumpang' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $sql = 'INSERT INTO penumpang_table (penumpang_masuk, penumpang_keluar, kursi_terisi, total_penumpang, timestamp) VALUES (:masuk, :keluar, :terisi, :total, :timestamp)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'masuk' => 0,
            'keluar' => 0,
            'terisi' => 0,
            'total' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    // Logika untuk penumpang masuk
    if ($action === 'enter') {
        if ($row['kursi_terisi'] + $number <= $maxSeats) {
            $row['penumpang_masuk'] += $number;
            $row['kursi_terisi'] += $number;
            $row['total_penumpang'] += $number;

            // Update data ke database
            $sql = 'UPDATE penumpang_table SET penumpang_masuk = :masuk, kursi_terisi = :terisi, total_penumpang = :total, timestamp = NOW() WHERE id = :busId';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'masuk' => $row['penumpang_masuk'],
                'terisi' => $row['kursi_terisi'],
                'total' => $row['total_penumpang'],
                'busId' => $busId
            ]);
            echo json_encode(['message' => "Penumpang masuk: $number", 'total_penumpang' => $row['total_penumpang'], 'kursi_terisi' => $row['kursi_terisi']]);
        } else {
            echo json_encode(['message' => "Tidak cukup kursi untuk $number penumpang."]);
        }
    }

    // Logika untuk penumpang keluar
    elseif ($action === 'exit') {
        if ($row['kursi_terisi'] - $number >= 0) {
            $row['penumpang_keluar'] += $number;
            $row['kursi_terisi'] -= $number;
            $row['total_penumpang'] -= $number;

            // Update data ke database
            $sql = 'UPDATE penumpang_table SET penumpang_keluar = :keluar, kursi_terisi = :terisi, total_penumpang = :total, timestamp = NOW() WHERE id = :busId';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'keluar' => $row['penumpang_keluar'],
                'terisi' => $row['kursi_terisi'],
                'total' => $row['total_penumpang'],
                'busId' => $busId
            ]);
            echo json_encode(['message' => "Penumpang keluar: $number", 'total_penumpang' => $row['total_penumpang'], 'kursi_terisi' => $row['