<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.html");
    exit();
}

if ($_SESSION["rol"] != "usuario") {
    header("Location: administrador.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];

$sql = $conexion->prepare("
    SELECT nombre_completo, correo, telefono, documento, rol 
    FROM usuarios 
    WHERE id_usuario = ?
");

$sql->bind_param("i", $id_usuario);
$sql->execute();
$resultado = $sql->get_result();
$usuario = $resultado->fetch_assoc();

/* Préstamos activos o vencidos del usuario */
$prestamos_actuales = $conexion->prepare("
    SELECT 
        p.id_prestamo,
        p.fecha_prestamo,
        p.fecha_devolucion,
        p.estado,
        i.nombre_implemento,
        c.nombre_categoria,
        dp.cantidad
    FROM prestamos p
    INNER JOIN detalle_prestamo dp ON p.id_prestamo = dp.id_prestamo
    INNER JOIN implementos i ON dp.id_implemento = i.id_implemento
    INNER JOIN categorias c ON i.id_categoria = c.id_categoria
    WHERE p.id_usuario = ?
    AND p.estado IN ('activo', 'vencido')
    ORDER BY p.fecha_prestamo DESC
");

$prestamos_actuales->bind_param("i", $id_usuario);
$prestamos_actuales->execute();
$resultado_prestamos_actuales = $prestamos_actuales->get_result();

/* Historial completo del usuario */
$historial_prestamos = $conexion->prepare("
    SELECT 
        p.id_prestamo,
        p.fecha_prestamo,
        p.fecha_devolucion,
        p.estado,
        i.nombre_implemento,
        c.nombre_categoria,
        dp.cantidad
    FROM prestamos p
    INNER JOIN detalle_prestamo dp ON p.id_prestamo = dp.id_prestamo
    INNER JOIN implementos i ON dp.id_implemento = i.id_implemento
    INNER JOIN categorias c ON i.id_categoria = c.id_categoria
    WHERE p.id_usuario = ?
    ORDER BY p.fecha_prestamo DESC
");

$historial_prestamos->bind_param("i", $id_usuario);
$historial_prestamos->execute();
$resultado_historial = $historial_prestamos->get_result();

/* Contadores del perfil */
$total_activos = $conexion->prepare("
    SELECT COUNT(*) AS total 
    FROM prestamos 
    WHERE id_usuario = ? 
    AND estado IN ('activo', 'vencido')
");

$total_activos->bind_param("i", $id_usuario);
$total_activos->execute();
$res_activos = $total_activos->get_result()->fetch_assoc();

$total_historico = $conexion->prepare("
    SELECT COUNT(*) AS total 
    FROM prestamos 
    WHERE id_usuario = ?
");

$total_historico->bind_param("i", $id_usuario);
$total_historico->execute();
$res_historico = $total_historico->get_result()->fetch_assoc();

/* Catálogo deportivo disponible */
$catalogo_disponible = $conexion->query("
    SELECT 
        i.nombre_implemento,
        i.descripcion,
        i.cantidad_disponible,
        c.nombre_categoria
    FROM implementos i
    INNER JOIN categorias c ON i.id_categoria = c.id_categoria
    WHERE i.cantidad_disponible > 0
    AND i.estado = 'disponible'
    ORDER BY i.nombre_implemento ASC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Panel de Usuario FET</title>

    <meta name="description" content="Sistema de inventario con tickets para articulos deportivos">
    <meta name="keywords" content="Sistema, inventario, tickets, articulos, deportivos">
    <meta name="author" content="Nazly Michel - Montealegre">

    <link rel="shortcut icon" href="img/icon.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="defaul.css">
    <script src="https://kit.fontawesome.com/9d1a86738f.js" crossorigin="anonymous"></script>
</head>

<body class="usuario-page">

    <header class="header-principal">

        <button class="btn-menu" id="btnMenu" aria-label="Abrir menú">
            <i class="fa-solid fa-bars"></i>
        </button>

    </header>

    <div class="fondo-menu" id="fondoMenu"></div>

    <aside class="menu-lateral-usuario" id="menuLateral">

        <div class="menu-titulo">
            <div>
                <h2>Navegación</h2>
                <p>Implementos Deportivos</p>
            </div>

            <button class="btn-cerrar-menu" id="btnCerrarMenu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="opciones-menu">

            <a href="#" class="opcion-menu activa" id="btnPanelUsuario">
                <span><i class="fa-solid fa-house"></i></span>
                Mi Panel
            </a>

            <a href="#" class="opcion-menu" id="btnCatalogoUsuario">
                <span><i class="fa-solid fa-football"></i></span>
                Catálogo Deportivo
            </a>

            <a href="#prestamos" class="opcion-menu" id="btnPrestamosUsuario">
                <span><i class="fa-solid fa-clipboard-list"></i></span>
                Mis Préstamos
            </a>

            <a href="#historial" class="opcion-menu" id="btnHistorialUsuario">
                <span><i class="fa-solid fa-clock-rotate-left"></i></span>
                Historial
            </a>

            <a href="cerrar_sesion.php" class="opcion-menu cerrar-sesion-usuario">
                <span><i class="fa-solid fa-right-from-bracket"></i></span>
                Cerrar Sesión
            </a>

        </nav>

        <div class="usuario-menu">
            <div class="iniciales">MG</div>

            <div>
                <h4><?php echo $usuario["nombre_completo"]; ?></h4>
                <p>ID: <?php echo $usuario["documento"] ? $usuario["documento"] : "Sin documento"; ?></p>
                <small>Sistema de Préstamos v1.0</small>
            </div>
        </div>

    </aside>

    <main class="main-usuario">

        <section class="encabezado-usuario">
            <h2>Panel de Usuario</h2>
            <p>Gestiona tus préstamos y consulta tu historial</p>
        </section>

        <!-- VISTA PANEL USUARIO -->
        <div id="vistaPanelUsuario">

            <section class="contenedor-usuario">

                <article class="perfil-usuario">

                    <div class="contenedor-avatar">
                        <img src="img/usuario2.png" alt="Foto de usuario" class="foto-usuario">
                        <span class="icono-avatar">
                            <i class="fa-solid fa-address-card"></i>
                        </span>
                    </div>

                    <h3><?php echo $usuario["nombre_completo"]; ?></h3>

                    <p class="codigo-usuario">
                        <i class="fa-solid fa-address-card"></i>
                        ID: <?php echo $usuario["documento"] ? $usuario["documento"] : "Sin documento"; ?>
                    </p>
                    <div class="datos-usuario">

                        <div class="dato">
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <span>Correo Electrónico</span>
                                <p><?php echo $usuario["correo"]; ?></p>
                            </div>
                        </div>

                        <div class="dato">
                            <i class="fa-solid fa-phone"></i>
                            <div>
                                <span>Teléfono</span>
                                <p><?php echo $usuario["telefono"]; ?></p>
                            </div>
                        </div>

                        <div class="dato">
                            <i class="fa-solid fa-location-dot"></i>
                            <div>
                                <span>Ubicación</span>
                                <p>Neiva, Huila</p>
                            </div>
                        </div>

                    </div>

                    <div class="resumen-usuario">
                        <div>
                            <h4><?php echo $res_activos["total"]; ?></h4>
                            <p>Préstamos Activos</p>
                        </div>

                        <div>
                            <h4><?php echo $res_historico["total"]; ?></h4>
                            <p>Total Histórico</p>
                        </div>
                    </div>

                </article>

                <section class="contenido-usuario">

                    <article class="tarjeta-usuario" id="prestamos">

                        <div class="titulo-tarjeta">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <h3>Préstamos Actuales</h3>
                        </div>

                        <?php if ($resultado_prestamos_actuales->num_rows > 0) { ?>

                            <?php while($prestamo = $resultado_prestamos_actuales->fetch_assoc()) { ?>

                                <div class="prestamo">
                                    <div class="prestamo-superior">
                                        <div>
                                            <h4><?php echo $prestamo["nombre_implemento"]; ?></h4>
                                            <p><?php echo $prestamo["nombre_categoria"]; ?> | Cantidad: <?php echo $prestamo["cantidad"]; ?></p>
                                        </div>

                                        <span class="estado <?php echo $prestamo["estado"]; ?>">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <?php echo ucfirst($prestamo["estado"]); ?>
                                        </span>
                                    </div>

                                    <div class="prestamo-inferior">
                                        <div class="fechas-prestamo">
                                            <p>
                                                <span>Prestado</span>
                                                <?php echo date("d/m/Y", strtotime($prestamo["fecha_prestamo"])); ?>
                                            </p>

                                            <p>
                                                <span>Devolución</span>
                                                <?php echo date("d/m/Y", strtotime($prestamo["fecha_devolucion"])); ?>
                                            </p>
                                        </div>

                                        <a 
                                            href="devolver_prestamo.php?id=<?php echo $prestamo["id_prestamo"]; ?>" 
                                            class="btn-devolver"
                                            onclick="return confirm('¿Confirmas la devolución de este implemento?');">
                                            Devolver
                                        </a>
                                    </div>
                                </div>

                            <?php } ?>

                        <?php } else { ?>

                            <div class="prestamo">
                                <div class="prestamo-superior">
                                    <div>
                                        <h4>No tienes préstamos activos</h4>
                                        <p>Cuando el administrador registre un préstamo, aparecerá aquí.</p>
                                    </div>
                                </div>
                            </div>

                        <?php } ?>

                    </article>

                    <article class="tarjeta-usuario historial" id="historial">

                        <div class="titulo-tarjeta">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <h3>Historial de Préstamos</h3>
                        </div>

                        <table class="tabla-historial">
                            <thead>
                                <tr>
                                    <th>Implemento</th>
                                    <th>Categoría</th>
                                    <th>Fecha Préstamo</th>
                                    <th>Fecha Devolución</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($resultado_historial->num_rows > 0) { ?>

                                    <?php while($historial = $resultado_historial->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo $historial["nombre_implemento"]; ?></td>
                                            <td><?php echo $historial["nombre_categoria"]; ?></td>
                                            <td><?php echo date("d/m/Y", strtotime($historial["fecha_prestamo"])); ?></td>
                                            <td><?php echo date("d/m/Y", strtotime($historial["fecha_devolucion"])); ?></td>
                                            <td>
                                                <span class="estado <?php echo $historial["estado"]; ?>">
                                                    <?php echo ucfirst($historial["estado"]); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>
                                        <td colspan="5">No tienes registros de préstamos.</td>
                                    </tr>

                                <?php } ?>
                            </tbody>
                        </table>

                        <div class="pie-tabla">
                            <p>Historial de préstamos registrados</p>
                            <div>
                                <button>Anterior</button>
                                <button>Siguiente</button>
                            </div>
                        </div>

                    </article>

                </section>

            </section>

        </div>

        <!-- CATÁLOGO DEPORTIVO USUARIO -->
        <section class="catalogo-usuario" id="vistaCatalogoUsuario">

            <div class="catalogo-header">
                <div>
                    <h2>Catálogo Deportivo</h2>
                    <p>Consulta los implementos disponibles para préstamo</p>
                </div>

                <button class="btn-volver-usuario" id="btnVolverPanelUsuario" type="button">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver a Mi Panel
                </button>
            </div>

            <div class="catalogo-grid">

                <?php if ($catalogo_disponible->num_rows > 0) { ?>

                    <?php while($implemento = $catalogo_disponible->fetch_assoc()) { ?>

                        <article class="catalogo-card">

                            <div class="catalogo-icon">
                                <?php if ($implemento["nombre_categoria"] == "Pelota") { ?>
                                    <i class="fa-solid fa-futbol"></i>
                                <?php } elseif ($implemento["nombre_categoria"] == "Raqueta") { ?>
                                    <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                                <?php } elseif ($implemento["nombre_categoria"] == "Uniforme") { ?>
                                    <i class="fa-solid fa-shirt"></i>
                                <?php } elseif ($implemento["nombre_categoria"] == "Protección") { ?>
                                    <i class="fa-solid fa-hand-fist"></i>
                                <?php } elseif ($implemento["nombre_categoria"] == "Fitness") { ?>
                                    <i class="fa-solid fa-dumbbell"></i>
                                <?php } else { ?>
                                    <i class="fa-solid fa-cube"></i>
                                <?php } ?>
                            </div>

                            <h3><?php echo $implemento["nombre_implemento"]; ?></h3>

                            <p>
                                <?php 
                                    echo $implemento["descripcion"] 
                                    ? $implemento["descripcion"] 
                                    : "Implemento deportivo disponible para préstamo."; 
                                ?>
                            </p>

                            <div class="catalogo-info">
                                <span>Categoría: <?php echo $implemento["nombre_categoria"]; ?></span>
                                <strong>Disponibles: <?php echo $implemento["cantidad_disponible"]; ?></strong>
                            </div>

                        </article>

                    <?php } ?>

                <?php } else { ?>

                    <article class="catalogo-card">
                        <div class="catalogo-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>

                        <h3>No hay implementos disponibles</h3>
                        <p>En este momento no hay implementos deportivos disponibles para préstamo.</p>

                        <div class="catalogo-info">
                            <span>Consulta nuevamente más tarde</span>
                            <strong>Disponibles: 0</strong>
                        </div>
                    </article>

                <?php } ?>

            </div>

        </section>

    </main>

    <footer>
        <small>Nazly Montealegre &copy; 2026</small>
    </footer>

    <script>
        const btnMenu = document.getElementById("btnMenu");
        const btnCerrarMenu = document.getElementById("btnCerrarMenu");
        const menuLateral = document.getElementById("menuLateral");
        const fondoMenu = document.getElementById("fondoMenu");

        const btnPanelUsuario = document.getElementById("btnPanelUsuario");
        const btnCatalogoUsuario = document.getElementById("btnCatalogoUsuario");
        const btnVolverPanelUsuario = document.getElementById("btnVolverPanelUsuario");
        const btnPrestamosUsuario = document.getElementById("btnPrestamosUsuario");
        const btnHistorialUsuario = document.getElementById("btnHistorialUsuario");

        const vistaPanelUsuario = document.getElementById("vistaPanelUsuario");
        const vistaCatalogoUsuario = document.getElementById("vistaCatalogoUsuario");

        const opcionesUsuario = document.querySelectorAll(".opciones-menu .opcion-menu");

        function abrirMenuUsuario(){
            menuLateral.classList.add("menu-activo");
            fondoMenu.classList.add("fondo-activo");
        }

        function cerrarMenuUsuario(){
            menuLateral.classList.remove("menu-activo");
            fondoMenu.classList.remove("fondo-activo");
        }

        function activarOpcionUsuario(opcionActiva){
            opcionesUsuario.forEach((opcion) => {
                opcion.classList.remove("activa");
            });

            if(opcionActiva){
                opcionActiva.classList.add("activa");
            }
        }

        function mostrarPanelUsuario(){
            vistaCatalogoUsuario.style.display = "none";
            vistaPanelUsuario.style.display = "block";
            activarOpcionUsuario(btnPanelUsuario);
            cerrarMenuUsuario();

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }

        function mostrarCatalogoUsuario(){
            vistaPanelUsuario.style.display = "none";
            vistaCatalogoUsuario.style.display = "block";
            activarOpcionUsuario(btnCatalogoUsuario);
            cerrarMenuUsuario();

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }

        btnMenu.addEventListener("click", abrirMenuUsuario);
        btnCerrarMenu.addEventListener("click", cerrarMenuUsuario);
        fondoMenu.addEventListener("click", cerrarMenuUsuario);

        btnPanelUsuario.addEventListener("click", (e) => {
            e.preventDefault();
            mostrarPanelUsuario();
        });

        btnCatalogoUsuario.addEventListener("click", (e) => {
            e.preventDefault();
            mostrarCatalogoUsuario();
        });

        btnVolverPanelUsuario.addEventListener("click", mostrarPanelUsuario);

        btnPrestamosUsuario.addEventListener("click", () => {
            vistaCatalogoUsuario.style.display = "none";
            vistaPanelUsuario.style.display = "block";
            activarOpcionUsuario(btnPrestamosUsuario);
            cerrarMenuUsuario();
        });

        btnHistorialUsuario.addEventListener("click", () => {
            vistaCatalogoUsuario.style.display = "none";
            vistaPanelUsuario.style.display = "block";
            activarOpcionUsuario(btnHistorialUsuario);
            cerrarMenuUsuario();
        });
    </script>

</body>
</html>