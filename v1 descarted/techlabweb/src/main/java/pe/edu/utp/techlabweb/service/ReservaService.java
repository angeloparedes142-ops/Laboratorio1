package pe.edu.utp.techlabweb.service;

import org.springframework.stereotype.Service;
import pe.edu.utp.techlabweb.dto.Reserva;
import java.util.ArrayList;
import java.util.List;

@Service
public class ReservaService {
    private List<Reserva> reservas = new ArrayList<>();
    public List<Reserva> listar() { return reservas; }
    public void agregar(Reserva reserva) { reservas.add(reserva); }
    public void guardar(Reserva reserva) { agregar(reserva); }
    public void eliminar(int index) {
        if (index >= 0 && index < reservas.size()) {
            reservas.remove(index);
        }
    }
}