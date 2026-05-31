# 🚛 LogiTrans Express — Control de Proyecto
> **Sistema Web de Control de Rutas y Transporte de Carga**
> Examen Práctico de Desarrollo Web — Entrega: **13 de Junio**
> Modalidad: Individual

---

## 📋 Metadata del Proyecto

```yaml
proyecto: LogiTrans Express
tipo: Examen Práctico
modalidad: Individual
fecha_entrega: 2025-06-13
arquitectura: Microservicios
estado_general: EN PROGRESO
commits_requeridos_por_dia: 1 (mínimo por repositorio)
formato_commit: "NombreApellido: [Descripción de la tarea]"
```

---

## 🛠️ Stack Tecnológico

### Frontend
| Tecnología | Versión | Permitido |
|------------|---------|-----------|
| HTML5 | — | ✅ |
| CSS3 | — | ✅ |
| JavaScript Vanilla | — | ✅ |
| Angular / React / Vue / jQuery | — | ❌ Prohibido |

### Backend (por cada microservicio)
| Tecnología | Versión | Permitido |
|------------|---------|-----------|
| PHP | 8+ | ✅ |
| Slim Framework | — | ✅ |
| Eloquent ORM | — | ✅ |
| Composer | — | ✅ |
| Laravel / Symfony / CodeIgniter | — | ❌ Prohibido |

### Base de Datos
| Tecnología | Notas |
|------------|-------|
| MySQL | Entregada como insumo. No se evalúa diseño ni normalización. |

---

## 🗂️ Repositorios Requeridos

| Repositorio | Descripción | Estado |
|-------------|-------------|--------|
| `frontend-logistica` | Aplicación frontend | ⬜ Pendiente |
| `ms-auth` | Microservicio de autenticación | ⬜ Pendiente |
| `ms-conductores` | Microservicio de conductores | ⬜ Pendiente |
| `ms-vehiculos` | Microservicio de vehículos | ⬜ Pendiente |
| `ms-rutas` | Microservicio de rutas | ⬜ Pendiente |
| `ms-viajes` | Microservicio de viajes y seguimiento | ⬜ Pendiente |

> ⚠️ **Reglas Git:**
> - No subir carpeta `vendor/` ni `composer.lock`
> - Mínimo **1 commit por día** por repositorio
> - Formato: `NombreApellido: [mensaje]`

---

## 🏗️ Arquitectura General

```
┌─────────────────────────────────────────────────────┐
│                  FRONTEND (JS Vanilla)               │
│                  frontend-logistica                  │
└────┬──────┬──────┬──────┬──────┬────────────────────┘
     │      │      │      │      │
     ▼      ▼      ▼      ▼      ▼
  ms-auth  ms-conductores  ms-vehiculos  ms-rutas  ms-viajes
     │          │               │           │          │
   DB-auth  DB-conductores  DB-vehiculos  DB-rutas  DB-viajes
```

> **Regla:** El frontend es el único orquestador. No hay comunicación entre microservicios, ni API Gateway, ni mensajería.

---

## 📁 Estructura de Cada Microservicio (sugerida)

```
ms-<nombre>/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Middleware/
│   ├── Config/
│   └── Routes/
├── public/
│   └── index.php
├── vendor/          ← NO subir a Git
├── composer.json
└── .env             ← NO subir a Git
```

---

## ✅ Checklist General de Avance

 [x] Configuración inicial del proyecto (Slim + Eloquent)
- [ ] `POST /login` — Inicio de sesión con usuario/correo y contraseña
  - [ ] Validar credenciales
  - [ ] Generar token simple
  - [ ] Actualizar estado de sesión (`logged`, `session_active`)
  - [ ] Retornar token al frontend
  - [ ] Retornar error si credenciales incorrectas
- [ ] `POST /logout` — Cierre de sesión
  - [ ] Invalidar token en base de datos
- [ ] Middleware de validación de sesión (token + estado activo)
- [ ] Endpoint de validación de token para otros microservicios

---

### 👨‍✈️ ms-conductores — Gestión de Conductores
- [ ] Configuración inicial del proyecto
- [ ] Modelo `Conductor`
- [ ] Middleware de autenticación
- [ ] `POST /conductores` — Crear conductor
  - [ ] Nombres, apellidos, documento
  - [ ] Teléfono, correo
  - [ ] Número y categoría de licencia, fecha de vencimiento
  - [ ] Validar documento no duplicado
  - [ ] Validar licencia no duplicada
  - [ ] Validar fecha de vencimiento
- [ ] `PUT /conductores/{id}` — Editar conductor
  - [ ] Info personal, licencia, estado
- [ ] `GET /conductores` — Listar todos
- [ ] `GET /conductores?documento=` — Buscar por documento
- [ ] `GET /conductores?licencia=` — Buscar por licencia
- [ ] `GET /conductores?estado=` — Buscar por estado
- [ ] `PATCH /conductores/{id}/estado` — Cambiar estado
  - [ ] `disponible` / `en_ruta` / `inactivo`

---

