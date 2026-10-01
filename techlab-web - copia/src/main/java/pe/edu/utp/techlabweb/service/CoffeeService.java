package pe.edu.utp.techlabweb.service;

import org.springframework.stereotype.Service;
import pe.edu.utp.techlabweb.dto.CoffeeDto;
import java.util.List;

@Service
public class CoffeeService {
    
    // Lista inmutable con tus productos
    private final List<CoffeeDto> menu = List.of(
        new CoffeeDto("Espresso Clásico", "Intenso y aromático, extraído a la perfección.", "https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=400&h=300&q=80", "Caliente", "S/ 6.00", "S/ 8.00", "-"),
        new CoffeeDto("Cappuccino", "Partes iguales de espresso, leche vaporizada y espuma sedosa.", "https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=400&h=300&q=80", "Caliente", "S/ 9.00", "S/ 11.00", "S/ 13.00"),
        new CoffeeDto("Iced Caramel Latte", "Café helado con un toque dulce de caramelo y leche fría.", "https://images.unsplash.com/photo-1499961024600-ad094db305cc?auto=format&fit=crop&w=400&h=300&q=80", "Frio", "-", "S/ 14.00", "S/ 16.00"),
        new CoffeeDto("Frappé Clásico", "Bebida licuada con hielo, base de café y coronada con crema.", "https://images.unsplash.com/photo-1572490122747-3968b75cc699?auto=format&fit=crop&w=400&h=300&q=80", "Frio", "-", "S/ 15.00", "S/ 18.00")
        // Puedes agregar el resto de tus cafés aquí siguiendo este mismo formato
    );

    // Método para filtrar por pestaña (Calientes o Frías)
    public List<CoffeeDto> buscarPorTipo(String tipo) {
        return menu.stream()
                   .filter(c -> c.tipo().equalsIgnoreCase(tipo))
                   .toList();
    }
    
    // Método para llenar la tabla de precios
    public List<CoffeeDto> obtenerTodos() { 
        return menu; 
    }
}