package pe.edu.utp.techlabweb.controller;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;
import pe.edu.utp.techlabweb.model.Postre;
import pe.edu.utp.techlabweb.service.PostreService;

import java.util.List;

@Controller
public class PostreController {

    private final PostreService service;

    public PostreController(PostreService service) {
        this.service = service;
    }

    // READ: listar
    @GetMapping("/postres")
    public String listar(Model model) {
        List<Postre> postres = service.listar();
        model.addAttribute("mensaje", "¡Bienvenido! Hoy tenemos " + postres.size() + " postres artesanales");
        model.addAttribute("totalPostres", postres.size());
        model.addAttribute("postres", postres);
        if (!model.containsAttribute("postreForm")) {
            model.addAttribute("postreForm", new Postre());
        }
        return "postres";
    }

    // CREATE / UPDATE
    @PostMapping("/postres/guardar")
    public String guardar(@ModelAttribute("postreForm") Postre postre, RedirectAttributes ra) {
        boolean esNuevo = postre.getId() == null;
        service.guardar(postre);
        ra.addFlashAttribute("ok", esNuevo ? "Postre creado correctamente" : "Postre actualizado correctamente");
        return "redirect:/postres";
    }

    // Cargar datos en el formulario para editar
    @GetMapping("/postres/editar/{id}")
    public String editar(@PathVariable Long id, Model model) {
        List<Postre> postres = service.listar();
        Postre p = service.obtener(id);
        if (p == null) {
            return "redirect:/postres";
        }
        model.addAttribute("mensaje", "Editando postre");
        model.addAttribute("totalPostres", postres.size());
        model.addAttribute("postres", postres);
        model.addAttribute("postreForm", p);
        return "postres";
    }

    // DELETE
    @PostMapping("/postres/eliminar/{id}")
    public String eliminar(@PathVariable Long id, RedirectAttributes ra) {
        service.eliminar(id);
        ra.addFlashAttribute("ok", "Postre eliminado");
        return "redirect:/postres";
    }
}
