<?php

session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.html");
    exit();
}

if (isset($_GET["id"])) {

    $id_prestamo = $_GET["id"];
    $id_usuario = $_SESSION["id_usuario"];

    // Buscar el préstamo y su detalle
    $consulta = $conexion->prepare("
        SELECT 
            p.id_prestamo,
            p.id_usuario,
            dp.id_implemento,
            dp.cantidad,
            i.cantidad_disponible,
            i.cantidad_total
        FROM prestamos p
        INNER JOIN detalle_prestamo dp ON p.id_prestamo = dp.id_prestamo
        INNER JOIN implementos i ON dp.id_implemento = i.id_implemento
        WHERE p.id_prestamo = ?
        AND p.id_usuario = ?
        AND p.estado IN ('activo', 'vencido')
    ");

    $consulta->bind_param("ii", $id_prestamo, $id_usuario);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows == 0) {
        echo "
            <script>
                alert('No se encontró un préstamo activo para devolver.');
                window.location.href = 'usuario.php';
            </script>
        ";
        exit();
    }

    $prestamo = $resultado->fetch_assoc();

    $id_implemento = $prestamo["id_implemento"];
    $cantidad_prestada = $prestamo["cantidad"];
    $cantidad_disponible = $prestamo["cantidad_disponible"];
    $cantidad_total = $prestamo["cantidad_total"];

    // Calcular nueva cantidad disponible
    $nueva_cantidad = $cantidad_disponible + $cantidad_prestada;

    if ($nueva_cantidad > $cantidad_total) {
        $nueva_cantidad = $cantidad_total;
    }

    // Cambiar préstamo a devuelto
    $actualizar_prestamo = $conexion->prepare("
        UPDATE prestamos
        SET estado = 'devuelto'
        WHERE id_prestamo = ?
    ");

    $actualizar_prestamo->bind_param("i", $id_prestamo);
    $actualizar_prestamo->execute();

    // Cambiar detalle a devuelto
    $actualizar_detalle = $conexion->prepare("
        UPDATE detalle_prestamo
        SET estado_detalle = 'devuelto'
        WHERE id_prestamo = ?
    ");

    $actualizar_detalle->bind_param("i", $id_prestamo);
    $actualizar_detalle->execute();

    // Devolver cantidad al inventario
    $actualizar_implemento = $conexion->prepare("
        UPDATE implementos
        SET cantidad_disponible = ?, estado = 'disponible'
        WHERE id_implemento = ?
    ");

    $actualizar_implemento->bind_param("ii", $nueva_cantidad, $id_implemento);
    $actualizar_implemento->execute();

    echo "
        <script>
            alert('Préstamo devuelto correctamente.');
            window.location.href = 'usuario.php';
        </script>
    ";

} else {
    header("Location: usuario.php");
    exit();
}

?>