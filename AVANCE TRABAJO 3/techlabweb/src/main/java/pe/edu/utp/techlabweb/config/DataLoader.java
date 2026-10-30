package pe.edu.utp.techlabweb.config;

import org.springframework.boot.CommandLineRunner;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.lang.NonNull;
import pe.edu.utp.techlabweb.model.Postre;
import pe.edu.utp.techlabweb.repository.PostreRepository;

@Configuration
public class DataLoader {

    @Bean
    CommandLineRunner cargarPostres(PostreRepository repo) {
        return args -> {
            if (repo.count() > 0) return;

            repo.save(crear("Cheesecake de Frutos Rojos", "Delicioso cheesecake con frutos rojos frescos y salsa de arándanos.", 12.00, "cheesecake.jpg", "Queso crema, galletas, arándanos, fresas, frambuesas.", 350, "200g", "Disponible"));
            repo.save(crear("Tiramisú Italiano", "Clásico italiano con café, mascarpone y cacao. Auténtica receta.", 14.00, "tiramisu.jpg", "Mascarpone, café espresso, bizcochos, cacao, huevos.", 380, "180g", "Disponible"));
            repo.save(crear("Queque", "Queque casero esponjoso con nueces.", 5.00, "keke.jpg", "Harina, mantequilla, azúcar, huevos, nueces.", 320, "150g", "Disponible"));
            repo.save(crear("Pay de Manzana", "Pay de manzana con caramelo y crema.", 10.00, "pay_de_manzana.jpg", "Manzanas, azúcar, caramelo, harina, vainilla.", 250, "160g", "Limitado"));
            repo.save(crear("Flan Casero", "Flan tradicional con caramelo, suave y cremoso. Receta de la querida abuela.", 8.00, "flan.jpg", "Leche condensada, leche evaporada, huevos, caramelo, vainilla.", 280, "170g", "Disponible"));
            repo.save(crear("Rollo de Canela", "Rollo de canela esponjoso con relleno de crema y azúcar.", 10.00, "rollo_de_canela.jpg", "Harina, mantequilla, azúcar, huevos, canela, crema.", 60, "15g c/u", "Disponible"));
            repo.save(crear("Torta Tres Leches", "Bizcocho suave bañado en tres leches y cubierto con crema.", 12.00, "torta_tres_leches.jpg", "Harina, huevos, leche evaporada, leche condensada y crema.", 390, "180g", "Disponible"));
            repo.save(crear("Torta Selva Negra", "Capas de chocolate, crema y cerezas en una combinación clásica.", 14.00, "selva_negra.jpg", "Chocolate, harina, crema, cerezas y huevos.", 420, "180g", "Disponible"));
            repo.save(crear("Crepe Dulce", "Crepe delicado servido con fruta fresca y salsa de chocolate.", 10.00, "crepe.jpg", "Harina, leche, huevo, frutas y chocolate.", 280, "150g", "Disponible"));
            repo.save(crear("Wafle con Frutas", "Wafle dorado acompañado de frutas y un toque de miel.", 11.00, "wafle.jpg", "Harina, leche, huevos, frutas y miel.", 310, "180g", "Disponible"));
            repo.save(crear("Roscas Dulces", "Roscas horneadas, suaves y perfectas para acompañar el café.", 6.00, "roscas.jfif", "Harina, azúcar, huevos y mantequilla.", 220, "100g", "Disponible"));
        };
    }

    @NonNull
    private Postre crear(String nombre, String descripcion, double precio, String imagen,
                         String ingredientes, int calorias, String tamano, String disponibilidad) {
        Postre p = new Postre();
        p.setNombre(nombre);
        p.setDescripcion(descripcion);
        p.setPrecio(precio);
        p.setImagen(imagen);
        p.setIngredientes(ingredientes);
        p.setCalorias(calorias);
        p.setTamano(tamano);
        p.setDisponibilidad(disponibilidad);
        return p;
    }
}