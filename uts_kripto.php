<?php
// Mencegah error notice muncul di layar (opsional, tapi bagus untuk produksi)
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Inisialisasi variabel dengan default value agar tidak undefined
$page = $_GET['page'] ?? 'home';
$result = '';
$input_text = $_POST['input_text'] ?? '';
$key = $_POST['key'] ?? '';
$action = $_POST['action'] ?? 'encrypt';

// Logika Pemrosesan Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($page) {
        case 'caesar':
            // Pastikan key berupa angka
            $shift = (int)$key;
            // Handle key negatif agar perputarannya tetap benar
            if ($shift < 0) {
                $shift = 26 - (abs($shift) % 26);
            }
            
            if ($action == 'decrypt') {
                $shift = 26 - ($shift % 26);
            }
            
            $res = '';
            $text_length = strlen($input_text);
            
            for ($i = 0; $i < $text_length; $i++) {
                $c = $input_text[$i];
                if (ctype_alpha($c)) {
                    $ascii = ord(ctype_upper($c) ? 'A' : 'a');
                    $res .= chr(($ascii + (ord($c) - $ascii + $shift) % 26));
                } else {
                    $res .= $c; // Biarkan spasi, angka, atau simbol
                }
            }
            $result = $res;
            break;

        case 'xor':
            $key_len = strlen($key);
            
            // Cegah error "Modulo by zero" jika key kosong
            if ($key_len === 0) {
                $result = "⚠️ Error: Key tidak boleh kosong untuk proses XOR!";
                break;
            }

            if ($action == 'encrypt') {
                $xor_res = '';
                for ($i = 0; $i < strlen($input_text); $i++) {
                    $xor_res .= $input_text[$i] ^ $key[$i % $key_len];
                }
                $result = bin2hex($xor_res); // Ubah ke Hex
            } else {
                // Cegah error hex2bin() dengan cek validitas heksadesimal dan panjang genap
                if (ctype_xdigit($input_text) && strlen($input_text) % 2 === 0) {
                    $raw = hex2bin($input_text);
                    $xor_res = '';
                    for ($i = 0; $i < strlen($raw); $i++) {
                        $xor_res .= $raw[$i] ^ $key[$i % $key_len];
                    }
                    $result = $xor_res;
                } else {
                    $result = "⚠️ Error: Teks untuk dekripsi XOR harus berupa Hexadecimal yang valid (karakter 0-9, a-f) dan jumlahnya genap!";
                }
            }
            break;

        case 'sha256':
            // SHA-256 aman walau input kosong
            $result = hash('sha256', $input_text);
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super App Kriptografi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; padding-top: 25px; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 12px 20px; display: block; margin-bottom: 8px; border-radius: 6px; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #3498db; color: white; }
        .content-box { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar">
                <h4 class="text-white text-center mb-4">Crypto Tools</h4>
                <a href="?page=home" class="<?= $page == 'home' ? 'active' : '' ?>">🏠 Dashboard Utama</a>
                <a href="?page=caesar" class="<?= $page == 'caesar' ? 'active' : '' ?>">🔠 Caesar Cipher</a>
                <a href="?page=xor" class="<?= $page == 'xor' ? 'active' : '' ?>">🔐 XOR Cipher</a>
                <a href="?page=sha256" class="<?= $page == 'sha256' ? 'active' : '' ?>">🏷️ SHA-256 Hash</a>
            </div>

            <div class="col-md-9 p-4 p-md-5">
                <div class="content-box">
                    
                    <?php switch($page): case 'home': ?>
                        <h2 class="mb-3">Web Tools Kriptografi (Super App)</h2>
                        <p class="text-secondary">Pilih algoritma di menu sebelah kiri. Aplikasi ini dirancang menggunakan konsep Single File Application dengan PHP murni.</p>
                        <div class="alert alert-info mt-4">
                            <strong>Fitur Aktif:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Caesar Cipher (Enkripsi/Dekripsi dengan pergeseran alfabet)</li>
                                <li>XOR Cipher (Enkripsi ke Hex, Dekripsi dari Hex)</li>
                                <li>SHA-256 Generator (Hashing satu arah)</li>
                            </ul>
                        </div>
                    
                    <?php break; case 'caesar': ?>
                        <h2 class="mb-4">Caesar Cipher</h2>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Teks:</label>
                                <textarea name="input_text" class="form-control" rows="4" placeholder="Masukkan teks di sini..." required><?= htmlspecialchars($input_text) ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Key (Angka Shift):</label>
                                    <input type="number" name="key" class="form-control" value="<?= htmlspecialchars($key) ?>" placeholder="Contoh: 3" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Mode:</label>
                                    <select name="action" class="form-select">
                                        <option value="encrypt" <?= $action == 'encrypt' ? 'selected' : '' ?>>Enkripsi</option>
                                        <option value="decrypt" <?= $action == 'decrypt' ? 'selected' : '' ?>>Dekripsi</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Proses Caesar</button>
                        </form>

                    <?php break; case 'xor': ?>
                        <h2 class="mb-4">XOR Cipher (dengan Bin2Hex)</h2>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Teks (Gunakan format Hex untuk Dekripsi):</label>
                                <textarea name="input_text" class="form-control" rows="4" placeholder="Masukkan teks biasa (untuk enkripsi) atau teks heksadesimal (untuk dekripsi)..." required><?= htmlspecialchars($input_text) ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Key (Karakter String):</label>
                                    <input type="text" name="key" class="form-control" value="<?= htmlspecialchars($key) ?>" placeholder="Contoh: rahasia" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Mode:</label>
                                    <select name="action" class="form-select">
                                        <option value="encrypt" <?= $action == 'encrypt' ? 'selected' : '' ?>>Enkripsi (Output Hex)</option>
                                        <option value="decrypt" <?= $action == 'decrypt' ? 'selected' : '' ?>>Dekripsi (Input Hex)</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2">Proses XOR</button>
                        </form>

                    <?php break; case 'sha256': ?>
                        <h2 class="mb-4">SHA-256 Hashing Generator</h2>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Teks Input:</label>
                                <textarea name="input_text" class="form-control" rows="4" placeholder="Masukkan teks yang ingin di-hash..." required><?= htmlspecialchars($input_text) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 py-2">Generate Hash (SHA-256)</button>
                        </form>

                    <?php break; endswitch; ?>

                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page != 'home'): ?>
                        <div class="mt-5 p-4 <?= strpos($result, '⚠️ Error') !== false ? 'bg-danger text-white' : 'bg-light border' ?> rounded shadow-sm">
                            <h5 class="fw-bold mb-3">Output Hasil:</h5>
                            <textarea class="form-control font-monospace" rows="4" readonly><?= htmlspecialchars($result) ?></textarea>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</body>
</html>
