package pe.edu.utp.techlabweb.controller;

import jakarta.validation.Valid;
import pe.edu.utp.techlabweb.dto.Reserva;
import pe.edu.utp.techlabweb.service.ReservaService;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.validation.BindingResult;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;

@Controller
public class ReservaFormController {
    private final ReservaService reservaService;
    public ReservaFormController(ReservaService reservaService) {
        this.reservaService = reservaService;
    }
    @PostMapping("/reservaciones")
    public String guardarReserva(@Valid @ModelAttribute("reserva") Reserva reserva,
                                  BindingResult resultado,
                                  Model model) {
        if (resultado.hasErrors()) {
            model.addAttribute("reservas", reservaService.listar());
            return "reservaciones";
        }
        reservaService.agregar(reserva);
        model.addAttribute("reserva", new Reserva());
        model.addAttribute("reservas", reservaService.listar());
        model.addAttribute("confirmada", reserva);
        return "reservaciones";
    }
    @PostMapping("/reservaciones/eliminar/{index}")
    public String eliminarReserva(@PathVariable int index) {
        reservaService.eliminar(index);
        return "redirect:/reservaciones";
    }
}