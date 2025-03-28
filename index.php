<?php

$mappa = 'uploads/';
$allowed_types = ['jpg', 'png', 'pdf'];

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';

$files = scandir($mappa);
$filtered_files = [];
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (empty($filter) || strpos($file, $filter) !== false || in_array($ext, $allowed_types)) {
            $filtered_files[] = [
                'name' => $file,
                'modified' => date('Y-m-d H:i:s', filemtime($mappa . $file)),
                'type' => $ext
            ];
        }
    }
}

usort($filtered_files, function ($a, $b) use ($order) {
    return $order == 'asc' ? strcmp($a['name'], $b['name']) : strcmp($b['name'], $a['name']);
});

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fájlok listázása</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h1>Fájlok</h1>
    <form>
        <input type="text" name="filter" placeholder="Keresés...">
        <select name="order">
            <option value="asc">Név szerint növekvő</option>
            <option value="desc">Név szerint csökkenő</option>
        </select>
        <button type="submit">Szűrés</button><p><strong>FONTOS HOGY A MAPPA LETÖLTÉSE ÉS ANNAK TÍPUSA KÍÍRÁSA/LETÖLTÉSE NEM MŰKÖDIK MÉG!!!!!!
    </form>
    <table class="table">
        <thead>
        <tr>
            <th>Név</th>
            <th>Módosítva</th>
            <th>Típus</th>
            <th>Műveletek</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($filtered_files as $file): ?>
            <tr>
                <td><?php echo $file['name']; ?></td>
                <td><?php echo $file['modified']; ?></td>
                <td><?php echo $file['type']; ?></td>
                <td>
                    <a href="<?php echo $mappa . $file['name']; ?>" download>Letöltés</a>
                    </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<FELEZO------------------------------------------------------------------------------------------------------------------------------------------------------------------>
<meta http-equiv="refresh" content="30">
<?php
session_start();

$upload_dir = 'uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (isset($_POST['new_file']) && !empty($_POST['new_file'])) {
    $new_file = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '', $_POST['new_file']);
    $target_path = $upload_dir . $new_file;
    $create_success = false;

    if (substr($new_file, -6) === '.mappa') {
        $target_path = substr($target_path, 0, -6);
        if (!is_dir($target_path)) {
            if (mkdir($target_path, 0755, true)) {
                $_SESSION['create_message'] = "A(z) " . basename($target_path) . " mappa sikeresen létrehozva.";
                $create_success = true;
            } else {
                $_SESSION['create_message'] = "<span style='color: red;'>Hiba történt a mappa létrehozásakor.</span>";
            }
        } else {
            $_SESSION['create_message'] = "<span style='color: red;'>Ez a mappa már létezik.</span>";
        }
    } else {
        if (!file_exists($target_path)) {
            if (touch($target_path)) {
                $_SESSION['create_message'] = "A(z) $new_file fájl sikeresen létrehozva.";
                $create_success = true;
            } else {
                $_SESSION['create_message'] = "<span style='color: red;'>Hiba történt a fájl létrehozásakor.</span>";
            }
        } else {
            $_SESSION['create_message'] = "<span style='color: red;'>Ez a fájl már létezik.</span>";
        }
    }
    if ($create_success) {
      header("Location: " . $_SERVER['REQUEST_URI']);
      exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $errors = [];
    $upload_successful = false;

    foreach ($_FILES['files']['name'] as $key => $name) {
        $file_name = $_FILES['files']['name'][$key];
        $file_tmp = $_FILES['files']['tmp_name'][$key];
        $target_file = $upload_dir . basename($file_name); // Nincs mappastruktúra kezelés

        if (move_uploaded_file($file_tmp, $target_file)) {
            $upload_successful = true;
        } else {
            $errors[] = "Hiba történt a(z) $file_name fájl feltöltésekor.";
        }
    }

    if ($upload_successful && empty($errors)) {
        $_SESSION['upload_message'] = "A fájlok sikeresen feltöltve!";
    } else {
        if (!empty($errors)) {
            $_SESSION['error_message'] = "";
            foreach ($errors as $error) {
                $_SESSION['error_message'] .= "<span style='color: red;'>$error</span><br>";
            }
        } else {
            $_SESSION['upload_message'] = "Hiba történt a feltöltés során.";
        }
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}


if (isset($_SESSION['upload_message'])) {
    $upload_message = $_SESSION['upload_message'];
    unset($_SESSION['upload_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['create_message'])) {
    $create_message = $_SESSION['create_message'];
    unset($_SESSION['create_message']);
}
?>

<form action="" method="post" enctype="multipart/form-data">
    Fájlok kiválasztása:
    <input type="file" name="files[]" multiple><br><br>
    <input type="submit" value="Feltöltés">
    <?php if (isset($upload_message)) echo $upload_message; ?>
    <?php if (isset($error_message)) echo $error_message; ?>
</form>

