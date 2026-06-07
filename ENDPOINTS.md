# Endpoints de los microservicios y consumo desde Postman

Este documento lista todos los endpoints disponibles en los microservicios del proyecto `LogiTransBackend` y cómo consumirlos desde Postman.

> Nota: Las direcciones base se configuran en cada microservicio con el puerto definido en `.env`.

## Bases locales

| Microservicio | URL base | Archivo `.env` | Protegido por token |
|---|---|---|---|
| `ms-auth` | `http://localhost:8001` | `ms-auth/.env` | no (login) / sí (logout, validate) |
| `ms-conductores` | `http://localhost:8002` | `ms-conductores/.env` | sí |
| `ms-vehiculos` | `http://localhost:8003` | `ms-vehiculos/.env` | sí |
| `ms-rutas` | `http://localhost:8004` | `ms-rutas/.env` | sí |
| `ms-viajes` | `http://localhost:8005` | `ms-viajes/.env` | sí |

## Cómo ejecutar en Postman

1. Crear una nueva colección en Postman.
2. Crear variables de entorno en Postman, por ejemplo:
   - `auth_url` = `http://localhost:8001`
   - `conductores_url` = `http://localhost:8002`
   - `vehiculos_url` = `http://localhost:8003`
   - `rutas_url` = `http://localhost:8004`
   - `viajes_url` = `http://localhost:8005`
   - `token` = ``
3. Usar `Content-Type: application/json` en los requests `POST`, `PUT`, `PATCH`.
4. Para endpoints protegidos, usar el header:

   `Authorization: Bearer {{token}}`

5. Primero realizar `POST /auth/login` en `ms-auth` para obtener el token.
6. Copiar el valor de `data.token` de la respuesta y pegarlo en la variable `token` de Postman.

## ms-auth

### Login
- Método: `POST`
- URL: `{{auth_url}}/auth/login`
- Cuerpo JSON:
```json
{
  "username": "usuario@example.com",
  "password": "123456"
}
```
- Respuesta esperada:
  - `data.token`
  - `data.user`

**Curl:**
```bash
curl -X POST http://localhost:8001/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "usuario@example.com",
    "password": "123456"
  }'
```

### Logout
- Método: `POST`
- URL: `{{auth_url}}/auth/logout`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X POST http://localhost:8001/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Validar token
- Método: `GET`
- URL: `{{auth_url}}/auth/validate`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X GET http://localhost:8001/auth/validate \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## ms-conductores

### Listar conductores
- Método: `GET`
- URL: `{{conductores_url}}/conductores`
- Headers:
  - `Authorization: Bearer {{token}}`
- Query params opcionales:
  - `estado`
  - `documento`
  - `licencia`
  - `search`

**Curl:**
```bash
curl -X GET "http://localhost:8002/conductores?estado=disponible" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Ver conductor por ID
- Método: `GET`
- URL: `{{conductores_url}}/conductores/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X GET http://localhost:8002/conductores/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Crear conductor
- Método: `POST`
- URL: `{{conductores_url}}/conductores`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON mínimo:
```json
{
  "nombres": "Juan",
  "apellidos": "Pérez",
  "documento": "12345678",
  "numero_licencia": "ABC12345"
}
```
- Otros campos opcionales:
  - `telefono`
  - `email`
  - `categoria_licencia`
  - `fecha_vencimiento_licencia` (YYYY-MM-DD)
  - `estado`

**Curl:**
```bash
curl -X POST http://localhost:8002/conductores \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "nombres": "Juan",
    "apellidos": "Pérez",
    "documento": "12345678",
    "numero_licencia": "ABC12345",
    "email": "juan@example.com",
    "telefono": "123456789",
    "categoria_licencia": "C"
  }'
```

