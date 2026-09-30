package pe.edu.utp.techlab_web.controller;

import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;

/**
 * Guarda las reservaciones en memoria y las comparte entre
 * PortalController (GET) y ReservaFormController (POST/eliminar).
 */
@Service
public class ReservaService {

    private final List<Reserva> reservas = new ArrayList<>();

    public List<Reserva> listar() {
        return reservas;
    }

    public void agregar(Reserva reserva) {
        reservas.add(reserva);
    }

    public void eliminar(int index) {
        if (index >= 0 && index < reservas.size()) {
            reservas.remove(index);
        }
    }
}