<?php
include_once 'Php/chatbot_processor.php';
?>

<!-- Botón flotante del chat -->
<div class="chat-bubble" id="chat-bubble">🤖</div>

<!-- Contenedor del chat -->
<div class="chat-container" id="chat-container">
    <div class="chat-header">
        Chatbot Nutricional
        <span class="close-btn" id="close-btn">❌</span>
    </div>

    <?php if (!isset($_GET['step']) || $_GET['step'] == "select"): ?>
        <form method="post" action="">
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

        <form method="post" action="">
            <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." required>
            <button type="submit">Enviar</button>
        </form>

        <div class="footer">
            Escribe "7" o "Salir" para cerrar la sesión.
        </div>

        <div style="text-align: center; padding: 10px;">
            <a href="?reset=1" style="color: #666; text-decoration: none; font-size: 12px;">🔄 Reiniciar chat</a>
        </div>
    <?php endif; ?>
</div>