### 🚛 ms-vehiculos — Gestión de Vehículos
- [ ] Configuración inicial del proyecto
- [ ] Modelo `Vehiculo`
- [ ] Middleware de autenticación
- [ ] `POST /vehiculos` — Crear vehículo
  - [ ] Placa, tipo, capacidad, modelo, marca, estado
  - [ ] Validar placa no duplicada
  - [ ] Validar capacidad > 0
- [ ] `PUT /vehiculos/{id}` — Editar vehículo
  - [ ] Capacidad, estado, info general
- [ ] `GET /vehiculos` — Listar todos
- [ ] `GET /vehiculos?placa=` — Buscar por placa
- [ ] `GET /vehiculos?estado=` — Buscar por estado
- [ ] `GET /vehiculos?tipo=` — Buscar por tipo
- [ ] Gestión de estados: `disponible` / `en_ruta` / `mantenimiento` / `inactivo`

---

### 🗺️ ms-rutas — Rutas y Programación de Viajes
- [ ] Configuración inicial del proyecto
- [ ] Modelos `Ruta` y `ProgramacionViaje`
- [ ] Middleware de autenticación

#### Rutas
- [ ] `POST /rutas` — Crear ruta
  - [ ] Ciudad origen, ciudad destino, distancia, tiempo estimado, observaciones
  - [ ] Validar ruta no duplicada
  - [ ] Validar distancia > 0
- [ ] `PUT /rutas/{id}` — Editar ruta (distancia, tiempo, observaciones)
- [ ] `GET /rutas` — Listar rutas
- [ ] `GET /rutas?ciudad=` — Buscar por ciudad
- [ ] `GET /rutas/{id}/tiempo` — Consultar tiempo estimado

#### Programación de Viajes
- [ ] `POST /programacion` — Programar viaje
  - [ ] Asignar conductor, vehículo, ruta
  - [ ] Fecha/hora de salida, fecha estimada de llegada, observaciones
  - [ ] Validar vehículo NO en mantenimiento
  - [ ] Validar conductor NO inactivo
  - [ ] Validar disponibilidad de conductor
  - [ ] Validar disponibilidad de vehículo
- [ ] `PUT /programacion/{id}` — Reprogramar viaje (fechas, conductor, vehículo, observaciones)
- [ ] `GET /programacion` — Listar viajes programados
- [ ] `GET /programacion?conductor=` — Buscar por conductor
- [ ] `GET /programacion?vehiculo=` — Buscar por vehículo
- [ ] `GET /programacion?estado=` — Buscar por estado
- [ ] `GET /programacion?fecha=` — Buscar por fecha

---

### 📍 ms-viajes — Seguimiento Operativo
- [ ] Configuración inicial del proyecto
- [ ] Modelos `Viaje` y `Novedad`
- [ ] Middleware de autenticación
- [ ] `POST /viajes/{id}/iniciar` — Iniciar viaje
  - [ ] Validar que no esté cancelado
  - [ ] Validar existencia de programación
- [ ] `PATCH /viajes/{id}/estado` — Actualizar estado
  - [ ] `programado` / `en_transito` / `retrasado` / `finalizado` / `cancelado`
- [ ] `POST /viajes/{id}/novedades` — Registrar novedad
  - [ ] Retrasos, incidentes, observaciones, cambios operativos
- [ ] `POST /viajes/{id}/finalizar` — Finalizar viaje
  - [ ] Validar que esté iniciado (no finalizar si no está en tránsito)
- [ ] `GET /viajes/{id}/historial` — Ver historial completo del viaje
- [ ] `GET /viajes/{id}/novedades` — Ver novedades registradas
- [ ] `GET /viajes/{id}/estados` — Ver estados del viaje

---

### 🖥️ frontend-logistica — Aplicación Frontend
- [ ] Configuración inicial (HTML, CSS, JS Vanilla)
- [ ] Manejo de token (almacenamiento en `localStorage` / `sessionStorage`)
- [ ] Módulo de autenticación
  - [ ] Pantalla de login
  - [ ] Logout
  - [ ] Redirección si no hay sesión activa
- [ ] Módulo de conductores
  - [ ] Listado
  - [ ] Formulario crear/editar
  - [ ] Búsqueda por documento / licencia / estado
  - [ ] Cambio de estado
- [ ] Módulo de vehículos
  - [ ] Listado
  - [ ] Formulario crear/editar
  - [ ] Búsqueda por placa / estado / tipo
- [ ] Módulo de rutas
  - [ ] Listado
  - [ ] Formulario crear/editar
  - [ ] Búsqueda por ciudad
- [ ] Módulo de programación de viajes
  - [ ] Programar viaje (selección de conductor, vehículo, ruta)
  - [ ] Reprogramar viaje
  - [ ] Listado con filtros
- [ ] Módulo de seguimiento de viajes
  - [ ] Iniciar viaje
  - [ ] Actualizar estado
  - [ ] Registrar novedad
  - [ ] Finalizar viaje
  - [ ] Ver historial y novedades
