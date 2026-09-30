package pe.edu.utp.techlab_web.controller;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class PortalController {

    @GetMapping("/")
    public String inicio() {
        // Devuelve el nombre exacto de tu archivo HTML sin la extensión .html
        return "index"; 
    }
    @GetMapping("/pedidos")
    public String pedidos() {
        return "pedidos"; // Esto buscará pedidos.html en templates/
    }

    // Nueva ruta para reservaciones
    @GetMapping("/reservaciones")
    public String reservaciones() {
        return "reservaciones"; // Esto buscará reservaciones.html
    }

    // Nueva ruta para usuario
    @GetMapping("/usuario")
    public String usuario() {
        return "usuario"; // Esto buscará usuario.html
    }
    
     @GetMapping("/coffe")
    public String coffe() {
        return "coffe"; // Esto buscará usuario.html
    }
    @GetMapping("/postres")
    public String postres() {
        return "postres"; // Esto buscará usuario.html
    }
     @GetMapping("/promociones")
    public String promociones() {
        return "promociones"; // Esto buscará usuario.html
    }
     
}