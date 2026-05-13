<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "administrador") {
    header("Location: login.html");
    exit();
}

$registros_por_pagina = 10;

$pagina_actual = isset($_GET["pagina"]) ? (int)$_GET["pagina"] : 1;

if ($pagina_actual < 1) {
    $pagina_actual = 1;
}

$inicio = ($pagina_actual - 1) * $registros_por_pagina;

$total_consulta = $conexion->query("SELECT COUNT(*) AS total FROM implementos");
$total_fila = $total_consulta->fetch_assoc();
$total_implementos = $total_fila["total"];

$total_paginas = ceil($total_implementos / $registros_por_pagina);

$consulta_implementos = $conexion->query("
    SELECT 
        i.id_implemento,
        i.nombre_implemento,
        i.codigo_implemento,
        i.cantidad_total,
        i.cantidad_disponible,
        i.estado,
        i.ubicacion,
        c.nombre_categoria
    FROM implementos i
    INNER JOIN categorias c ON i.id_categoria = c.id_categoria
    ORDER BY i.id_implemento DESC
    LIMIT $inicio, $registros_por_pagina
");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Inventario FET</title>

    <meta name="description" content="Gestión de inventario de implementos deportivos para la Universidad FET">
    <meta name="keywords" content="inventario, deportes, implementos deportivos, préstamos, Universidad FET">
    <meta name="author" content="Nazly Michel - Montealegre">

    <link rel="shortcut icon" href="img/icon.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="defaul.css">
    <script src="https://kit.fontawesome.com/9d1a86738f.js" crossorigin="anonymous"></script>
</head>

<body class="inventario-page">

    <div class="inventario-contenedor">

        <!-- MENÚ LATERAL -->
        <aside class="inventario-menu">

            <div class="inventario-logo">
                <div class="inventario-logo-icon">
                    <i class="fa-solid fa-cube"></i>
                </div>

                <div>
                    <h2>Deportes Hub</h2>
                    <p>Sistema de Inventario</p>
                </div>
            </div>

            <nav class="inventario-nav">

                <a href="administrador.php" class="inventario-link">
                    <i class="fa-solid fa-table-cells-large"></i>
                    <span>Panel Principal</span>
                </a>

                <a href="index.php" class="inventario-link activo">
                    <i class="fa-solid fa-cube"></i>
                    <span>Equipamiento</span>
                </a>

            </nav>

            <div class="inventario-user">
                <div class="inventario-user-avatar">JD</div>

                <div>
                    <h4>Juan Díaz</h4>
                    <p>Administrador</p>
                </div>
            </div>

        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="inventario-main">

            <!-- CABECERA -->
            <section class="inventario-header">

                <div>
                    <h1>Gestión de Inventario</h1>
                    <p>Administra y controla todos los implementos deportivos</p>
                </div>

                <button class="btn-agregar-articulo" id="btnMostrarArticulo" type="button">
                    <i class="fa-solid fa-plus"></i>
                    Agregar Artículo
                </button>

            </section>

            <!-- FORMULARIO AGREGAR ARTÍCULO -->
            <section class="form-articulo-panel" id="formArticulo">

                <article class="form-articulo-card">

                    <div class="form-articulo-title">
                        <div>
                            <h2>Agregar Artículo</h2>
                            <p>Registra un nuevo implemento deportivo en el inventario</p>
                        </div>

                        <div class="form-articulo-actions">
                            <button class="btn-volver-inventario" id="btnVolverInventario" type="button">
                                <i class="fa-solid fa-arrow-left"></i>
                                Volver al Inventario
                            </button>

                            <div class="form-articulo-icon">
                                <i class="fa-solid fa-cube"></i>
                            </div>
                        </div>
                    </div>

                    <form class="form-agregar-articulo" action="agregar_articulo.php" method="post">

                        <div class="grupo-articulo-form">
                            <label for="nombreArticulo">Nombre del artículo</label>
                            <div class="input-articulo-icono">
                                <i class="fa-solid fa-cube"></i>
                                <input 
                                    type="text"
                                    id="nombreArticulo"
                                    name="nombreArticulo"
                                    placeholder="Ej: Balón de fútbol Adidas"
                                    maxlength="80"
                                    required>
                            </div>
                        </div>

                        <div class="grupo-articulo-form">
                            <label for="categoriaArticulo">Categoría</label>
                            <div class="input-articulo-icono">
                                <i class="fa-solid fa-tags"></i>
                                <select id="categoriaArticulo" name="categoriaArticulo" required>
                                    <option value="">Selecciona una categoría</option>
                                    <option value="pelota">Pelota</option>
                                    <option value="raqueta">Raqueta</option>
                                    <option value="uniforme">Uniforme</option>
                                    <option value="proteccion">Equipo Protector</option>
                                    <option value="fitness">Equipo de Fitness</option>
                                    <option value="redes">Porterías y Redes</option>
                                    <option value="calzado">Calzado</option>
                                </select>
                            </div>
                        </div>

                        <div class="grupo-articulo-form">
                            <label for="codigoArticulo">Código del artículo</label>
                            <div class="input-articulo-icono">
                                <i class="fa-solid fa-barcode"></i>
                                <input 
                                    type="text"
                                    id="codigoArticulo"
                                    name="codigoArticulo"
                                    placeholder="Ej: BAL-001"
                                    maxlength="30"
                                    required>
                            </div>
                        </div>

                        <div class="grupo-articulo-form">
                            <label for="cantidadArticulo">Cantidad</label>
                            <div class="input-articulo-icono">
                                <i class="fa-solid fa-hashtag"></i>
                                <input 
                                    type="number"
                                    id="cantidadArticulo"
                                    name="cantidadArticulo"
                                    placeholder="Cantidad"
                                    min="1"
                                    max="500"
                                    required>
                            </div>
                        </div>

                        <div class="grupo-articulo-form">
                            <label for="estadoArticulo">Estado</label>
                            <div class="input-articulo-icono">
                                <i class="fa-solid fa-circle-check"></i>
                                <select id="estadoArticulo" name="estadoArticulo" required>
                                    <option value="">Selecciona un estado</option>
                                    <option value="disponible">Disponible</option>
                                    <option value="prestado">Prestado</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                        </div>

                        <div class="grupo-articulo-form">
                            <label for="ubicacionArticulo">Ubicación</label>
                            <div class="input-articulo-icono">
                                <i class="fa-solid fa-location-dot"></i>
                                <input 
                                    type="text"
                                    id="ubicacionArticulo"
                                    name="ubicacionArticulo"
                                    placeholder="Ej: Bodega deportiva FET"
                                    maxlength="60"
                                    required>
                            </div>
                        </div>

                        <div class="grupo-articulo-form articulo-descripcion">
                            <label for="descripcionArticulo">Descripción</label>
                            <div class="textarea-articulo">
                                <i class="fa-solid fa-comment-dots"></i>
                                <textarea 
                                    id="descripcionArticulo"
                                    name="descripcionArticulo"
                                    placeholder="Describe el estado, características o detalles importantes del implemento deportivo."></textarea>
                            </div>
                        </div>

                        <div class="articulo-resumen">
                            <div>
                                <i class="fa-solid fa-circle-info"></i>
                                <p>
                                    Verifica que el código del artículo no esté repetido y que la cantidad registrada
                                    corresponda al inventario físico disponible.
                                </p>
                            </div>
                        </div>

                        <button class="btn-guardar-articulo" type="submit">
                            <i class="fa-solid fa-check"></i>
                            Guardar Artículo
                        </button>

                    </form>

                </article>

            </section>

            <!-- VISTA INVENTARIO -->
            <div id="vistaInventario">

                <!-- FILTROS -->
                <section class="inventario-filtros">

                    <div class="inventario-buscador">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Buscar artículos...">
                    </div>

                    <button class="btn-filtro">
                        <i class="fa-solid fa-filter"></i>
                        Todos los Estados
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>

                    <button class="btn-filtro">
                        <i class="fa-solid fa-filter"></i>
                        Todas las Categorías
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>

                </section>

                <!-- RESUMEN -->
                <section class="inventario-cards">

                    <article class="inventario-card">
                        <p>Total Artículos</p>
                        <h2>15</h2>
                    </article>

                    <article class="inventario-card">
                        <p>Disponibles</p>
                        <h2 class="numero-verde">9</h2>
                    </article>

                    <article class="inventario-card">
                        <p>Prestados</p>
                        <h2 class="numero-rojo">3</h2>
                    </article>

                    <article class="inventario-card">
                        <p>En Mantenimiento</p>
                        <h2 class="numero-naranja">3</h2>
                    </article>

                </section>

                <!-- TABLA -->
                <section class="inventario-tabla-contenedor">

                    <table class="inventario-tabla">

                        <thead>
                            <tr>
                                <th>Nombre del Artículo</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Disponible</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while($implemento = $consulta_implementos->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $implemento["nombre_implemento"]; ?></td>
                                    <td><?php echo $implemento["nombre_categoria"]; ?></td>
                                    <td>
                                        <span class="estado-inventario <?php echo $implemento["estado"]; ?>">
                                            <?php echo ucfirst($implemento["estado"]); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $implemento["cantidad_total"]; ?></td>
                                    <td><?php echo $implemento["cantidad_disponible"]; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>

                </section>

                <!-- PAGINACIÓN -->
                <section class="inventario-paginacion">

                    <p>
                        Mostrando 
                        <strong>
                            <?php echo $total_implementos > 0 ? $inicio + 1 : 0; ?> 
                            a 
                            <?php 
                                $fin = $inicio + $registros_por_pagina;
                                echo $fin > $total_implementos ? $total_implementos : $fin;
                            ?>
                        </strong> 
                        de <strong><?php echo $total_implementos; ?></strong> implementos
                    </p>

                    <div class="paginacion-botones">

                        <?php if ($pagina_actual > 1) { ?>
                            <button type="button" onclick="window.location.href='index.php?pagina=<?php echo $pagina_actual - 1; ?>'">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                        <?php } else { ?>
                            <button type="button" disabled>
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                        <?php } ?>

                        <?php for ($i = 1; $i <= $total_paginas; $i++) { ?>

                            <button 
                                type="button"
                                class="<?php echo $i == $pagina_actual ? 'pagina-activa' : ''; ?>"
                                onclick="window.location.href='index.php?pagina=<?php echo $i; ?>'">
                                <?php echo $i; ?>
                            </button>

                        <?php } ?>

                        <?php if ($pagina_actual < $total_paginas) { ?>
                            <button type="button" onclick="window.location.href='index.php?pagina=<?php echo $pagina_actual + 1; ?>'">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        <?php } else { ?>
                            <button type="button" disabled>
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        <?php } ?>

                    </div>

                </section>

            </div>

        </main>

    </div>

    <script>
        const btnMostrarArticulo = document.getElementById("btnMostrarArticulo");
        const btnVolverInventario = document.getElementById("btnVolverInventario");
        const vistaInventario = document.getElementById("vistaInventario");
        const formArticulo = document.getElementById("formArticulo");

        if (btnMostrarArticulo && btnVolverInventario && vistaInventario && formArticulo) {

            btnMostrarArticulo.addEventListener("click", () => {
                vistaInventario.style.display = "none";
                formArticulo.style.display = "block";

                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            });

            btnVolverInventario.addEventListener("click", () => {
                formArticulo.style.display = "none";
                vistaInventario.style.display = "block";

                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            });

        }
    </script>

</body>
</html>