- [ ] Mensajes de éxito y error en formularios
- [ ] Validaciones básicas en cliente
- [ ] Estados visuales de viajes y programación

---

## 📦 Requerimientos Técnicos por Microservicio

Cada microservicio debe cumplir:

- [ ] Arquitectura REST
- [ ] Respuestas en formato JSON
- [ ] Uso de `Composer`
- [ ] Uso de `Slim Framework`
- [ ] Uso de `Eloquent ORM`
- [ ] Organización modular (Controllers / Models / Middleware / Config / Routes)
- [ ] Validación de token en cada endpoint protegido
- [ ] Al menos 1 commit por día en el repositorio

---

## 🔑 Autenticación — Notas de Implementación

```
NO se requiere JWT.
Se permite token simple almacenado en base de datos.

Campos sugeridos en tabla users:
- token          → string único generado al login
- logged         → boolean
- session_active → boolean

Flujo:
1. Login → genera token → guarda en DB → retorna al frontend
2. Frontend almacena token → lo envía en cada request (Header o Query param)
3. Middleware valida token existe en DB + session_active = true
4. Logout → limpia token y session_active en DB
```

---

## 📊 Criterios de Evaluación

| Criterio | Peso |
|----------|------|
| Desarrollo frontend | ⭐⭐⭐ |
| Desarrollo backend | ⭐⭐⭐ |
| Consumo de APIs REST | ⭐⭐⭐ |
| Arquitectura microservicios | ⭐⭐ |
| Integración frontend-backend | ⭐⭐⭐ |
| Uso de Slim Framework | ⭐⭐ |
| Uso de Eloquent ORM | ⭐⭐ |
| Manejo de autenticación | ⭐⭐ |
| Lógica logística y estados | ⭐⭐ |
| Organización y buenas prácticas | ⭐⭐ |
| Uso correcto de Git | ⭐⭐ |

> ⚠️ **El proyecto debe ser completamente funcional al momento de la entrega. Si no funciona, la nota es CERO.**

---

## 🗓️ Plan de Trabajo Sugerido (13 días)

| Día | Tarea Principal |
|-----|----------------|
| 1 | Configuración de los 6 repositorios. Estructura inicial de todos los microservicios. |
| 2 | ms-auth: login, logout, validación de token. |
| 3 | ms-conductores: CRUD completo + validaciones. |
| 4 | ms-vehiculos: CRUD completo + validaciones. |
| 5 | ms-rutas: CRUD de rutas. |
| 6 | ms-rutas: Programación de viajes + validaciones de disponibilidad. |
| 7 | ms-viajes: Iniciar, actualizar estado, novedades, finalizar. |
| 8 | Frontend: Login, layout base, navegación entre módulos. |
| 9 | Frontend: Módulos de conductores y vehículos. |
| 10 | Frontend: Módulos de rutas y programación. |
| 11 | Frontend: Módulo de seguimiento de viajes. |
| 12 | Integración completa y pruebas end-to-end. |
| 13 | Correcciones finales, revisión de commits, entrega. |

---

## 🤖 Contexto para IA Local (Monitoring Block)

```json
{
  "proyecto": "LogiTrans Express",
  "descripcion": "Sistema Web de Control de Rutas y Transporte de Carga bajo arquitectura de microservicios",
  "fecha_entrega": "2025-06-13",
  "tecnologias": {
    "frontend": ["HTML5", "CSS3", "JavaScript Vanilla"],
    "backend": ["PHP 8+", "Slim Framework", "Eloquent ORM", "Composer"],
    "base_datos": "MySQL",
    "prohibido": ["Laravel", "Symfony", "React", "Angular", "Vue", "jQuery"]
  },
  "microservicios": [
    { "nombre": "ms-auth", "responsabilidad": "Autenticacion y sesion" },
    { "nombre": "ms-conductores", "responsabilidad": "CRUD de conductores" },
    { "nombre": "ms-vehiculos", "responsabilidad": "CRUD de vehiculos" },
    { "nombre": "ms-rutas", "responsabilidad": "CRUD de rutas y programacion de viajes" },
    { "nombre": "ms-viajes", "responsabilidad": "Seguimiento operativo y estados de viajes" }
  ],
  "repositorios": [
    "frontend-logistica",
    "ms-auth",
    "ms-conductores",
    "ms-vehiculos",
    "ms-rutas",
    "ms-viajes"
  ],
  "reglas_git": {
    "commits_minimos_por_dia": 1,
    "formato_commit": "NombreApellido: [descripcion]",
    "archivos_excluidos": ["vendor/", "composer.lock", ".env"]
  },
  "autenticacion": {
    "tipo": "Token simple en base de datos",
    "jwt_requerido": false,
    "campos_tabla_users": ["token", "logged", "session_active"]
  },
  "comunicacion": {
    "patron": "Frontend se comunica directamente con cada microservicio",
    "api_gateway": false,
    "comunicacion_entre_microservicios": false
  },
  "nota_critica": "El proyecto debe ser completamente funcional al momento de la entrega. Si no funciona, la nota es CERO."
}
```

---

*Última actualización: inicio del proyecto*