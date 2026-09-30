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
        return "index"; 
        // Devuelve el nombre exacto de tu archivo HTML sin la extensión .html
        return "index";
    }

    @GetMapping("/pedidos")
    public String pedidos() {
        return "pedidos";
    }
    @GetMapping("/reservaciones")
    public String reservaciones() {
        return "reservaciones";
    public String reservaciones(Model model) {
        model.addAttribute("reserva", new Reserva());
        model.addAttribute("reservas", reservaService.listar());
        return "reservaciones"; // Esto buscará reservaciones.html
    }
    @GetMapping("/usuario")
    public String usuario() {
        return "usuario";
    }

    @GetMapping("/coffe")

    public String coffe() {
        return "coffe";
    }

    @GetMapping("/postres")
    public String postres(Model model) {
    model.addAttribute("mensaje", "¡Bienvenido! Hoy tenemos 11 postres artesanales");
    model.addAttribute("totalPostres", 11);
        return "postres";
}
     @GetMapping("/promociones")
    public String postres() {
        return "postres";
    }
    @GetMapping("/promociones")
    public String promociones() {
        return "promociones";
    }
}