<?php 
session_start();

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
        
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?step=chat");
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
            header("Location: " . dirname($_SERVER['HTTP_REFERER']) . "/index.php");
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
        
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?step=chat");
        exit();
    }
}

// Reset del chat
if (isset($_GET['reset'])) {
    session_destroy();
    header("Location: " . dirname($_SERVER['HTTP_REFERER']) . "/index.php");
    exit();
}
?>