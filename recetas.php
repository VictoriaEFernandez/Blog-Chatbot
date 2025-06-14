<?php
// IncluiMOS la conexión a la base de datos
include 'php/conexion.php'; 

$sql = "SELECT titulo, subtitulo, imagen_url FROM receta"; // Definimos la consulta SQL para obtener el título, subtítulo e imagen de las recetas
$result = $conn->query($sql); // EjecutaMOS la consulta y almacena el resultado

// VerificaMOS si hubo un error en la consulta
if (!$result) { // Comprobamos si el resultado de la consulta es falso (error)
    die("Error en la consulta: " . $conn->error); // Terminamos la ejecución y muestra un mensaje de error
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Nutricionista Lara - Recetas</title> 
    <link rel="stylesheet" href="styles/styles.css">  
    <link rel="stylesheet" href="styles/chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">  
</head>

<body>

    <header>
        <nav>
            <ul>  
                <li><a href="index.php">Sobre mí</a></li>  
                <li><a href="recetas.php">Recetas</a></li>  
                <li><a href="contacto.php">Contacto</a></li>  
                <li><a href="login.php">Panel de Administración</a></li>
            </ul>
        </nav>
    </header>

    <main>

        <h1>Recetas saludables</h1>  

        <section id="recetas">  
            <div class="recetas-container"> 
                <?php
                // Verificamos si hay recetas en la base de datos
                if ($result->num_rows > 0) { // Comprobamos si hay filas en el resultado (recetas disponibles)
                    // Recorremos las recetas y mostrarlas en HTML
                    while ($row = $result->fetch_assoc()) { // Iteramos sobre cada fila de resultados
                        $titulo = urlencode($row["titulo"]); // Codificamos el título para usarlo en la URL
                        echo '<div class="recetas-box">'; // Abrimos el contenedor de la receta
                        echo '<a href="receta.php?titulo=' . $titulo . '">'; // Enlace a la página de detalles de la receta pasando el título como parámetro
                        echo '<img src="' . $row["imagen_url"] . '" alt="' . $row["titulo"] . '">'; // Muestramos la imagen de la receta
                        echo '<h3>' . $row["titulo"] . '</h3>'; // Muestramos el título de la receta
                        echo '<p>' . $row["subtitulo"] . '</p>'; // Muestramos el subtítulo de la receta
                        echo '</a>'; // Cerramos el enlace
                        echo '</div>'; // Cerramos el contenedor de la receta
                    }
                } else {
                    echo "<p>No hay recetas disponibles en este momento.</p>"; // Mensaje si no hay recetas en la base de datos
                }

                // Cerrar la conexión al final
                $conn->close(); // Cierra la conexión a la base de datos
                ?>
            </div>
        </section>
    </main>

    <footer>
        <p>Contáctame:</p>
        <div class="iconos-wpp-ins">  
            <a href="https://wa.me/1234567890" target="_blank" class="social-icon">  
                <i class="fab fa-whatsapp"></i> 3764636363 
            </a>
            <a href="https://www.instagram.com/_fleitadana_" target="_blank" class="social-icon">  
                <i class="fab fa-instagram"></i> TuNutri2025  
            </a>
        </div>
        <p>&copy; 2024 Nutricionista Lara. Todos los derechos reservados.</p>  
    </footer>

    <!-- Incluir el chatbot AQUÍ, después del HTML -->
    <?php include 'php/chatbot_widget.php'; ?>

    <script src="js/chat.js"></script>
</body>

</html>
