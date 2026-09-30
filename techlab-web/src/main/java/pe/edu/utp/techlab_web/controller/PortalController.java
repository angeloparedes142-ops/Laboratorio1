package pe.edu.utp.techlab_web.controller;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class PortalController {

    private final ReservaService reservaService;

    public PortalController(ReservaService reservaService) {
        this.reservaService = reservaService;
    }

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
    public String reservaciones(Model model) {
        model.addAttribute("reserva", new Reserva());
        model.addAttribute("reservas", reservaService.listar());
        return "reservaciones"; // Esto buscará reservaciones.html
    }

    // Nueva ruta para usuario
    @GetMapping("/usuario")
    public String usuario() {
        return "usuario"; // Esto buscará usuario.html
    }

    @GetMapping("/coffe")
    public String coffe() {
        return "coffe";
    }

    @GetMapping("/postres")
    public String postres() {
        return "postres";
    }

    @GetMapping("/promociones")
    public String promociones() {
        return "promociones";
    }
}