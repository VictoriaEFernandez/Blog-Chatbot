<?php 
session_start();
include 'php/conexion.php'; 

// Función para hacer llamadas a la API de Gemini
function callGeminiAPI($message, $chat_history = []) {
    $api_key = "AIzaSyCbNuldbtQCWXgSY-0UeUr28RmIr8BaRm8";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;
    
    $data = [
        "contents" => array_merge($chat_history, [
            [
                "role" => "user",
                "parts" => [["text" => $message]]
            ]
        ])
    ];
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return "Error al conectar con el servicio de chat.";
    }
    
    $response = json_decode($result, true);
    
    if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        return $response['candidates'][0]['content']['parts'][0]['text'];
    }
    
    return "No se pudo obtener respuesta del chatbot.";
}

// Procesar formularios del chat
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Selección de idioma
    if (isset($_POST['idioma'])) {
        $idioma = $_POST['idioma'];
        $_SESSION['idioma'] = $idioma;
        $_SESSION['chat_history'] = [];
        $_SESSION['messages'] = [];
        
        // Instrucción inicial
        $system_prompt = "Responde siempre en " . $idioma . ".";
        $_SESSION['chat_history'][] = [
            "role" => "user",
            "parts" => [["text" => $system_prompt]]
        ];
        
        // Menú inicial
        $menu_opciones = "¡Hola! Soy tu Chatbot Nutricional.<br>" .
                        "Selecciona una opción para comenzar:<br>" .
                        "1️⃣ Menús<br>" .
                        "2️⃣ Tipos de dietas<br>" .
                        "3️⃣ Contactar con un especialista<br>" .
                        "4️⃣ Consejos de alimentación saludable<br>" .
                        "5️⃣ Información sobre nutrientes<br>" .
                        "6️⃣ Planes semanales<br>" .
                        "7️⃣ Salir";
        
        $_SESSION['chat_history'][] = [
            "role" => "model",
            "parts" => [["text" => $menu_opciones]]
        ];
        $_SESSION['messages'][] = ["autor" => "Chatbot Nutricional", "texto" => $menu_opciones];
        
        header("Location: index.php?step=chat");
        exit();
    }
    
    // Procesar mensaje del chat
    if (isset($_POST['mensaje'])) {
        $user_input = trim(strtolower($_POST['mensaje']));
        
        // Agregar mensaje del usuario
        $_SESSION['chat_history'][] = [
            "role" => "user",
            "parts" => [["text" => $user_input]]
        ];
        $_SESSION['messages'][] = ["autor" => "Tú", "texto" => $_POST['mensaje']];
        
        // Salir
        if ($user_input === "7" || $user_input === "salir") {
            session_destroy();
            header("Location: index.php");
            exit();
        }
        
        // Volver al menú
        elseif (in_array($user_input, ["menu", "volver al menú", "volver al menu"])) {
            $menu_opciones = "Selecciona una opción para comenzar:<br>" .
                            "1️⃣ Menús<br>" .
                            "2️⃣ Tipos de dietas<br>" .
                            "3️⃣ Contactar con un especialista<br>" .
                            "4️⃣ Consejos de alimentación saludable<br>" .
                            "5️⃣ Información sobre nutrientes<br>" .
                            "6️⃣ Planes semanales<br>" .
                            "7️⃣ Salir";
            
            $_SESSION['chat_history'][] = [
                "role" => "model",
                "parts" => [["text" => $menu_opciones]]
            ];
            $_SESSION['messages'][] = ["autor" => "Chatbot Nutricional", "texto" => $menu_opciones];
        }
        
        // Contactar especialista
        elseif ($user_input === "3" || $user_input === "contactar con un especialista") {
            $whatsapp_link = "https://wa.me/5491123456789";
            $respuesta = "Puedes contactar con un especialista aquí: <a href='" . $whatsapp_link . "' target='_blank'>WhatsApp</a>";
            
            $_SESSION['chat_history'][] = [
                "role" => "model",
                "parts" => [["text" => $respuesta]]
            ];
            $_SESSION['messages'][] = ["autor" => "Chatbot Nutricional", "texto" => $respuesta];
        }
        
        // Respuesta normal del chatbot
        else {
            $response = callGeminiAPI($_POST['mensaje'], $_SESSION['chat_history']);
            
            // Convertir **texto** a <strong>texto</strong>
            $respuesta_html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $response);
            
            // Agregar botón de volver al menú
            $volver_menu = "<br><br><button onclick=\"enviarMensaje('menu')\" style='padding: 6px 12px; border: none; background: #007bff; color: white; border-radius: 4px; cursor: pointer;'>🔙 Volver al menú</button>";
            $respuesta_html .= $volver_menu;
            
            $_SESSION['chat_history'][] = [
                "role" => "model",
                "parts" => [["text" => $response]]
            ];
            $_SESSION['messages'][] = ["autor" => "Chatbot Nutricional", "texto" => $respuesta_html];
        }
        
        header("Location: index.php?step=chat");
        exit();
    }
}

