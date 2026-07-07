<?php

// Fungsi utama untuk menjalankan aplikasi terminal
function jalankanAplikasi() {
    while (true) {
        echo "\n=== MENU APLIKASI RAPORT ===\n";
        echo "1. Input Data Siswa\n";
        echo "2. Input Nilai\n";
        echo "3. Cetak Nilai Raport\n";
        echo "4. Analisa Tabel\n";
        echo "5. Keluar\n";
        echo "Pilih menu (1-5): ";
        
        $pilihan = trim(fgets(STDIN));

        if ($pilihan == '1') {
            inputDataSiswa();
        } elseif ($pilihan == '2') {
            inputNilaiSiswa();
        } elseif ($pilihan == '3') {
            cetakRaportSiswa();
        } elseif ($pilihan == '4') {
            analisaTabelMapel();
        } elseif ($pilihan == '5') {
            echo "Terima kasih! Aplikasi keluar.\n";
            break;
        } else {
            echo "Pilihan tidak valid, coba lagi.\n";
        }
    }
}

// MENU 1: Input Data Siswa
function inputDataSiswa() {
    echo "\n--- INPUT DATA SISWA ---\n";
    echo "Masukkan No Siswa: ";
    $no_siswa = trim(fgets(STDIN));
    echo "Masukkan Nama Siswa: ";
    $nama_siswa = trim(fgets(STDIN));

    $file = fopen("data_siswa.csv", "a");
    fwrite($file, $no_siswa . ";" . $nama_siswa . "\n");
    fclose($file);
    echo "✅ Data siswa berhasil disimpan!\n";
}

// MENU 2: Input Nilai
function inputNilaiSiswa() {
    echo "\n--- INPUT NILAI SISWA ---\n";
    echo "Masukkan No Siswa: ";
    $no_siswa = trim(fgets(STDIN));
    echo "Masukkan Nilai MTK: ";
    $mtk = trim(fgets(STDIN));
    echo "Masukkan Nilai IPA: ";
    $ipa = trim(fgets(STDIN));
    echo "Masukkan Nilai IPS: ";
    $ips = trim(fgets(STDIN));

    $file = fopen("data_nilai.csv", "a");
    fwrite($file, $no_siswa . ";" . $mtk . ";" . $ipa . ";" . $ips . "\n");
    fclose($file);
    echo "✅ Data nilai berhasil disimpan!\n";
}

// MENU 3: Cetak Nilai Per Siswa
function cetakRaportSiswa() {
    echo "\n--- CETAK NILAI RAPORT ---\n";
    echo "Masukkan No Siswa yang dicari: ";
    $cari_no = trim(fgets(STDIN));

    $nama_siswa = "Tidak Ditemukan";
    $nilai_ditemukan = false;

    // 1. Cari Nama Siswa
    if (file_exists("data_siswa.csv")) {
        $file_siswa = fopen("data_siswa.csv", "r");
        while (($line = fgets($file_siswa)) !== FALSE) {
            $data = explode(";", trim($line));
            if (isset($data[0]) && $data[0] == $cari_no) {
                $nama_siswa = $data[1];
                break;
            }
        }
        fclose($file_siswa);
    }

    // 2. Cari Nilai
    if (file_exists("data_nilai.csv")) {
        $file_nilai = fopen("data_nilai.csv", "r");
        while (($line = fgets($file_nilai)) !== FALSE) {
            $data = explode(";", trim($line));
            if (isset($data[0]) && $data[0] == $cari_no) {
                $mtk = (int)$data[1];
                $ipa = (int)$data[2];
                $ips = (int)$data[3];
                $nilai_ditemukan = true;
                break;
            }
        }
        fclose($file_nilai);
    }

    if (!$nilai_ditemukan) {
        echo "❌ Maaf, No Siswa [$cari_no] nilainya belum diinput di Menu 2.\n";
        return;
    }

    $nilai_array = [$mtk, $ipa, $ips];
    $terbesar   = max($nilai_array);
    $terkecil   = min($nilai_array);
    $jumlah     = array_sum($nilai_array);
    $rata_rata  = number_format($jumlah / count($nilai_array), 2);

    echo "\n=========================================\n";
    echo "RAPORT HASIL BELAJAR SISWA\n";
    echo "=========================================\n";
    echo "No Siswa   : $cari_no\n";
    echo "Nama       : $nama_siswa\n";
    echo "-----------------------------------------\n";
    echo "Nilai MTK  : $mtk\n";
    echo "Nilai IPA  : $ipa\n";
    echo "Nilai IPS  : $ips\n";
    echo "-----------------------------------------\n";
    echo "Hasil Perhitungan (Area Orange Atas):\n";
    echo "- Nilai Terbesar  : $terbesar\n";
    echo "- Nilai Terkecil  : $terkecil\n";
    echo "- Nilai Rata-Rata : $rata_rata\n";
    echo "- Jumlah Nilai    : $jumlah\n";
    echo "=========================================\n";
}

// MENU 4: Analisa Tabel (Kotak Rapi Mirip Format Gambar Excel)
function analisaTabelMapel() {
    if (!file_exists("data_nilai.csv")) {
        echo "❌ Belum ada data nilai untuk dianalisa.\n";
        return;
    }

    $all_mtk = [];
    $all_ipa = [];
    $all_ips = [];

    $file = fopen("data_nilai.csv", "r");
    while (($line = fgets($file)) !== FALSE) {
        $data = explode(";", trim($line));
        if (count($data) >= 4) {
            $all_mtk[] = (int)$data[1];
            $all_ipa[] = (int)$data[2];
            $all_ips[] = (int)$data[3];
        }
    }
    fclose($file);

    if (empty($all_mtk)) {
        echo "❌ Data di file data_nilai.csv masih kosong.\n";
        return;
    }

    $hitung_stats = function($mapel_array) {
        $jml = array_sum($mapel_array);
        return [
            'max'  => max($mapel_array),
            'min'  => min($mapel_array),
            'avg'  => number_format($jml / count($mapel_array), 1),
            'sum'  => $jml
        ];
    };

    $stats_mtk = $hitung_stats($all_mtk);
    $stats_ipa = $hitung_stats($all_ipa);
    $stats_ips = $hitung_stats($all_ips);

    // Output tabel dengan garis pembatas agar membentuk grid kotak
    echo "\n+-------+================+================+=================+==============+\n";
    echo   "|         tabel analisa                                                    |\n";
    echo   "+-------+================+================+=================+==============+\n";
    echo sprintf("| %-5s | %-14s | %-14s | %-15s | %-12s |\n", "MAPEL", "Nilai Terbesar", "Nilai Terkecil", "Nilai Rata-Rata", "Jumlah Nilai");
    echo   "+-------+----------------+----------------+-----------------+--------------+\n";
    echo sprintf("| %-5s | %-14s | %-14s | %-15s | %-12s |\n", "MTK", $stats_mtk['max'], $stats_mtk['min'], $stats_mtk['avg'], $stats_mtk['sum']);
    echo sprintf("| %-5s | %-14s | %-14s | %-15s | %-12s |\n", "IPA", $stats_ipa['max'], $stats_ipa['min'], $stats_ipa['avg'], $stats_ipa['sum']);
    echo sprintf("| %-5s | %-14s | %-14s | %-15s | %-12s |\n", "IPS", $stats_ips['max'], $stats_ips['min'], $stats_ips['avg'], $stats_ips['sum']);
    echo   "+-------+----------------+----------------+-----------------+--------------+\n";
}

// Jalankan aplikasinya
jalankanAplikasi();