### Actualizar conductor
- Método: `PUT`
- URL: `{{conductores_url}}/conductores/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON de ejemplo:
```json
{
  "telefono": "987654321",
  "email": "juan.perez@example.com",
  "estado": "disponible"
}
```

**Curl:**
```bash
curl -X PUT http://localhost:8002/conductores/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "telefono": "987654321",
    "email": "juan.perez@example.com"
  }'
```

### Cambiar estado de conductor
- Método: `PATCH`
- URL: `{{conductores_url}}/conductores/{id}/estado`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{
  "estado": "en_ruta"
}
```

**Curl:**
```bash
curl -X PATCH http://localhost:8002/conductores/1/estado \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "estado": "en_ruta"
  }'
```

### Eliminar conductor
- Método: `DELETE`
- URL: `{{conductores_url}}/conductores/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X DELETE http://localhost:8002/conductores/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## ms-vehiculos

### Listar vehículos
- Método: `GET`
- URL: `{{vehiculos_url}}/vehiculos`
- Headers:
  - `Authorization: Bearer {{token}}`
- Query params opcionales:
  - `estado`
  - `tipo`
  - `placa`
  - `search`

**Curl:**
```bash
curl -X GET "http://localhost:8003/vehiculos?estado=disponible" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Ver vehículo por ID
- Método: `GET`
- URL: `{{vehiculos_url}}/vehiculos/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X GET http://localhost:8003/vehiculos/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Crear vehículo
- Método: `POST`
- URL: `{{vehiculos_url}}/vehiculos`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{
  "placa": "ABC123",
  "tipo": "camión",
  "capacidad_carga": 5000,
  "marca": "Volvo",
  "modelo": "FH"
}
```
- Campo opcional:
  - `estado` (`disponible`, `en_ruta`, `mantenimiento`, `inactivo`)

**Curl:**
```bash
curl -X POST http://localhost:8003/vehiculos \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "placa": "ABC123",
    "tipo": "camión",
    "capacidad_carga": 5000,
    "marca": "Volvo",
    "modelo": "FH",
    "estado": "disponible"
  }'
```

### Actualizar vehículo
- Método: `PUT`
- URL: `{{vehiculos_url}}/vehiculos/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON de ejemplo:
```json
{
  "placa": "ABC123",
  "estado": "mantenimiento"
}
```

**Curl:**
```bash
curl -X PUT http://localhost:8003/vehiculos/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "placa": "ABC123",
    "estado": "mantenimiento"
  }'
```

### Cambiar estado de vehículo
- Método: `PATCH`
- URL: `{{vehiculos_url}}/vehiculos/{id}/estado`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{
  "estado": "disponible"
}
```

**Curl:**
```bash
curl -X PATCH http://localhost:8003/vehiculos/1/estado \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "estado": "disponible"
  }'
```

### Eliminar vehículo
- Método: `DELETE`
- URL: `{{vehiculos_url}}/vehiculos/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X DELETE http://localhost:8003/vehiculos/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## ms-rutas

### Rutas

#### Listar rutas
- Método: `GET`
- URL: `{{rutas_url}}/rutas`
- Headers:
  - `Authorization: Bearer {{token}}`
- Query params opcionales:
  - `ciudad`

**Curl:**
```bash
curl -X GET "http://localhost:8004/rutas?ciudad=Buenos" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Ver ruta por ID
- Método: `GET`
- URL: `{{rutas_url}}/rutas/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X GET http://localhost:8004/rutas/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Crear ruta
- Método: `POST`
- URL: `{{rutas_url}}/rutas`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{
  "ciudad_origen": "Buenos Aires",
  "ciudad_destino": "Rosario",
  "distancia": 300,
  "tiempo_estimado_horas": 4.5,
  "observaciones": "Ruta principal"
}
```

**Curl:**
```bash
curl -X POST http://localhost:8004/rutas \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "ciudad_origen": "Buenos Aires",
    "ciudad_destino": "Rosario",
    "distancia_km": 300,
    "tiempo_estimado_horas": 4.5,
    "observaciones": "Ruta principal"
  }'
```

