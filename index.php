<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "galeria_imagenes";

$conex = new mysqli($servername, $username, $password, $dbname);

// Verificar si hay errores en la conexión
if ($conex->connect_error) {
    die("Error de conexión a la base de datos: " . $conex->connect_error);
}
// Subir imagen
if (isset($_POST['upload'])) {
    $targetDir = "imgsubidas/";
    $targetFile = $targetDir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Verificar si el archivo es una imagen válida
    $allowedTypes = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $allowedTypes)) {
        echo "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) { // Código que se ejecuta si el archivo se mueve correctamente a la ubicación de destino
            // Guardar información en la base de datos
            $nombre = basename($_FILES["image"]["name"]);
            $sql = "INSERT INTO imagenes (nombre, estado) VALUES ('$nombre', 'subido')";
            if ($conex->query($sql) === true) {
                echo "La imagen se ha subido correctamente.";
            } else {
                echo "Hubo un error al subir la imagen: ";
            }
        } else {
            echo "Hubo un error al subir la imagen.";
        }
    }
}

// Eliminar imagen
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Obtener el nombre del archivo
    $sql = "SELECT nombre FROM imagenes WHERE id = $id";
    $result = $conex->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filename = $row['nombre'];

        // Eliminar el archivo
        $filepath = "imgsubidas/" . $filename;
        if (unlink($filepath)) {
            // Eliminar el registro de la base de datos
            $sql = "DELETE FROM imagenes WHERE id = $id"; 
            if ($conex->query($sql) === true) {
                echo "La imagen se ha eliminado correctamente.";
            } else {
                echo "Hubo un error al eliminar la imagen ";
            }
        } else {
            echo "Hubo un error al eliminar la imagen.";
        }
    }
}

// Obtener todas las imágenes de la base de datos
$sql = "SELECT * FROM imagenes";
$result = $conex->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Galería de Imágenes</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h2>Galería de Imágenes</h2>

    <form action="index.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="image" />
        <input type="submit" name="upload" value="Subir" />
    </form>

    <div class="gallery">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
                $filename = $row['nombre'];
                $status = $row['estado'];
        ?>
                <div class="image">
                    <img src="imgsubidas/<?php echo $filename; ?>" alt="<?php echo $filename; ?>" />
                    <p><?php echo $status; ?></p>
                    <a href="index.php?delete=<?php echo $id; ?>">Eliminar</a>
                </div>
        <?php
            }
        } else {
            echo "No hay imágenes.";
        }
        ?>
    </div>
</body>
</html>
