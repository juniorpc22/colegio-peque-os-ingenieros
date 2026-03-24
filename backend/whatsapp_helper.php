<?php
// backend/whatsapp_helper.php

function enviarWhatsApp($numero, $mensaje) {
    // ============================================================
    // CONFIGURACIÓN: GREEN-API
    // ============================================================
    
    // IMPORTANTE: ¡Las comillas "" son OBLIGATORIAS para texto y códigos!
    $idInstance = "7103537629";  
    $apiTokenInstance = "8ea59b0e1bf44a82ae764487d2063947115ca7c99114460e8b"; 

    // LÓGICA DE ENVÍO
    $url = "https://api.green-api.com/waInstance$idInstance/SendMessage/$apiTokenInstance";

    // 1. Limpiar el número (quitar guiones, espacios, paréntesis)
    $numero = preg_replace('/[^0-9]/', '', $numero);
    
    // 2. Agregar código de país 51 (Perú) si no lo tiene
    // Si tiene 9 dígitos (ej: 999888777), le ponemos el 51 delante.
    if (strlen($numero) == 9) { 
        $numero = "51" . $numero; 
    }

    $data = array(
        'chatId' => $numero . '@c.us',
        'message' => $mensaje
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $result = curl_exec($ch);
    
    // Verificamos si hubo error de conexión
    if(curl_errno($ch)){
        error_log('Error CURL WhatsApp: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    return $result;
}
?>