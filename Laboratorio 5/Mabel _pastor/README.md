# TechLab Web - Semana 5

## Descripción
Propósito del proyecto y continuidad con el APF1. Migración del portal TechLab aprobado en el APFI.

## Requisitos
JDK 25 y conexión inicial para dependencias.

## Ejecución
*   **Windows:** `.\mvnw.cmd spring-boot:run`
*   **macOS/Linux:** `./mvnw spring-boot:run`

## Pruebas
Ejecutar `mvnw test`. Resultado esperado: BUILD SUCCESS.

## Empaquetado
Comando: `mvnw clean package`
Nombre del JAR: `target/techlab-web-0.0.1-SNAPSHOT.jar`

## Perfiles
*   Puerto base: 8080 (`application.properties`)
*   Puerto local: 8081 (`application-local.properties`)
*   Activación: `java -jar target/techlab-web-0.0.1-SNAPSHOT.jar --spring.profiles.active=local`
*   El entorno local es de desarrollo y no contiene secretos.

## Alcance
Frontend estático; sin controladores, base de datos ni seguridad.