#### Actualizar ruta
- Método: `PUT`
- URL: `{{rutas_url}}/rutas/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{  
  "distancia": 300,
  "tiempo_estimado_horas": 4.5  
}
```
**Curl:**
```bash
curl -X PUT http://localhost:8004/rutas/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "distancia_km": 310,
    "tiempo_estimado_horas": 4.7
  }'
```

#### Eliminar ruta
- Método: `DELETE`
- URL: `{{rutas_url}}/rutas/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X DELETE http://localhost:8004/rutas/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Programación

#### Listar programación
- Método: `GET`
- URL: `{{rutas_url}}/programacion`
- Headers:
  - `Authorization: Bearer {{token}}`
- Query params opcionales:
  - `conductor_id`
  - `vehiculo_id`
  - `estado`
  - `fecha`
  - `ruta_id`

**Curl:**
```bash
curl -X GET "http://localhost:8004/programacion?estado=programado" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Ver programación por ID
- Método: `GET`
- URL: `{{rutas_url}}/programacion/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X GET http://localhost:8004/programacion/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Crear programación
- Método: `POST`
- URL: `{{rutas_url}}/programacion`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{
  "conductor_id": 1,
  "vehiculo_id": 2,
  "ruta_id": 3,
  "fecha_salida": "2025-06-20",
  "hora_salida": "08:30",
  "fecha_estimada_llegada": "2025-06-20",
  "observaciones": "Carga general"
}
```

**Curl:**
```bash
curl -X POST http://localhost:8004/programacion \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "conductor_id": 1,
    "vehiculo_id": 2,
    "ruta_id": 3,
    "fecha_salida": "2025-06-20",
    "hora_salida": "08:30",
    "fecha_estimada_llegada": "2025-06-20",
    "observaciones": "Carga general"
  }'
```

#### Actualizar programación
- Método: `PUT`
- URL: `{{rutas_url}}/programacion/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`

**Curl:**
```bash
curl -X PUT http://localhost:8004/programacion/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "fecha_salida": "2025-06-21",
    "hora_salida": "09:00"
  }'
```

#### Eliminar programación
- Método: `DELETE`
- URL: `{{rutas_url}}/programacion/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X DELETE http://localhost:8004/programacion/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## ms-viajes

### Listar viajes
- Método: `GET`
- URL: `{{viajes_url}}/viajes`
- Headers:
  - `Authorization: Bearer {{token}}`
- Query params opcionales:
  - `estado`
  - `conductor_id`
  - `vehiculo_id`
  - `programacion_id`

**Curl:**
```bash
curl -X GET "http://localhost:8005/viajes?estado=en_transito" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Ver viaje por ID
- Método: `GET`
- URL: `{{viajes_url}}/viajes/{id}`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X GET http://localhost:8005/viajes/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Crear viaje
- Método: `POST`
- URL: `{{viajes_url}}/viajes`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{
  "programacion_id": 1,
  "conductor_id": 2,
  "vehiculo_id": 3,
  "ruta_id": 4,
  "observaciones": "Carga refrigerada"
}
```

**Curl:**
```bash
curl -X POST http://localhost:8005/viajes \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "programacion_id": 1,
    "conductor_id": 2,
    "vehiculo_id": 3,
    "ruta_id": 4,
    "observaciones": "Carga refrigerada"
  }'
```

### Iniciar viaje
- Método: `POST`
- URL: `{{viajes_url}}/viajes/{id}/iniciar`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X POST http://localhost:8005/viajes/1/iniciar \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Actualizar estado de viaje
- Método: `PATCH`
- URL: `{{viajes_url}}/viajes/{id}/estado`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{
  "estado": "retrasado"
}
```

**Curl:**
```bash
curl -X PATCH http://localhost:8005/viajes/1/estado \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "estado": "retrasado"
  }'
```

