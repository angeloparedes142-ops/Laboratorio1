package pe.edu.utp.techlabweb.model;

import jakarta.persistence.*;

@Entity
@Table(name = "postres")
public class Postre {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, length = 100)
    private String nombre;

    @Column(length = 255)
    private String descripcion;

    @Column(nullable = false)
    private Double precio;

    private String imagen;          // ej: cheesecake.jpg
    private String ingredientes;
    private Integer calorias;
    private String tamano;          // ej: 200g
    private String disponibilidad;  // Disponible / Limitado / Agotado

    public Postre() {}

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public String getNombre() { return nombre; }
    public void setNombre(String nombre) { this.nombre = nombre; }
    public String getDescripcion() { return descripcion; }
    public void setDescripcion(String descripcion) { this.descripcion = descripcion; }
    public Double getPrecio() { return precio; }
    public void setPrecio(Double precio) { this.precio = precio; }
    public String getImagen() { return imagen; }
    public void setImagen(String imagen) { this.imagen = imagen; }
    public String getIngredientes() { return ingredientes; }
    public void setIngredientes(String ingredientes) { this.ingredientes = ingredientes; }
    public Integer getCalorias() { return calorias; }
    public void setCalorias(Integer calorias) { this.calorias = calorias; }
    public String getTamano() { return tamano; }
    public void setTamano(String tamano) { this.tamano = tamano; }
    public String getDisponibilidad() { return disponibilidad; }
    public void setDisponibilidad(String disponibilidad) { this.disponibilidad = disponibilidad; }
}