// Reset del chat
if (isset($_GET['reset'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Consultamos para obtener el texto de presentación
$sql = "SELECT presentacion FROM Usuario WHERE id_usuario = 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $presentacion = $row['presentacion'];
} else {
    $presentacion = "No se encontró presentación.";
}

$sql = "SELECT tipo FROM Servicio"; 
$result = $conn->query($sql);

$servicios = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $servicios[] = $row['tipo'];
    }
} else {
    echo "No se encontraron servicios.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutricionista Lara</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .chat-bubble {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 30px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
            z-index: 999;
        }

        .chat-bubble:hover {
            transform: scale(1.1);
        }

        .chat-container {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            display: none;
            flex-direction: column;
            z-index: 999;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .chat-header {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn {
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }

        .chat-box {
            padding: 15px;
            height: 300px;
            overflow-y: auto;
            background: #f9f9f9;
        }

        .msg {
            margin-bottom: 10px;
        }

        .user {
            text-align: right;
            background-color: #d1e7dd;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
            max-width: 80%;
            margin-left: 20%;
        }

        .bot {
            text-align: left;
            background-color: #e7f1ff;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
            max-width: 80%;
        }

        .chat-container form {
            display: flex;
            gap: 5px;
            padding: 10px;
            border-top: 1px solid #ddd;
        }

        .chat-container input[type="text"] {
            flex: 1;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .chat-container button {
            padding: 10px 15px;
            background-color: #4CAF50;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .chat-container button:hover {
            background-color: #45a049;
        }

        .footer {
            margin-top: 10px;
            font-size: 0.85em;
            color: #777;
            text-align: center;
        }

        .chat-container select {
            padding: 8px;
            margin: 10px auto;
            display: block;
            border-radius: 5px;
        }

        .chat-container form[method="post"] > label {
            display: block;
            text-align: center;
            margin-top: 10px;
        }
    </style>
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
        
        <!-- Sección Portada -->
        <h1>¡Hola soy Lara!</h1>
        <h2>Nutricionista Graduada</h2>

        <!-- Sección Carrusel -->
        <section class="carrusel">
            <div class="carrusel-container">
                <div class="carrusel-imagenes">
                    <img src="img/carrusel1.jpg" alt="Imagen 1">
                    <img src="img/carrusel3.jpg" alt="Imagen 3">
                    <img src="img/carrusel4.jpg" alt="Imagen 4">
                    <img src="img/carrusel5.jpg" alt="Imagen 5">
                    <img src="img/carrusel6.jpg" alt="Imagen 6">
                    <img src="img/carrusel7.jpg" alt="Imagen 7">
                    <img src="img/carrusel8.jpg" alt="Imagen 8">
                    <img src="img/carrusel9.jpg" alt="Imagen 9">
                    <img src="img/carrusel2.jpg" alt="Imagen 2">
                    <img src="img/carrusel10.jpg" alt="Imagen 10">
                    <img src="img/carrusel11.jpg" alt="Imagen 11">
                </div>
            </div>
        </section>

        <section class="sobreMi">
            <div class="cover-text">
                <h3><?php echo htmlspecialchars($presentacion); ?></h3>
            </div>
        </section>

        <!-- Sección Servicios -->
        <section class="servicios">
            <h2>Descubre Mis Servicios</h2>
            <table>
                <?php if (!empty($servicios)): ?>
                    <?php foreach ($servicios as $servicio): ?> 
                        <tr>
                            <td>
                                <h3><?php echo htmlspecialchars($servicio); ?></h3>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>
                            <h3>No hay servicios disponibles.</h3>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
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

    <!-- Botón flotante del chat -->
    <div class="chat-bubble" id="chat-bubble">🤖</div>

    <!-- Contenedor del chat -->
    <div class="chat-container" id="chat-container">
        <div class="chat-header">
            Chatbot Nutricional
            <span class="close-btn" id="close-btn">❌</span>
        </div>

        <?php if (!isset($_GET['step']) || $_GET['step'] == "select"): ?>
            <form method="post">
                <label for="idioma">Selecciona un idioma:</label>
                <select name="idioma" required>
                    <option value="">-- Elige un idioma --</option>
                    <option value="español">Español</option>
                    <option value="inglés">Inglés</option>
                    <option value="francés">Francés</option>
                    <option value="alemán">Alemán</option>
                    <option value="portugués">Portugués</option>
                    <option value="italiano">Italiano</option>
                </select>
                <button type="submit">Comenzar</button>
            </form>

        <?php elseif (isset($_GET['step']) && $_GET['step'] == "chat"): ?>
            <div class="chat-box" id="chat">
                <?php if (isset($_SESSION['messages'])): ?>
                    <?php foreach ($_SESSION['messages'] as $mensaje): ?>
                        <div class="msg">
                            <?php if ($mensaje['autor'] == "Tú"): ?>
                                <div class="user"><?php echo htmlspecialchars($mensaje['texto']); ?></div>
                            <?php else: ?>
                                <div class="bot"><strong>🤖 <?php echo htmlspecialchars($mensaje['autor']); ?>:</strong> <?php echo $mensaje['texto']; ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="post">
                <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." required>
                <button type="submit">Enviar</button>
            </form>

            <div class="footer">
                Escribe "7" o "Salir" para cerrar la sesión.
            </div>

            <div style="text-align: center; padding: 10px;">
                <a href="index.php?reset=1" style="color: #666; text-decoration: none; font-size: 12px;">🔄 Reiniciar chat</a>
            </div>
        <?php endif; ?>

    </div>

    <script src="js/carrusel.js"></script>

    <script>
        const chatBubble = document.getElementById('chat-bubble');
        const chatContainer = document.getElementById('chat-container');
        const closeBtn = document.getElementById('close-btn');

        // Mostrar u ocultar el chat al hacer click en el botón
        chatBubble.addEventListener('click', () => {
            const isVisible = chatContainer.style.display === 'block';
            chatContainer.style.display = isVisible ? 'none' : 'block';
            localStorage.setItem('chatVisible', !isVisible);
        });

        // Botón de cerrar dentro del chat
        closeBtn.addEventListener('click', () => {
            chatContainer.style.display = 'none';
            localStorage.setItem('chatVisible', 'false');
        });

        // Mantener el estado al recargar
        window.addEventListener('load', () => {
            if (localStorage.getItem('chatVisible') === 'true') {
                chatContainer.style.display = 'block';
            }
        });

        // Auto-scroll
        window.addEventListener('load', () => {
            const chatBox = document.getElementById('chat');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });

        // Función para enviar mensajes desde botones
        function enviarMensaje(mensaje) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'mensaje';
            input.value = mensaje;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>

</body>

</html>