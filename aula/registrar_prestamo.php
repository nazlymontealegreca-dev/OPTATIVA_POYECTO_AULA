<?php

session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "administrador") {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_usuario = $_POST["usuarioPrestamo"];
    $id_implemento = $_POST["articuloPrestamo"];
    $cantidad = intval($_POST["cantidadPrestamo"]);
    $fecha_prestamo = $_POST["fechaPrestamo"];
    $fecha_devolucion = $_POST["fechaDevolucion"];
    $observaciones = $_POST["observacionesPrestamo"];

    $id_administrador = $_SESSION["id_usuario"];

    if ($cantidad <= 0) {
        echo "
            <script>
                alert('La cantidad debe ser mayor a cero.');
                window.location.href = 'administrador.php';
            </script>
        ";
        exit();
    }

    // Consultar disponibilidad del implemento
    $consulta = $conexion->prepare("
        SELECT cantidad_disponible 
        FROM implementos 
        WHERE id_implemento = ?
    ");

    $consulta->bind_param("i", $id_implemento);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows == 0) {
        echo "
            <script>
                alert('El implemento seleccionado no existe.');
                window.location.href = 'administrador.php';
            </script>
        ";
        exit();
    }

    $implemento = $resultado->fetch_assoc();
    $cantidad_disponible = intval($implemento["cantidad_disponible"]);

    if ($cantidad > $cantidad_disponible) {
        echo "
            <script>
                alert('No hay suficiente cantidad disponible para este préstamo.');
                window.location.href = 'administrador.php';
            </script>
        ";
        exit();
    }

    // Crear préstamo principal
    $sql_prestamo = $conexion->prepare("
        INSERT INTO prestamos 
        (id_usuario, id_administrador, fecha_prestamo, fecha_devolucion, estado, observaciones)
        VALUES (?, ?, ?, ?, 'activo', ?)
    ");

    $sql_prestamo->bind_param(
        "iisss",
        $id_usuario,
        $id_administrador,
        $fecha_prestamo,
        $fecha_devolucion,
        $observaciones
    );

    if ($sql_prestamo->execute()) {

        $id_prestamo = $conexion->insert_id;

        // Guardar detalle del préstamo
        $sql_detalle = $conexion->prepare("
            INSERT INTO detalle_prestamo
            (id_prestamo, id_implemento, cantidad, estado_detalle)
            VALUES (?, ?, ?, 'prestado')
        ");

        $sql_detalle->bind_param(
            "iii",
            $id_prestamo,
            $id_implemento,
            $cantidad
        );

        if ($sql_detalle->execute()) {

            // Actualizar cantidad disponible
            $nueva_cantidad = $cantidad_disponible - $cantidad;

            if ($nueva_cantidad > 0) {
                $nuevo_estado = "disponible";
            } else {
                $nuevo_estado = "prestado";
            }

            $actualizar = $conexion->prepare("
                UPDATE implementos
                SET cantidad_disponible = ?, estado = ?
                WHERE id_implemento = ?
            ");

            $actualizar->bind_param(
                "isi",
                $nueva_cantidad,
                $nuevo_estado,
                $id_implemento
            );

            $actualizar->execute();

            echo "
                <script>
                    alert('Préstamo registrado correctamente.');
                    window.location.href = 'administrador.php';
                </script>
            ";

        } else {
            echo "
                <script>
                    alert('Error al guardar el detalle del préstamo.');
                    window.location.href = 'administrador.php';
                </script>
            ";
        }

    } else {
        echo "
            <script>
                alert('Error al registrar el préstamo.');
                window.location.href = 'administrador.php';
            </script>
        ";
    }

    $conexion->close();

} else {
    header("Location: administrador.php");
    exit();
}

?>