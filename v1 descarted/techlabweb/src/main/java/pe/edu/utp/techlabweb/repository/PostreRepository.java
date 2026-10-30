package pe.edu.utp.techlabweb.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import pe.edu.utp.techlabweb.model.Postre;

public interface PostreRepository extends JpaRepository<Postre, Long> {
}