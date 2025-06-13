from flask import Flask, render_template, request, session, redirect, url_for
import re
from markupsafe import Markup
import google.generativeai as genai

app = Flask(__name__)
app.secret_key = 'supersecreto'

# Configura la API de Gemini
genai.configure(api_key="AIzaSyCbNuldbtQCWXgSY-0UeUr28RmIr8BaRm8")
model = genai.GenerativeModel(model_name="gemini-1.5-flash")

@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == 'POST':
        idioma = request.form['idioma']
        session['idioma'] = idioma
        session['chat_history'] = []
        session['messages'] = []

        # Instrucción inicial
        system_prompt = f"Responde siempre en {idioma}."
        session['chat_history'].append({"role": "user", "parts": [system_prompt]})

        # Menú inicial
        menu_opciones = (
            "¡Hola! Soy tu Chatbot Nutricional.<br>"
            "Selecciona una opción para comenzar:<br>"
            "1️⃣ Menús<br>"
            "2️⃣ Tipos de dietas<br>"
            "3️⃣ Contactar con un especialista<br>"
            "4️⃣ Consejos de alimentación saludable<br>"
            "5️⃣ Información sobre nutrientes<br>"
            "6️⃣ Planes semanales<br>"
            "7️⃣ Salir"
        )
        session['chat_history'].append({"role": "model", "parts": [menu_opciones]})
        session['messages'].append(("Chatbot Nutricional", Markup(menu_opciones)))

        return redirect(url_for('chat'))

    return render_template('chat.html', step="select")


@app.route('/chat', methods=['GET', 'POST'])
def chat():
    if 'idioma' not in session or 'chat_history' not in session:
        return redirect(url_for('index'))

    chat = model.start_chat(history=session['chat_history'])

    if request.method == 'POST':
        user_input = request.form['mensaje'].strip().lower()
        session['chat_history'].append({"role": "user", "parts": [user_input]})
        session['messages'].append(("Tú", user_input))

        # Salir
        if user_input in ["7", "salir"]:
            return redirect(url_for('reset'))

        # Volver al menú
        elif user_input in ["menu", "volver al menú", "volver al menu"]:
            menu_opciones = (
                "Selecciona una opción para comenzar:<br>"
                "1️⃣ Menús<br>"
                "2️⃣ Tipos de dietas<br>"
                "3️⃣ Contactar con un especialista<br>"
                "4️⃣ Consejos de alimentación saludable<br>"
                "5️⃣ Información sobre nutrientes<br>"
                "6️⃣ Planes semanales<br>"
                "7️⃣ Salir"
            )
            session['chat_history'].append({"role": "model", "parts": [menu_opciones]})
            session['messages'].append(("Chatbot Nutricional", Markup(menu_opciones)))

        # Contactar especialista
        elif user_input in ["3", "contactar con un especialista"]:
            whatsapp_link = "https://wa.me/5491123456789"
            respuesta = f"Puedes contactar con un especialista aquí: <a href='{whatsapp_link}' target='_blank'>WhatsApp</a>"
            session['chat_history'].append({"role": "model", "parts": [respuesta]})
            session['messages'].append(("Chatbot Nutricional", Markup(respuesta)))

        # Respuesta normal del chatbot
        else:
            response = chat.send_message(user_input)
            texto = response.text
            respuesta_html = re.sub(r'\*\*(.*?)\*\*', r'<strong>\1</strong>', texto)

            # Agregar botón de volver al menú
            volver_menu = (
                "<br><br><button onclick=\"enviarMensaje('menu')\" style='padding: 6px 12px; border: none; background: #007bff; color: white; border-radius: 4px; cursor: pointer;'>🔙 Volver al menú</button>"
            )
            respuesta_html += volver_menu

            session['chat_history'].append({"role": "model", "parts": [texto]})
            session['messages'].append(("Chatbot Nutricional", Markup(respuesta_html)))

    return render_template('chat.html', step="chat", messages=session['messages'], idioma=session['idioma'])


@app.route('/reset')
def reset():
    session.clear()
    return redirect(url_for('index'))

if __name__ == '__main__':
    app.run(debug=True)
