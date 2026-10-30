package pe.edu.utp.techlabweb.service;

import org.springframework.stereotype.Service;
import java.util.HashMap;
import java.util.Map;

@Service
public class AsesorBaristaService {

    public Map<String, String> procesarConsulta(String mensaje) {
        Map<String, String> respuesta = new HashMap<>();
        String lowerMsg = mensaje.toLowerCase();

        if (lowerMsg.contains("puntos") || lowerMsg.contains("reward")) {
            respuesta.put("tipo", "puntos");
            respuesta.put("mensaje", "⭐ Tienes <strong>850 Puntos VIP Gold</strong>. Te faltan 150 puntos para tu café gratis.");
        } else if (lowerMsg.contains("promo") || lowerMsg.contains("descuento") || lowerMsg.contains("2x1")) {
            respuesta.put("tipo", "promo");
            respuesta.put("mensaje", "🎟️ Promociones activas: <strong>Combo Mañanero (S/ 12.00)</strong>, <strong>2x1 en Cappuccino</strong> y <strong>Tarde Dulce con Roscas</strong>.");
        } else if (lowerMsg.contains("recomiend") || lowerMsg.contains("bebida") || lowerMsg.contains("cafe")) {
            respuesta.put("tipo", "recomendacion");
            respuesta.put("mensaje", "☕ Te recomendamos nuestro nuevo <strong>Frappé Moka Especial</strong> o un clásico <strong>Cappuccino Artesanal</strong>.");
        } else {
            respuesta.put("tipo", "general");
            respuesta.put("mensaje", "¡Con gusto te asisto! Puedes consultarme sobre tus puntos, promociones del día o recomendaciones de repostería.");
        }

        return respuesta;
    }
}