<?php

include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombreNuevoUsuario"];
    $correo = $_POST["correoNuevoUsuario"];
    $telefono = $_POST["telefonoNuevoUsuario"];
    $documento = $_POST["documentoNuevoUsuario"];
    $rol = $_POST["rolNuevoUsuario"];
    $estado = $_POST["estadoNuevoUsuario"];
    $password = $_POST["passwordNuevoUsuario"];

    $password_segura = password_hash($password, PASSWORD_DEFAULT);

    $verificar = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $verificar->bind_param("s", $correo);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
        echo "
            <script>
                alert('El correo ya está registrado.');
                window.location.href = 'administrador.php';
            </script>
        ";
        exit();
    }

    $sql = $conexion->prepare("
        INSERT INTO usuarios 
        (nombre_completo, correo, telefono, documento, password, rol, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $sql->bind_param("sssssss", $nombre, $correo, $telefono, $documento, $password_segura, $rol, $estado);

    if ($sql->execute()) {
        echo "
            <script>
                alert('Cuenta registrada correctamente.');
                window.location.href = 'administrador.php';
            </script>
        ";
    } else {
        echo "
            <script>
                alert('Error al registrar la cuenta.');
                window.location.href = 'administrador.php';
            </script>
        ";
    }

    $sql->close();
    $conexion->close();

} else {
    header("Location: administrador.php");
    exit();
}

?>