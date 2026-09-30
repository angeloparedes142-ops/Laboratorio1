package pe.edu.utp.techlabweb.controller;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import pe.edu.utp.techlabweb.service.CoffeeService;

@Controller
public class PortalController {

    private final CoffeeService coffeeService;

    // Inyección de dependencias por constructor (Requisito de Spring)
    public PortalController(CoffeeService coffeeService) {
        this.coffeeService = coffeeService;
    }

    @GetMapping("/")
    public String inicio() { return "index"; }
    
    @GetMapping("/pedidos")
    public String pedidos() { return "pedidos"; }

    @GetMapping("/reservaciones")
    public String reservaciones() { return "reservaciones"; }

    @GetMapping("/usuario")
    public String usuario() { return "usuario"; }
    
    // RUTA MODIFICADA PARA COFFEE
    @GetMapping("/coffe")
    public String coffe(Model model) {
        model.addAttribute("calientes", coffeeService.buscarPorTipo("Caliente"));
        model.addAttribute("frias", coffeeService.buscarPorTipo("Frio"));
        model.addAttribute("todos", coffeeService.obtenerTodos());
        return "coffe"; 
    }
    
    @GetMapping("/postres")
    public String postres() { return "postres"; }
    
    @GetMapping("/promociones")
    public String promociones() { return "promociones"; }
}