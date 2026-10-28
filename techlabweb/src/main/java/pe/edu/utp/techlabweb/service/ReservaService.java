package pe.edu.utp.techlabweb.service;

import org.springframework.stereotype.Service;

import pe.edu.utp.techlabweb.dto.Reserva;
import pe.edu.utp.techlabweb.repository.ReservaRepository;

import java.util.List;

/**
 * Guarda las reservaciones en MySQL (con JPA/Hibernate) y las comparte entre
 * PortalController (GET) y ReservaFormController (POST/eliminar).
 */
@Service
public class ReservaService {

    private final ReservaRepository repository;

    public ReservaService(ReservaRepository repository) {
        this.repository = repository;
    }

    public List<Reserva> listar() {
        return repository.findAll();
    }

    public void agregar(Reserva reserva) {
        repository.save(reserva);
    }

    public void eliminar(int index) {
        List<Reserva> lista = repository.findAll();
        if (index >= 0 && index < lista.size()) {
            repository.deleteById(lista.get(index).getId());
        }
    }
}