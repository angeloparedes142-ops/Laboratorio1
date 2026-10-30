package pe.edu.utp.techlabweb.dto;

public record CoffeeDto(
    String nombre, 
    String descripcion, 
    String imagen, 
    String tipo, 
    String precioPeque, 
    String precioMediano, 
    String precioGrande
) {}