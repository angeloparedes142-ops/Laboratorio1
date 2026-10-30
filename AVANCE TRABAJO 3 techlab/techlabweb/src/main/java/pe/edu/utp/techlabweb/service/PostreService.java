package pe.edu.utp.techlabweb.service;

import org.springframework.stereotype.Service;
import pe.edu.utp.techlabweb.model.Postre;
import pe.edu.utp.techlabweb.repository.PostreRepository;

import java.util.List;

@Service
public class PostreService {

    private final PostreRepository repo;

    public PostreService(PostreRepository repo) {
        this.repo = repo;
    }

    public List<Postre> listar() { return repo.findAll(); }
    public Postre obtener(Long id) { return repo.findById(id).orElse(null); }
    public Postre guardar(Postre p) { return repo.save(p); }   // crea o actualiza
    public void eliminar(Long id) { repo.deleteById(id); }
}
