```java
package pe.edu.utp.techlabweb.controller;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;

import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.pdmodel.PDPage;
import org.apache.pdfbox.pdmodel.PDPageContentStream;
import org.apache.pdfbox.pdmodel.font.PDType1Font;
import org.apache.pdfbox.pdmodel.font.Standard14Fonts;

import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.io.ByteArrayOutputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.*;

@RestController
@RequestMapping("/api/pedidos")
public class PedidosController {

    private final ObjectMapper mapper = new ObjectMapper();

    private final Path archivo =
            Path.of("data", "pedidos.json");

    // REGISTRAR PEDIDO
    @PostMapping
    public synchronized Map<String, Object> registrar(
            @RequestBody Map<String, Object> pedido)
            throws Exception {

        Object numero = pedido.get("numero");

        if (numero == null || pedido.get("productos") == null) {
            throw new IllegalArgumentException(
                    "El pedido debe incluir número y productos.");
        }

        List<Map<String, Object>> pedidos = leerPedidos();

        boolean existe = pedidos.stream()
                .anyMatch(p -> Objects.equals(
                        String.valueOf(p.get("numero")),
                        String.valueOf(numero)));

        if (!existe) {
            pedidos.add(new LinkedHashMap<>(pedido));
        }

        Files.createDirectories(archivo.getParent());

        mapper.writerWithDefaultPrettyPrinter()
                .writeValue(archivo.toFile(), pedidos);

        Map<String, Object> respuesta = new LinkedHashMap<>();
        respuesta.put("ok", true);
        respuesta.put("numero", numero);
        respuesta.put("mensaje", "Pedido guardado correctamente");

        return respuesta;
    }

    // LISTAR PEDIDOS REGISTRADOS
    @GetMapping
    public synchronized List<Map<String, Object>> listar()
            throws Exception {

        return leerPedidos();
    }

    // GENERAR Y DESCARGAR BOLETA PDF
    @GetMapping(
            value = "/{numero}/boleta.pdf",
            produces = MediaType.APPLICATION_PDF_VALUE
    )
    public synchronized ResponseEntity<byte[]> boleta(
            @PathVariable String numero)
            throws Exception {

        Map<String, Object> pedido = leerPedidos().stream()
                .filter(p -> Objects.equals(
                        String.valueOf(p.get("numero")),
                        numero))
                .findFirst()
                .orElse(null);

        if (pedido == null) {
            return ResponseEntity.notFound().build();
        }

        byte[] pdf = crearPdf(pedido);

        return ResponseEntity.ok()
                .header(
                        HttpHeaders.CONTENT_DISPOSITION,
                        "attachment; filename=boleta-" + numero + ".pdf"
                )
                .contentType(MediaType.APPLICATION_PDF)
                .body(pdf);
    }

    // LEER LOS PEDIDOS ALMACENADOS
    private List<Map<String, Object>> leerPedidos()
            throws Exception {

        if (!Files.exists(archivo)) {
            return new ArrayList<>();
        }

        return mapper.readValue(
                archivo.toFile(),
                new TypeReference<List<Map<String, Object>>>() {}
        );
    }

    // CONSTRUIR LA BOLETA PDF
    private byte[] crearPdf(Map<String, Object> pedido)
            throws Exception {

        try (
                PDDocument documento = new PDDocument();
                ByteArrayOutputStream salida =
                        new ByteArrayOutputStream()
        ) {

            PDPage pagina = new PDPage();
            documento.addPage(pagina);

            try (
                    PDPageContentStream contenido =
                            new PDPageContentStream(documento, pagina)
            ) {

                PDType1Font negrita = new PDType1Font(
                        Standard14Fonts.FontName.HELVETICA_BOLD);

                PDType1Font normal = new PDType1Font(
                        Standard14Fonts.FontName.HELVETICA);

                float y = 750;

                y = linea(
                        contenido, negrita, 17, 55, y,
                        "TECHLAB - BOLETA DE VENTA"
                );

                y -= 8;

                y = linea(
                        contenido, normal, 9, 55, y,
                        "Documento interno; no es comprobante electronico SUNAT."
                );

                y -= 15;

                y = linea(
                        contenido, negrita, 12, 55, y,
                        "Pedido: " + seguro(pedido.get("numero"))
                );

                y = linea(
                        contenido, normal, 10, 55, y,
                        "Fecha: " + fecha(pedido.get("fecha"))
                );

                y = linea(
                        contenido, normal, 10, 55, y,
                        "Cliente: " + seguro(pedido.get("cliente"))
                );

                y = linea(
                        contenido, normal, 10, 55, y,
                        "Telefono: " + seguro(pedido.get("telefono"))
                );

                y = linea(
                        contenido, normal, 10, 55, y,
                        "Tipo de pedido: " +
                                seguro(pedido.get("tipoPedido"))
                );

                y = linea(
                        contenido, normal, 10, 55, y,
                        "Forma de pago: " + seguro(pedido.get("pago"))
                );

                String direccion = seguro(pedido.get("direccion"));

                if (!direccion.isBlank()) {
                    y = linea(
                            contenido, normal, 10, 55, y,
                            "Direccion: " + direccion
                    );
                }

                y -= 15;

                y = linea(
                        contenido, negrita, 10, 55, y,
                        "PRODUCTO"
                );

                linea(
                        contenido, negrita, 9, 315, y + 13,
                        "CANT."
                );

                linea(
                        contenido, negrita, 9, 365, y + 13,
                        "PRECIO"
                );

                linea(
                        contenido, negrita, 9, 455, y + 13,
                        "IMPORTE"
                );

                Object productos = pedido.get("productos");

                if (productos instanceof List<?> lista) {

                    for (Object objeto : lista) {

                        if (!(objeto instanceof Map<?, ?> item)) {
                            continue;
                        }

                        String nombre = seguro(item.get("name"));

                        if (nombre.length() > 36) {
                            nombre = nombre.substring(0, 36);
                        }

                        double cantidad = numero(item.get("cantidad"));
                        double precio = numero(item.get("price"));

                        y = linea(
                                contenido, normal, 9, 55, y,
                                nombre
                        );

                        linea(
                                contenido, normal, 9, 320, y + 13,
                                String.valueOf((int) cantidad)
                        );

                        linea(
                                contenido, normal, 9, 365, y + 13,
                                soles(precio)
                        );

                        linea(
                                contenido, normal, 9, 455, y + 13,
                                soles(precio * cantidad)
                        );

                        y -= 4;

                        if (y < 150) {
                            break;
                        }
                    }
                }

                y -= 15;

                y = linea(
                        contenido, normal, 10, 330, y,
                        "Subtotal: " + soles(numero(pedido.get("subtotal")))
                );

                y = linea(
                        contenido, normal, 10, 330, y,
                        "Delivery: " + soles(numero(pedido.get("delivery")))
                );

                y -= 4;

                y = linea(
                        contenido, negrita, 14, 330, y,
                        "TOTAL: " + soles(numero(pedido.get("total")))
                );

                y -= 25;

                linea(
                        contenido, normal, 10, 55, y,
                        "Gracias por su compra."
                );
            }

            documento.save(salida);

            return salida.toByteArray();
        }
    }

    // ESCRIBIR UNA LINEA EN EL PDF
    private float linea(
            PDPageContentStream contenido,
            PDType1Font fuente,
            int tamano,
            float x,
            float y,
            String texto
    ) throws Exception {

        contenido.beginText();
        contenido.setFont(fuente, tamano);
        contenido.newLineAtOffset(x, y);
        contenido.showText(limpiar(texto));
        contenido.endText();

        return y - tamano - 7;
    }

    // EVITAR CARACTERES NO SOPORTADOS POR HELVETICA
    private String limpiar(String texto) {

        return texto.replaceAll(
                "[^\\x20-\\x7E\\xA0-\\xFF]",
                "?"
        );
    }

    private String seguro(Object valor) {
        return valor == null ? "" : String.valueOf(valor);
    }

    private double numero(Object valor) {

        try {
            return Double.parseDouble(String.valueOf(valor));
        } catch (Exception e) {
            return 0;
        }
    }

    private String soles(double cantidad) {
        return String.format(Locale.US, "S/ %.2f", cantidad);
    }

    private String fecha(Object valor) {

        try {
            return LocalDateTime.parse(
                    String.valueOf(valor),
                    DateTimeFormatter.ISO_DATE_TIME
            ).format(
                    DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm")
            );

        } catch (Exception e) {
            return seguro(valor);
        }
    }
}
```
