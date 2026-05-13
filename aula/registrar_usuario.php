<?php

include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombreRegistro"];
    $correo = $_POST["correoRegistro"];
    $telefono = $_POST["telefonoRegistro"];
    $documento = $_POST["documentoRegistro"];
    $password = $_POST["passwordRegistro"];

    // El registro público siempre crea usuario normal
    $rol = "usuario";
    $estado = "activo";

    // Encriptar contraseña
    $password_segura = password_hash($password, PASSWORD_DEFAULT);

    // Verificar si el correo ya existe
    $verificar = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $verificar->bind_param("s", $correo);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
        echo "
            <script>
                alert('El correo ya está registrado.');
                window.location.href = 'login.html';
            </script>
        ";
        exit();
    }

    // Insertar usuario
    $sql = $conexion->prepare("
        INSERT INTO usuarios 
        (nombre_completo, correo, telefono, documento, password, rol, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $sql->bind_param("sssssss", $nombre, $correo, $telefono, $documento, $password_segura, $rol, $estado);

    if ($sql->execute()) {
        echo "
            <script>
                alert('Usuario registrado correctamente. Ahora puedes iniciar sesión.');
                window.location.href = 'login.html';
            </script>
        ";
    } else {
        echo "
            <script>
                alert('Error al registrar usuario.');
                window.location.href = 'login.html';
            </script>
        ";
    }

    $sql->close();
    $conexion->close();
}

?>