<?php
// backend/test_whatsapp.php
// ESTE ARCHIVO ES SOLO PARA PROBAR SI EL WHATSAPP FUNCIONA

include 'whatsapp_helper.php';

// 1. Pon aquí TU número de celular real para la prueba (con el 9...)
$numero_prueba = "999123456"; // <--- ¡CAMBIA ESTO POR TU NÚMERO!

echo "<h1>🕵️ Probador de WhatsApp</h1>";
echo "<p>Intentando enviar mensaje a: <strong>$numero_prueba</strong>...</p>";

// 2. Intentar enviar
$respuesta = enviarWhatsApp($numero_prueba, "🔔 Hola, esta es una prueba técnica del sistema.");

// 3. Mostrar el resultado
echo "<h3>Respuesta de Green-API:</h3>";
echo "<pre style='background: #eee; padding: 10px; border: 1px solid #999;'>";
print_r($respuesta);
echo "</pre>";

echo "<hr>";
echo "<h3>¿Qué significa esto?</h3>";
echo "<ul>";
echo "<li>Si ves <strong>'idMessage'</strong>: ¡El mensaje SALIÓ CORRECTAMENTE! (Revisa tu celular).</li>";
echo "<li>Si ves <strong>'Not authorized'</strong>: Tu celular se desconectó. Vuelve a escanear el QR.</li>";
echo "<li>Si ves <strong>'Limit reached'</strong>: El plan gratuito solo deja enviar a 3 números diferentes.</li>";
echo "<li>Si ves <strong>'null'</strong> o vacío: Revisa tu conexión a internet o el Token.</li>";
echo "</ul>";
?>