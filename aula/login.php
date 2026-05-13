<?php

session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = $_POST["correoLogin"];
    $password = $_POST["passwordLogin"];

    $sql = $conexion->prepare("
        SELECT id_usuario, nombre_completo, correo, password, rol, estado 
        FROM usuarios 
        WHERE correo = ?
    ");

    $sql->bind_param("s", $correo);
    $sql->execute();
    $resultado = $sql->get_result();

    if ($resultado->num_rows == 1) {

        $usuario = $resultado->fetch_assoc();

        if ($usuario["estado"] != "activo") {
            echo "
                <script>
                    alert('El usuario se encuentra inactivo.');
                    window.location.href = 'login.html';
                </script>
            ";
            exit();
        }

        if (password_verify($password, $usuario["password"])) {

            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["nombre_completo"] = $usuario["nombre_completo"];
            $_SESSION["correo"] = $usuario["correo"];
            $_SESSION["rol"] = $usuario["rol"];

            if ($usuario["rol"] == "administrador") {
                header("Location: administrador.php");
                exit();
            } else {
                header("Location: usuario.php");
                exit();
            }

        } else {
            echo "
                <script>
                    alert('Contraseña incorrecta.');
                    window.location.href = 'login.html';
                </script>
            ";
        }

    } else {
        echo "
            <script>
                alert('El correo no está registrado.');
                window.location.href = 'login.html';
            </script>
        ";
    }

    $sql->close();
    $conexion->close();
}

?>