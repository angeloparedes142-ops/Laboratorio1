package main.java.pe.edu.utp.techlabweb.repository;

import org.springframework.data.jpa.repository.JpaRepository;

import pe.edu.utp.techlabweb.dto.Reserva;

public interface ReservaRepository extends JpaRepository<Reserva, Long> {
}