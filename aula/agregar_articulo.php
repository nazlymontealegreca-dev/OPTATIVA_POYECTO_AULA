<?php

include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombreArticulo"];
    $categoria = $_POST["categoriaArticulo"];
    $codigo = $_POST["codigoArticulo"];
    $cantidad = $_POST["cantidadArticulo"];
    $estado = $_POST["estadoArticulo"];
    $ubicacion = $_POST["ubicacionArticulo"];
    $descripcion = $_POST["descripcionArticulo"];

    /*
        Convertimos el valor de categoría del formulario
        a un nombre bonito para guardarlo o buscarlo.
    */
    $categorias = [
        "pelota" => "Pelota",
        "raqueta" => "Raqueta",
        "uniforme" => "Uniforme",
        "proteccion" => "Protección",
        "fitness" => "Fitness",
        "redes" => "Porterías y Redes",
        "calzado" => "Calzado"
    ];

    $nombre_categoria = isset($categorias[$categoria]) ? $categorias[$categoria] : $categoria;

    // Verificar si la categoría ya existe
    $buscar_categoria = $conexion->prepare("SELECT id_categoria FROM categorias WHERE nombre_categoria = ?");
    $buscar_categoria->bind_param("s", $nombre_categoria);
    $buscar_categoria->execute();
    $resultado_categoria = $buscar_categoria->get_result();

    if ($resultado_categoria->num_rows > 0) {
        $fila_categoria = $resultado_categoria->fetch_assoc();
        $id_categoria = $fila_categoria["id_categoria"];
    } else {
        // Si no existe, se crea automáticamente
        $crear_categoria = $conexion->prepare("
            INSERT INTO categorias (nombre_categoria, descripcion, estado)
            VALUES (?, 'Categoría registrada desde inventario', 'activa')
        ");

        $crear_categoria->bind_param("s", $nombre_categoria);
        $crear_categoria->execute();

        $id_categoria = $conexion->insert_id;
    }

    // Verificar si ya existe el código del implemento
    $verificar_codigo = $conexion->prepare("SELECT id_implemento FROM implementos WHERE codigo_implemento = ?");
    $verificar_codigo->bind_param("s", $codigo);
    $verificar_codigo->execute();
    $resultado_codigo = $verificar_codigo->get_result();

    if ($resultado_codigo->num_rows > 0) {
        echo "
            <script>
                alert('Ya existe un implemento registrado con ese código.');
                window.location.href = 'index.html';
            </script>
        ";
        exit();
    }

    /*
        Si el artículo se registra como disponible,
        la cantidad disponible será igual a la cantidad total.

        Si se registra como prestado o mantenimiento,
        la cantidad disponible queda en 0.
    */
    if ($estado == "disponible") {
        $cantidad_disponible = $cantidad;
    } else {
        $cantidad_disponible = 0;
    }

    $sql = $conexion->prepare("
        INSERT INTO implementos
        (id_categoria, nombre_implemento, codigo_implemento, descripcion, cantidad_total, cantidad_disponible, estado, ubicacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $sql->bind_param(
        "isssiiss",
        $id_categoria,
        $nombre,
        $codigo,
        $descripcion,
        $cantidad,
        $cantidad_disponible,
        $estado,
        $ubicacion
    );

    if ($sql->execute()) {
        echo "
            <script>
                alert('Implemento registrado correctamente.');
                window.location.href = 'index.php';
            </script>
        ";
    } else {
        echo "
            <script>
                alert('Error al registrar el implemento.');
                window.location.href = 'index.php';
            </script>
        ";
    }

    $sql->close();
    $conexion->close();

} else {
    header("Location: index.php");
    exit();
}

?>