<?php
// Konfigurasi database
include "connect.php";

// Path ke file CSV
$csv_file = 'anime.csv';

try {
    // Membuka file CSV
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Membaca header (baris pertama)
        $headers = fgetcsv($handle);

        if ($headers !== FALSE) {
            // Buat tabel jika belum ada
            $create_table_query = "CREATE TABLE IF NOT EXISTS anime (";
            foreach ($headers as $header) {
                $create_table_query .= "`$header` TEXT, "; // Default tipe data TEXT
            }
            $create_table_query = rtrim($create_table_query, ", ") . ")"; // Menghapus koma terakhir
            mysqli_query($connect, $create_table_query);

            echo "Tabel 'anime' berhasil dibuat atau sudah ada.\n";

            // Menyiapkan query untuk memasukkan data
            $placeholders = implode(", ", array_fill(0, count($headers), "?"));
            $insert_query = "INSERT INTO anime (" . implode(", ", array_map(function($col) {
                return "`$col`";
            }, $headers)) . ") VALUES ($placeholders)";
            $stmt = mysqli_prepare($connect, $insert_query);

            // Bind parameter sesuai jumlah kolom
            $types = str_repeat('s', count($headers));
            $params = array_fill(0, count($headers), null);
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            // Memasukkan data dari CSV
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) === count($headers)) {
                    for ($i = 0; $i < count($headers); $i++) {
                        $params[$i] = $data[$i];
                    }
                    mysqli_stmt_execute($stmt);
                } else {
                    echo "Jumlah kolom tidak sesuai pada baris: " . implode(", ", $data) . "\n";
                }
            }

            echo "Data berhasil dimasukkan ke database.";
        } else {
            echo "File CSV kosong atau tidak valid.";
        }

        fclose($handle);
    } else {
        echo "Gagal membuka file CSV.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Tutup koneksi
mysqli_close($connect);
?>