### Finalizar viaje
- Método: `POST`
- URL: `{{viajes_url}}/viajes/{id}/finalizar`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON opcional:
```json
{
  "observaciones": "Llegada con retraso por tráfico"
}
```

**Curl:**
```bash
curl -X POST http://localhost:8005/viajes/1/finalizar \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "observaciones": "Llegada con retraso por tráfico"
  }'
```

### Registrar novedad
- Método: `POST`
- URL: `{{viajes_url}}/viajes/{id}/novedades`
- Headers:
  - `Authorization: Bearer {{token}}`
  - `Content-Type: application/json`
- Cuerpo JSON:
```json
{
  "descripcion": "Neumático pinchado",
  "tipo": "incidente",
  "registrado_por": "Supervisor"
}
```
- Tipos válidos:
  - `observacion`
  - `retraso`
  - `incidente`

**Curl:**
```bash
curl -X POST http://localhost:8005/viajes/1/novedades \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "descripcion": "Neumático pinchado",
    "tipo": "incidente",
    "registrado_por": "Supervisor"
  }'
```

### Seguimiento del viaje
- Método: `GET`
- URL: `{{viajes_url}}/viajes/{id}/seguimiento`
- Headers:
  - `Authorization: Bearer {{token}}`

**Curl:**
```bash
curl -X GET http://localhost:8005/viajes/1/seguimiento \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Ejemplo de flujo de uso en Postman

1. Ejecutar `POST {{auth_url}}/auth/login`.
2. Copiar el token de la respuesta y guardarlo en `{{token}}`.
3. Probar `GET {{conductores_url}}/conductores`.
4. Probar `POST {{vehiculos_url}}/vehiculos`.
5. Probar `POST {{rutas_url}}/rutas`.
6. Probar `POST {{rutas_url}}/programacion`.
7. Probar `POST {{viajes_url}}/viajes`.
8. Probar `POST {{viajes_url}}/viajes/{id}/iniciar`.

## Consejos rápidos

- Siempre usar `Content-Type: application/json` en peticiones con body.
- Si recibes `401` o `403`, revisa el header `Authorization` y el token.
- Si tu microservicio no está en el puerto esperado, ajusta la variable de entorno de Postman.
- El login puede usar `username` o `email`.

## Notas sobre Curl en Windows (PowerShell)

Si ejecutas los comandos curl desde **PowerShell en Windows**, recuerda:

1. **Comillas simples vs dobles**: PowerShell trata las comillas de forma diferente. Usa comillas dobles para envolver todo:

```powershell
curl -X POST http://localhost:8001/auth/login `
  -H "Content-Type: application/json" `
  -d '{"username":"usuario@example.com","password":"123456"}'
```

2. **Escaping de caracteres**: Si necesitas caracteres especiales, escápalo con backtick `` ` ``:

```powershell
curl -X GET "http://localhost:8002/conductores?search=Juan%20Perez" `
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

3. **Variable con token**: Guardar el token en una variable:

```powershell
$token = "token_obtenido_del_login"
curl -X GET http://localhost:8001/auth/validate `
  -H "Authorization: Bearer $token"
```

4. **Body en varias líneas** (más legible):

```powershell
$body = @{
  nombres = "Juan"
  apellidos = "Pérez"
  documento = "12345678"
  numero_licencia = "ABC12345"
} | ConvertTo-Json

curl -X POST http://localhost:8002/conductores `
  -H "Authorization: Bearer $token" `
  -H "Content-Type: application/json" `
  -d $body
```

5. **Alternativa: Usar Invoke-WebRequest** (cmdlet nativo de PowerShell):

```powershell
$headers = @{
  "Authorization" = "Bearer $token"
  "Content-Type" = "application/json"
}

$body = @{
  nombres = "Juan"
  apellidos = "Pérez"
  documento = "12345678"
  numero_licencia = "ABC12345"
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://localhost:8002/conductores" `
  -Method POST `
  -Headers $headers `
  -Body $body
```
