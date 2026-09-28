package pe.edu.utp.techlab.controller;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Import;
import org.springframework.boot.webmvc.test.autoconfigure.WebMvcTest;
import org.springframework.test.web.servlet.MockMvc;
import pe.edu.utp.techlab.service.CourseService;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.*;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.*;

@WebMvcTest({CourseController.class, PortalController.class})
@Import(CourseService.class)
class WebRoutesTests {

    @Autowired
    private MockMvc mvc;

    @Test
    void listadoYFiltroFuncionan() throws Exception {
        mvc.perform(get("/api/v1/cursos"))
            .andExpect(status().isOk())
            .andExpect(jsonPath("$.length()").value(3));
            
        mvc.perform(get("/api/v1/cursos").param("q", "SPRING"))
            .andExpect(status().isOk())
            .andExpect(jsonPath("$[0].id").value(3));
    }

    @Test
    void detalleYErroresUsanEstadosHttp() throws Exception {
        mvc.perform(get("/api/v1/cursos/2"))
            .andExpect(status().isOk())
            .andExpect(jsonPath("$.titulo").value("Bootstrap"));
            
        mvc.perform(get("/api/v1/cursos/999"))
            .andExpect(status().isNotFound());
            
        mvc.perform(get("/api/v1/cursos/abc"))
            .andExpect(status().isBadRequest());
            
        mvc.perform(get("/api/v1/cursos").param("q", "x".repeat(61)))
            .andExpect(status().isBadRequest());
    }

    @Test
    void metodoNoPermitidoYRedireccion() throws Exception {
        mvc.perform(post("/api/v1/cursos"))
            .andExpect(status().isMethodNotAllowed());
            
        mvc.perform(get("/portal"))
            .andExpect(status().isFound())
            .andExpect(redirectedUrl("/catalogo.html"));
    }
    
    // Reto de transferencia
    @Test
    void duracionRetornaDtoOError() throws Exception {
        mvc.perform(get("/api/v1/cursos/2/duracion"))
            .andExpect(status().isOk())
            .andExpect(jsonPath("$.id").value(2))
            .andExpect(jsonPath("$.horas").value(16));

        mvc.perform(get("/api/v1/cursos/999/duracion"))
            .andExpect(status().isNotFound());

        mvc.perform(get("/api/v1/cursos/abc/duracion"))
            .andExpect(status().isBadRequest());
    }
}
