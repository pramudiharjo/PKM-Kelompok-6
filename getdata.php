<?php
// Koneksi ke database
$servername = "localhost";
$username = "username"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$dbname = "cekcek"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inisialisasi variabel
$maxSeats = 50; // Jumlah maksimum kursi di bis
$total_penumpang = 0;
$kursi_terisi = 0;

// Mengambil data dari POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $direction = $_POST['direction']; // 'masuk' atau 'keluar'
    $jumlah_penumpang = (int)$_POST['jumlah_penumpang']; // Jumlah penumpang yang masuk/keluar

    // Ambil data penumpang saat ini dari database
    $sql = 'SELECT * FROM penumpang_table LIMIT 1';
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_penumpang = $row['total_penumpang'];
        $kursi_terisi = $row['kursi_terisi'];
    } else {
        // Jika tidak ada data, inisialisasi
        $total_penumpang = 0;
        $kursi_terisi = 0;
        // Masukkan data awal
        $sql = "INSERT INTO penumpang_table (penumpang_masuk, penumpang_keluar, kursi_terisi, total_penumpang, timestamp) VALUES (0, 0, 0, 0, NOW())";
        $conn->query($sql);
    }

    // Logika untuk menghitung total penumpang
    if ($direction == "masuk") {
        if ($kursi_terisi + $jumlah_penumpang <= $maxSeats) {
            $kursi_terisi += $jumlah_penumpang;
            $total_penumpang += $jumlah_penumpang;

            // Update data ke database
            $sql = "UPDATE penumpang_table SET penumpang_masuk = penumpang_masuk + ?, kursi_terisi = ?, total_penumpang = ?, timestamp = NOW() WHERE id = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $jumlah_penumpang, $kursi_terisi, $total_penumpang);
            $stmt->execute();
            echo "Penumpang masuk: $jumlah_penumpang. Total penumpang: $total_penumpang.";
        } else {
            echo "Tidak cukup kursi untuk $jumlah_penumpang penumpang.";
        }
    } elseif ($direction == "keluar") {
        if ($kursi_terisi - $jumlah_penumpang >= 0) {
            $kursi_terisi -= $jumlah_penumpang;
            $total_penumpang -= $jumlah_penumpang;

            // Update data ke database
            $sql = "UPDATE penumpang_table SET penumpang_keluar = penumpang_keluar + ?, kursi_terisi = ?, total_penumpang = ?, timestamp = NOW() WHERE id = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $jumlah_penumpang, $kursi_terisi, $total_penumpang);
            $stmt->execute();
            echo "Penumpang keluar: $jumlah_penumpang. Total penumpang: $total_penumpang.";
        } else {
            echo "Jumlah penumpang keluar melebihi jumlah penumpang yang ada.";
        }
    }
}

$conn->close();
?>