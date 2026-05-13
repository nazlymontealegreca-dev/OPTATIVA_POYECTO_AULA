<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "administrador") {
    header("Location: login.html");
    exit();
}

$id_administrador = $_SESSION["id_usuario"];

$consulta_usuarios = $conexion->query("
    SELECT nombre_completo, correo, rol, estado 
    FROM usuarios 
    ORDER BY id_usuario DESC
");

/* Conteo total de implementos */
$consulta_total_implementos = $conexion->query("
    SELECT SUM(cantidad_total) AS total 
    FROM implementos
");

$total_implementos = $consulta_total_implementos->fetch_assoc();


/* Préstamos activos */
$consulta_prestamos_activos = $conexion->query("
    SELECT COUNT(*) AS total 
    FROM prestamos 
    WHERE estado = 'activo'
");

$prestamos_activos = $consulta_prestamos_activos->fetch_assoc();


/* Préstamos atrasados */
$consulta_atrasados = $conexion->query("
    SELECT COUNT(*) AS total 
    FROM prestamos 
    WHERE estado = 'activo'
    AND fecha_devolucion < CURDATE()
");

$prestamos_atrasados = $consulta_atrasados->fetch_assoc();


/* Implementos en mantenimiento */
$consulta_mantenimiento = $conexion->query("
    SELECT COUNT(*) AS total 
    FROM implementos 
    WHERE estado = 'mantenimiento'
");

$implementos_mantenimiento = $consulta_mantenimiento->fetch_assoc();

$usuarios_prestamo = $conexion->query("
    SELECT id_usuario, nombre_completo, documento 
    FROM usuarios 
    WHERE rol = 'usuario' AND estado = 'activo'
    ORDER BY nombre_completo ASC
");

$implementos_prestamo = $conexion->query("
    SELECT 
        i.id_implemento,
        i.nombre_implemento,
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

    <title>Administrador FET</title>

    <meta name="description" content="Panel administrativo para el sistema de inventario y préstamos de implementos deportivos de la Universidad FET">
    <meta name="keywords" content="inventario, préstamos, implementos deportivos, universidad FET, administrador">
    <meta name="author" content="Nazly Michel - Montealegre">

    <link rel="shortcut icon" href="img/icon.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="defaul.css">
    <script src="https://kit.fontawesome.com/9d1a86738f.js" crossorigin="anonymous"></script>
</head>

<body class="admin-page">

    <div class="admin-contenedor">

        <!-- MENÚ LATERAL -->
        <aside class="admin-menu">

            <div class="admin-logo">
                <div class="admin-logo-icon">
                    <i class="fa-solid fa-cube"></i>
                </div>

                <div>
                    <h2>Deportes Hub</h2>
                    <p>Sistema de Inventario</p>
                </div>
            </div>

            <nav class="admin-nav">

                <a href="#" class="admin-link activo" id="btnMenuPanel">
                    <i class="fa-solid fa-table-cells-large"></i>
                    <span>Panel Principal</span>
                </a>

                <a href="index.php" class="admin-link">
                    <i class="fa-solid fa-cube"></i>
                    <span>Equipamiento</span>
                </a>

                <a href="#" class="admin-link" id="btnMenuUsuarios">
                    <i class="fa-solid fa-users"></i>
                    <span>Usuarios</span>
                </a>

                <a href="#" class="admin-link" id="btnMenuAnalisis">
                    <i class="fa-solid fa-chart-column"></i>
                    <span>Análisis</span>
                </a>

            </nav>

            <div class="admin-user">
                <div class="admin-user-avatar">JD</div>

                <div>
                    <h4>Juan Díaz</h4>
                    <p>Administrador</p>
                </div>
            </div>

        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="admin-main">

            <!-- HEADER SUPERIOR -->
            <section class="admin-header">

                <div>
                    <h1>Panel Principal</h1>
                    <p>Bienvenido de nuevo, aquí está tu resumen de inventario</p>
                </div>

                <div class="admin-actions">
                    <div class="admin-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Buscar equipamiento...">
                    </div>

                    <button class="admin-bell">
                        <i class="fa-regular fa-bell"></i>
                        <span></span>
                    </button>

                    <button class="btn-nuevo-prestamo" id="btnMostrarPrestamo" type="button">
                        <i class="fa-solid fa-plus"></i>
                        Nuevo Préstamo
                    </button>
                </div>

            </section>

            <!-- FORMULARIO NUEVO PRÉSTAMO -->
            <section class="admin-prestamo-panel" id="nuevo-prestamo">

                <article class="admin-prestamo-card">

                    <div class="prestamo-title">
                        <div>
                            <h2>Nuevo Préstamo</h2>
                            <p>Registra el préstamo de un implemento deportivo a un usuario</p>
                        </div>

                        <div class="prestamo-title-actions">
                            <button class="btn-volver-panel" id="btnVolverPanel" type="button">
                                <i class="fa-solid fa-arrow-left"></i>
                                Volver al Panel
                            </button>

                            <div class="prestamo-title-icon">
                                <i class="fa-solid fa-clipboard-list"></i>
                            </div>
                        </div>
                    </div>

                    <form class="form-nuevo-prestamo" action="registrar_prestamo.php" method="post">

                        <div class="grupo-prestamo-form">
                            <label for="usuarioPrestamo">Usuario / Estudiante</label>
                            <div class="input-prestamo-icono">
                                <i class="fa-solid fa-user"></i>
                                <select id="usuarioPrestamo" name="usuarioPrestamo" required>
                                    <option value="">Selecciona un usuario</option>

                                    <?php while($usuarioPrestamo = $usuarios_prestamo->fetch_assoc()) { ?>
                                        <option value="<?php echo $usuarioPrestamo['id_usuario']; ?>">
                                            <?php echo $usuarioPrestamo['nombre_completo']; ?> -
                                            <?php echo $usuarioPrestamo['documento'] ? $usuarioPrestamo['documento'] : 'Sin documento'; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                             </div>
                        </div>

                        <div class="grupo-prestamo-form">
                            <label for="articuloPrestamo">Implemento Deportivo</label>
                            <div class="input-prestamo-icono">
                                <i class="fa-solid fa-cube"></i>
                                <select id="articuloPrestamo" name="articuloPrestamo" required>
                                    <option value="">Selecciona un implemento</option>

                                    <?php while($implementoPrestamo = $implementos_prestamo->fetch_assoc()) { ?>
                                        <option value="<?php echo $implementoPrestamo['id_implemento']; ?>">
                                            <?php echo $implementoPrestamo['nombre_implemento']; ?> -
                                            <?php echo $implementoPrestamo['nombre_categoria']; ?> -
                                            Disponibles: <?php echo $implementoPrestamo['cantidad_disponible']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="grupo-prestamo-form">
                            <label for="cantidadPrestamo">Cantidad</label>
                            <div class="input-prestamo-icono">
                                <i class="fa-solid fa-hashtag"></i>
                                <input 
                                    type="number"
                                    id="cantidadPrestamo"
                                    name="cantidadPrestamo"
                                    placeholder="Cantidad"
                                    min="1"
                                    max="50"
                                    required>
                            </div>
                        </div>

                        <div class="grupo-prestamo-form">
                            <label for="fechaPrestamo">Fecha de Préstamo</label>
                            <div class="input-prestamo-icono">
                                <i class="fa-solid fa-calendar-day"></i>
                                <input 
                                    type="date"
                                    id="fechaPrestamo"
                                    name="fechaPrestamo"
                                    required>
                            </div>
                        </div>

                        <div class="grupo-prestamo-form">
                            <label for="fechaDevolucion">Fecha de Devolución</label>
                            <div class="input-prestamo-icono">
                                <i class="fa-solid fa-calendar-check"></i>
                                <input 
                                    type="date"
                                    id="fechaDevolucion"
                                    name="fechaDevolucion"
                                    required>
                            </div>
                        </div>

                        <div class="grupo-prestamo-form prestamo-observaciones">
                            <label for="observacionesPrestamo">Observaciones</label>
                            <div class="textarea-prestamo">
                                <i class="fa-solid fa-comment-dots"></i>
                                <textarea 
                                    id="observacionesPrestamo"
                                    name="observacionesPrestamo"
                                    placeholder="Ejemplo: artículo entregado en buen estado, préstamo para entrenamiento, etc."></textarea>
                            </div>
                        </div>

                        <div class="prestamo-resumen">
                            <div>
                                <i class="fa-solid fa-circle-info"></i>
                                <p>
                                    Antes de registrar el préstamo, verifica que el implemento esté disponible
                                    y que el usuario no tenga préstamos vencidos.
                                </p>
                            </div>
                        </div>

                        <button class="btn-registrar-prestamo" type="submit">
                            <i class="fa-solid fa-check"></i>
                            Registrar Préstamo
                        </button>

                    </form>

                </article>

            </section>

            <!-- VISTA PANEL PRINCIPAL -->
            <div id="vistaDashboard">

                <!-- CARDS RESUMEN -->
                <section class="admin-cards">

                    <article class="admin-card">
                        <div>
                            <p>Equipamiento Total</p>
                            <h2><?php echo $total_implementos["total"] ? $total_implementos["total"] : 0; ?></h2>
                            <span class="positivo">↑ 12% <small>vs mes anterior</small></span>
                        </div>

                        <div class="card-icon morado">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                    </article>

                    <article class="admin-card">
                        <div>
                            <p>Préstamos Activos</p>
                            <h2>342</h2>
                            <span class="positivo">↑ 8% <small>vs mes anterior</small></span>
                        </div>

                        <div class="card-icon rosa">
                            <i class="fa-regular fa-clipboard"></i>
                        </div>
                    </article>

                    <article class="admin-card">
                        <div>
                            <p>Artículos Atrasados</p>
                            <h2><?php echo $prestamos_atrasados["total"]; ?></h2>
                            <span class="negativo">↓ 5% <small>vs mes anterior</small></span>
                        </div>

                        <div class="card-icon rojo-claro">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                    </article>

                    <article class="admin-card">
                        <div>
                            <p>Solicitudes de Mantenimiento</p>
                            <h2><?php echo $implementos_mantenimiento["total"]; ?></h2>
                            <span class="negativo">↓ 3% <small>vs mes anterior</small></span>
                        </div>

                        <div class="card-icon morado">
                            <i class="fa-solid fa-wrench"></i>
                        </div>
                    </article>

                </section>

                <!-- PERFIL Y GESTIÓN DE USUARIOS -->
                <section class="admin-usuarios-panel">

                    <!-- PERFIL ADMINISTRADOR -->
                    <article class="admin-perfil-card">

                        <div class="admin-perfil-header">
                            <div class="admin-perfil-avatar">JD</div>

                            <div>
                                <h2>Juan Díaz</h2>
                                <p>Administrador Principal</p>
                            </div>
                        </div>

                        <div class="admin-perfil-info">

                            <div class="perfil-info-item">
                                <i class="fa-solid fa-envelope"></i>
                                <div>
                                    <span>Correo</span>
                                    <p>juan.diaz@fet.edu.co</p>
                                </div>
                            </div>

                            <div class="perfil-info-item">
                                <i class="fa-solid fa-phone"></i>
                                <div>
                                    <span>Teléfono</span>
                                    <p>+57 300 123 4567</p>
                                </div>
                            </div>

                            <div class="perfil-info-item">
                                <i class="fa-solid fa-id-card"></i>
                                <div>
                                    <span>ID Administrador</span>
                                    <p>ADM-2026-001</p>
                                </div>
                            </div>

                        </div>

                        <div class="admin-perfil-stats">
                            <div>
                                <h2><?php echo $prestamos_activos["total"]; ?></h2>
                                <p>Préstamos Gestionados</p>
                            </div>

                            <div>
                                <h3>1,248</h3>
                                <p>Artículos Registrados</p>
                            </div>

                            <div>
                                <h3>18</h3>
                                <p>Reportes Generados</p>
                            </div>
                        </div>

                        <div class="admin-perfil-botones">
                            <button class="btn-editar-admin">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Editar Perfil
                            </button>

                            <a href="cerrar_sesion.php" class="btn-cerrar-sesion">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Cerrar Sesión
                            </a>
                        </div>

                    </article>

                    <!-- CREAR USUARIO O ADMINISTRADOR -->
                    <article class="admin-crear-usuario-card">

                        <div class="crear-usuario-title">
                            <div>
                                <h2>Gestión de Usuarios</h2>
                                <p>Registra usuarios o nuevos administradores del sistema</p>
                            </div>

                            <div class="crear-usuario-icon">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                        </div>

                        <div class="admin-lista-usuarios">

                            <h3>Usuarios registrados</h3>

                            <table>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php while($fila = $consulta_usuarios->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo $fila["nombre_completo"]; ?></td>
                                            <td><?php echo $fila["correo"]; ?></td>
                                            <td><?php echo ucfirst($fila["rol"]); ?></td>
                                            <td>
                                                <span class="usuario-activo">
                                                    <?php echo ucfirst($fila["estado"]); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                        </div>

                        <form class="form-crear-usuario" action="crear_usuario_admin.php" method="post">

                            <div class="grupo-admin-form">
                                <label for="nombreNuevoUsuario">Nombre completo</label>
                                <div class="input-admin-icono">
                                    <i class="fa-solid fa-user"></i>
                                    <input 
                                        type="text" 
                                        id="nombreNuevoUsuario" 
                                        name="nombreNuevoUsuario"
                                        placeholder="Nombre y apellido"
                                        maxlength="60"
                                        required>
                                </div>
                            </div>

                            <div class="grupo-admin-form">
                                <label for="correoNuevoUsuario">Correo institucional</label>
                                <div class="input-admin-icono">
                                    <i class="fa-solid fa-envelope"></i>
                                    <input 
                                        type="email" 
                                        id="correoNuevoUsuario" 
                                        name="correoNuevoUsuario"
                                        placeholder="usuario@fet.edu.co"
                                        maxlength="50"
                                        required>
                                </div>
                            </div>

                            <div class="grupo-admin-form">
                                <label for="telefonoNuevoUsuario">Teléfono</label>
                                <div class="input-admin-icono">
                                    <i class="fa-solid fa-phone"></i>
                                    <input 
                                        type="tel" 
                                        id="telefonoNuevoUsuario" 
                                        name="telefonoNuevoUsuario"
                                        placeholder="+57 300 123 4567"
                                        maxlength="20"
                                        required>
                                </div>
                            </div>
                            
                            <div class="grupo-admin-form">
                                <label for="documentoNuevoUsuario">Documento</label>
                                <div class="input-admin-icono">
                                    <i class="fa-solid fa-id-card"></i>
                                    <input 
                                        type="text" 
                                        id="documentoNuevoUsuario" 
                                        name="documentoNuevoUsuario"
                                        placeholder="Número de documento"
                                        maxlength="30"
                                        required>
                                </div>
                            </div>

                            <div class="grupo-admin-form">
                                <label for="rolNuevoUsuario">Tipo de cuenta</label>
                                <div class="input-admin-icono">
                                    <i class="fa-solid fa-user-shield"></i>
                                    <select id="rolNuevoUsuario" name="rolNuevoUsuario" required>
                                        <option value="">Selecciona el tipo de cuenta</option>
                                        <option value="usuario">Usuario / Estudiante</option>
                                        <option value="administrador">Administrador</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grupo-admin-form">
                                <label for="estadoNuevoUsuario">Estado</label>
                                <div class="input-admin-icono">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <select id="estadoNuevoUsuario" name="estadoNuevoUsuario" required>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grupo-admin-form">
                                <label for="passwordNuevoUsuario">Contraseña temporal</label>
                                <div class="input-admin-icono">
                                    <i class="fa-solid fa-lock"></i>
                                    <input 
                                        type="password" 
                                        id="passwordNuevoUsuario" 
                                        name="passwordNuevoUsuario"
                                        placeholder="Mínimo 8 caracteres"
                                        minlength="8"
                                        maxlength="25"
                                        required>
                                </div>
                            </div>

                            <button class="btn-crear-usuario" type="submit">
                                <i class="fa-solid fa-user-plus"></i>
                                Registrar Cuenta
                            </button>

                        </form>

                    </article>

                </section>

                <!-- ACCESOS RÁPIDOS -->
                <!-- ACCESOS RÁPIDOS -->
                <section class="admin-accesos-rapidos">

                    <button class="admin-acceso-card" id="btnAccesoPrestamo" type="button">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <h3>Nuevo Préstamo</h3>
                        <p>Registrar préstamo deportivo</p>
                    </button>

                    <button class="admin-acceso-card" id="btnCategoriasAdmin" type="button">
                        <i class="fa-solid fa-layer-group"></i>
                        <h3>Categorías</h3>
                        <p>Resumen por tipo de implemento</p>
                    </button>

                </section>
                <!-- ZONA CENTRAL -->
                <section class="admin-grid">

                    <!-- GRÁFICA -->
                    <article class="admin-panel grafica-panel">

                        <div class="panel-title">
                            <div>
                                <h3>Vista General de Préstamos</h3>
                                <p>Tendencias mensuales de préstamos y devoluciones</p>
                            </div>
                        </div>

                        <div class="grafica-simulada">
                            <div class="eje-y">
                                <span>120</span>
                                <span>90</span>
                                <span>60</span>
                                <span>30</span>
                                <span>0</span>
                            </div>

                            <div class="grafica-area" id="graficaAreaAdmin">
                                <div class="grid-linea"></div>
                                <div class="grid-linea"></div>
                                <div class="grid-linea"></div>
                                <div class="grid-linea"></div>

                                <div class="marcador-vertical" id="marcadorVertical"></div>

                                <div class="tooltip-grafica" id="tooltipGrafica">
                                    <h4 id="tooltipMes">Abr</h4>
                                    <p class="tooltip-prestamos">Préstamos : <span id="tooltipPrestamos">58</span></p>
                                    <p class="tooltip-devoluciones">Devoluciones : <span id="tooltipDevoluciones">59</span></p>
                                </div>

                                <svg viewBox="0 0 700 240" preserveAspectRatio="none">
                                    <polyline points="0,170 70,155 140,130 210,135 280,105 350,115 420,78 490,90 560,65 630,62 700,40"
                                        fill="none" stroke="#d98cf0" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />

                                    <polyline points="0,180 70,165 140,145 210,135 280,120 350,110 420,105 490,88 560,78 630,70 700,55"
                                        fill="none" stroke="#ed8fcb" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <button class="punto-mes" style="--x:0%; --y1:170px; --y2:180px;" data-mes="Ene" data-prestamos="45" data-devoluciones="42"></button>
                                <button class="punto-mes" style="--x:10%; --y1:155px; --y2:165px;" data-mes="Feb" data-prestamos="52" data-devoluciones="48"></button>
                                <button class="punto-mes" style="--x:20%; --y1:130px; --y2:145px;" data-mes="Mar" data-prestamos="61" data-devoluciones="55"></button>
                                <button class="punto-mes" style="--x:30%; --y1:135px; --y2:135px;" data-mes="Abr" data-prestamos="58" data-devoluciones="59"></button>
                                <button class="punto-mes" style="--x:40%; --y1:105px; --y2:120px;" data-mes="May" data-prestamos="72" data-devoluciones="65"></button>
                                <button class="punto-mes" style="--x:50%; --y1:115px; --y2:110px;" data-mes="Jun" data-prestamos="69" data-devoluciones="68"></button>
                                <button class="punto-mes" style="--x:60%; --y1:78px; --y2:105px;" data-mes="Jul" data-prestamos="85" data-devoluciones="72"></button>
                                <button class="punto-mes" style="--x:70%; --y1:90px; --y2:88px;" data-mes="Ago" data-prestamos="79" data-devoluciones="80"></button>
                                <button class="punto-mes" style="--x:80%; --y1:65px; --y2:78px;" data-mes="Sep" data-prestamos="91" data-devoluciones="85"></button>
                                <button class="punto-mes" style="--x:90%; --y1:62px; --y2:70px;" data-mes="Oct" data-prestamos="88" data-devoluciones="89"></button>
                                <button class="punto-mes" style="--x:95%; --y1:48px; --y2:60px;" data-mes="Nov" data-prestamos="96" data-devoluciones="91"></button>
                                <button class="punto-mes" style="--x:100%; --y1:40px; --y2:55px;" data-mes="Dic" data-prestamos="102" data-devoluciones="96"></button>

                                <div class="meses">
                                    <span>Ene</span>
                                    <span>Feb</span>
                                    <span>Mar</span>
                                    <span>Abr</span>
                                    <span>May</span>
                                    <span>Jun</span>
                                    <span>Jul</span>
                                    <span>Ago</span>
                                    <span>Sep</span>
                                    <span>Oct</span>
                                    <span>Nov</span>
                                    <span>Dic</span>
                                </div>
                            </div>
                        </div>

                        <div class="leyenda">
                            <span><b class="dot morado-dot"></b> Préstamos</span>
                            <span><b class="dot rosa-dot"></b> Devoluciones</span>
                        </div>

                    </article>

                    <!-- ACTIVIDAD -->
                    <article class="admin-panel actividad-panel">

                        <div class="panel-title actividad-title">
                            <div>
                                <h3>Actividad Reciente</h3>
                                <p>Últimas actualizaciones y transacciones</p>
                            </div>

                            <a href="#">Ver Todo</a>
                        </div>

                        <div class="actividad-lista">

                            <div class="actividad-item">
                                <div class="actividad-icon morado">
                                    <i class="fa-solid fa-arrow-trend-up"></i>
                                </div>

                                <div>
                                    <h4>Nuevo Préstamo Creado</h4>
                                    <p>Baloncesto (ID: BB-0452) prestado a Sarah J...</p>
                                    <small><i class="fa-regular fa-clock"></i> hace 5 minutos</small>
                                </div>
                            </div>

                            <div class="actividad-item">
                                <div class="actividad-icon rosa">
                                    <i class="fa-regular fa-circle-check"></i>
                                </div>

                                <div>
                                    <h4>Equipo Devuelto</h4>
                                    <p>Raqueta de Tenis (ID: TR-0123) devuelta por...</p>
                                    <small><i class="fa-regular fa-clock"></i> hace 23 minutos</small>
                                </div>
                            </div>

                            <div class="actividad-item">
                                <div class="actividad-icon rojo-claro">
                                    <i class="fa-solid fa-cube"></i>
                                </div>

                                <div>
                                    <h4>Solicitud de Mantenimiento</h4>
                                    <p>Portería de Fútbol (ID: SG-0089) requiere re...</p>
                                    <small><i class="fa-regular fa-clock"></i> hace 1 hora</small>
                                </div>
                            </div>

                            <div class="actividad-item">
                                <div class="actividad-icon morado">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                </div>

                                <div>
                                    <h4>Alerta de Retraso</h4>
                                    <p>Bate de Béisbol (ID: BB-0234) tiene 3 días d...</p>
                                    <small><i class="fa-regular fa-clock"></i> hace 2 horas</small>
                                </div>
                            </div>

                        </div>

                    </article>

                </section>

                <!-- EQUIPAMIENTO POR CATEGORIA -->
                <section class="admin-panel categorias-panel">

                    <div class="panel-title categorias-title">
                        <div>
                            <h3>Equipamiento por Categoría</h3>
                            <p>Distribución actual del inventario</p>
                        </div>

                        <button class="btn-filtrar">
                            <i class="fa-solid fa-filter"></i>
                            Filtrar
                        </button>
                    </div>

                    <div class="categorias-grid">

                        <article class="categoria-card">
                            <div class="categoria-top">
                                <div class="categoria-icon morado"><i class="fa-solid fa-cube"></i></div>
                                <div>
                                    <h4>Balones</h4>
                                    <p>342 artículos</p>
                                </div>
                            </div>

                            <div class="categoria-info">
                                <span>Disponible</span>
                                <strong>298</strong>
                            </div>

                            <div class="barra-progreso">
                                <span style="width: 87%;"></span>
                            </div>
                        </article>

                        <article class="categoria-card">
                            <div class="categoria-top">
                                <div class="categoria-icon rosa"><i class="fa-solid fa-cube"></i></div>
                                <div>
                                    <h4>Raquetas</h4>
                                    <p>156 artículos</p>
                                </div>
                            </div>

                            <div class="categoria-info">
                                <span>Disponible</span>
                                <strong>134</strong>
                            </div>

                            <div class="barra-progreso rosa-barra">
                                <span style="width: 85%;"></span>
                            </div>
                        </article>

                        <article class="categoria-card">
                            <div class="categoria-top">
                                <div class="categoria-icon rojo-claro"><i class="fa-solid fa-cube"></i></div>
                                <div>
                                    <h4>Equipo Protector</h4>
                                    <p>234 artículos</p>
                                </div>
                            </div>

                            <div class="categoria-info">
                                <span>Disponible</span>
                                <strong>189</strong>
                            </div>

                            <div class="barra-progreso rojo-barra">
                                <span style="width: 80%;"></span>
                            </div>
                        </article>

                        <article class="categoria-card">
                            <div class="categoria-top">
                                <div class="categoria-icon morado"><i class="fa-solid fa-cube"></i></div>
                                <div>
                                    <h4>Equipo de Fitness</h4>
                                    <p>198 artículos</p>
                                </div>
                            </div>

                            <div class="categoria-info">
                                <span>Disponible</span>
                                <strong>176</strong>
                            </div>

                            <div class="barra-progreso">
                                <span style="width: 88%;"></span>
                            </div>
                        </article>

                    </div>

                </section>

            </div>

        </main>

    </div>

    <script>
        /* ================= GRAFICA INTERACTIVA ================= */

        const puntosMes = document.querySelectorAll(".admin-page .punto-mes");
        const tooltipGrafica = document.getElementById("tooltipGrafica");
        const marcadorVertical = document.getElementById("marcadorVertical");
        const tooltipMes = document.getElementById("tooltipMes");
        const tooltipPrestamos = document.getElementById("tooltipPrestamos");
        const tooltipDevoluciones = document.getElementById("tooltipDevoluciones");
        const graficaAreaAdmin = document.getElementById("graficaAreaAdmin");

        if (puntosMes.length > 0 && tooltipGrafica && marcadorVertical && graficaAreaAdmin) {

            function posicionarTooltip(x) {
                const anchoGrafica = graficaAreaAdmin.clientWidth;
                const anchoTooltip = 180;
                let izquierda = x + 15;

                if (izquierda + anchoTooltip > anchoGrafica) {
                    izquierda = x - anchoTooltip - 15;
                }

                if (izquierda < 10) {
                    izquierda = 10;
                }

                tooltipGrafica.style.left = izquierda + "px";
            }

            puntosMes.forEach((punto) => {

                punto.addEventListener("mouseenter", () => {
                    const mes = punto.dataset.mes;
                    const prestamos = punto.dataset.prestamos;
                    const devoluciones = punto.dataset.devoluciones;
                    const x = punto.offsetLeft + (punto.offsetWidth / 2);

                    tooltipMes.textContent = mes;
                    tooltipPrestamos.textContent = prestamos;
                    tooltipDevoluciones.textContent = devoluciones;

                    marcadorVertical.style.left = x + "px";
                    marcadorVertical.classList.add("activo-linea");

                    posicionarTooltip(x);
                    tooltipGrafica.classList.add("activo-tooltip");
                });

                punto.addEventListener("mouseleave", () => {
                    marcadorVertical.classList.remove("activo-linea");
                    tooltipGrafica.classList.remove("activo-tooltip");
                });

            });
        }


        /* ================= VISTAS INTERNAS ADMIN ================= */

        const btnMostrarPrestamo = document.getElementById("btnMostrarPrestamo");
        const btnVolverPanel = document.getElementById("btnVolverPanel");
        const btnGestionUsuarios = document.getElementById("btnGestionUsuarios");
        const btnAccesoPrestamo = document.getElementById("btnAccesoPrestamo");
        const btnAnalisisAdmin = document.getElementById("btnAnalisisAdmin");
        const btnCategoriasAdmin = document.getElementById("btnCategoriasAdmin");

        const btnMenuPanel = document.getElementById("btnMenuPanel");
        const btnMenuUsuarios = document.getElementById("btnMenuUsuarios");
        const btnMenuAnalisis = document.getElementById("btnMenuAnalisis");
        const linksMenuAdmin = document.querySelectorAll(".admin-nav .admin-link");

        const adminCards = document.querySelector(".admin-cards");
        const usuariosPanel = document.querySelector(".admin-usuarios-panel");
        const perfilAdmin = document.querySelector(".admin-perfil-card");
        const crearUsuario = document.querySelector(".admin-crear-usuario-card");
        const accesosRapidos = document.querySelector(".admin-accesos-rapidos");
        const adminGrid = document.querySelector(".admin-grid");
        const categoriasPanel = document.querySelector(".categorias-panel");
        const panelPrestamo = document.getElementById("nuevo-prestamo");

        const adminHeaderTitulo = document.querySelector(".admin-header h1");
        const adminHeaderTexto = document.querySelector(".admin-header p");

        function subirArriba() {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }

        function cambiarTitulo(titulo, texto) {
            if (adminHeaderTitulo) adminHeaderTitulo.textContent = titulo;
            if (adminHeaderTexto) adminHeaderTexto.textContent = texto;
        }

        function activarLinkMenu(linkActivo) {
            linksMenuAdmin.forEach((link) => {
                link.classList.remove("activo");
            });

            if (linkActivo) {
                linkActivo.classList.add("activo");
            }
        }

        function agregarBotonVolver(contenedor) {
            if (contenedor && !contenedor.querySelector(".btn-volver-admin")) {
                const boton = document.createElement("button");
                boton.className = "btn-volver-admin";
                boton.type = "button";
                boton.innerHTML = `<i class="fa-solid fa-arrow-left"></i> Volver al Panel`;
                contenedor.prepend(boton);

                boton.addEventListener("click", () => {
                    mostrarResumenAdmin();
                    activarLinkMenu(btnMenuPanel);
                });
            }
        }

        function ocultarTodoAdmin() {
            if (adminCards) adminCards.style.display = "none";
            if (usuariosPanel) usuariosPanel.style.display = "none";
            if (perfilAdmin) perfilAdmin.style.display = "none";
            if (crearUsuario) crearUsuario.style.display = "none";
            if (accesosRapidos) accesosRapidos.style.display = "none";
            if (adminGrid) adminGrid.style.display = "none";
            if (categoriasPanel) categoriasPanel.style.display = "none";
            if (panelPrestamo) panelPrestamo.style.display = "none";
        }

        function mostrarResumenAdmin() {
            ocultarTodoAdmin();

            if (adminCards) adminCards.style.display = "grid";
            if (usuariosPanel) {
                usuariosPanel.style.display = "grid";
                usuariosPanel.classList.add("modo-resumen");
                usuariosPanel.classList.remove("modo-usuarios");
            }
            if (perfilAdmin) perfilAdmin.style.display = "block";
            if (accesosRapidos) accesosRapidos.style.display = "grid";

            cambiarTitulo("Panel Principal", "Bienvenido de nuevo, aquí está tu resumen de inventario");
            subirArriba();
        }

        function mostrarGestionUsuarios() {
            ocultarTodoAdmin();

            if (usuariosPanel) {
                usuariosPanel.style.display = "grid";
                usuariosPanel.classList.remove("modo-resumen");
                usuariosPanel.classList.add("modo-usuarios");
            }

            if (crearUsuario) {
                crearUsuario.style.display = "block";
                agregarBotonVolver(crearUsuario);
            }

            cambiarTitulo("Gestión de Usuarios", "Consulta usuarios registrados o crea nuevas cuentas");
            subirArriba();
        }

        function mostrarNuevoPrestamo() {
            ocultarTodoAdmin();

            if (panelPrestamo) {
                panelPrestamo.style.display = "block";
            }

            cambiarTitulo("Nuevo Préstamo", "Registra el préstamo de un implemento deportivo");
            subirArriba();
        }

        function mostrarAnalisis() {
            ocultarTodoAdmin();

            if (adminGrid) {
                adminGrid.style.display = "grid";
                agregarBotonVolver(adminGrid);
            }

            cambiarTitulo("Análisis del Sistema", "Consulta la gráfica y la actividad reciente");
            subirArriba();
        }

        function mostrarCategorias() {
            ocultarTodoAdmin();

            if (categoriasPanel) {
                categoriasPanel.style.display = "block";
                agregarBotonVolver(categoriasPanel);
            }

            cambiarTitulo("Categorías de Equipamiento", "Consulta la distribución actual del inventario");
            subirArriba();
        }

        if (btnMostrarPrestamo) {
            btnMostrarPrestamo.addEventListener("click", () => {
                mostrarNuevoPrestamo();
                activarLinkMenu(null);
            });
        }

        if (btnVolverPanel) {
            btnVolverPanel.addEventListener("click", () => {
                mostrarResumenAdmin();
                activarLinkMenu(btnMenuPanel);
            });
        }

        if (btnGestionUsuarios) {
            btnGestionUsuarios.addEventListener("click", () => {
                mostrarGestionUsuarios();
                activarLinkMenu(btnMenuUsuarios);
            });
        }

        if (btnAccesoPrestamo) {
            btnAccesoPrestamo.addEventListener("click", () => {
                mostrarNuevoPrestamo();
                activarLinkMenu(null);
            });
        }

        if (btnAnalisisAdmin) {
            btnAnalisisAdmin.addEventListener("click", () => {
                mostrarAnalisis();
                activarLinkMenu(btnMenuAnalisis);
            });
        }

        if (btnCategoriasAdmin) {
            btnCategoriasAdmin.addEventListener("click", () => {
                mostrarCategorias();
                activarLinkMenu(null);
            });
        }

        if (btnMenuPanel) {
            btnMenuPanel.addEventListener("click", (e) => {
                e.preventDefault();
                mostrarResumenAdmin();
                activarLinkMenu(btnMenuPanel);
            });
        }

        if (btnMenuUsuarios) {
            btnMenuUsuarios.addEventListener("click", (e) => {
                e.preventDefault();
                mostrarGestionUsuarios();
                activarLinkMenu(btnMenuUsuarios);
            });
        }

        if (btnMenuAnalisis) {
            btnMenuAnalisis.addEventListener("click", (e) => {
                e.preventDefault();
                mostrarAnalisis();
                activarLinkMenu(btnMenuAnalisis);
            });
        }

        mostrarResumenAdmin();
    </script>

</body>
</html>