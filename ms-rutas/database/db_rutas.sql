CREATE TABLE rutas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ciudad_origen VARCHAR(100) NOT NULL,
    ciudad_destino VARCHAR(100) NOT NULL,
    distancia DECIMAL(10,2) NOT NULL,
    tiempo_estimado VARCHAR(50) NOT NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE programaciones_viajes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    conductor_id BIGINT UNSIGNED NOT NULL,
    vehiculo_id BIGINT UNSIGNED NOT NULL,
    ruta_id BIGINT UNSIGNED NOT NULL,

    fecha_salida DATE NOT NULL,
    hora_salida TIME NOT NULL,
    fecha_estimada_llegada DATE NOT NULL,

    observaciones TEXT NULL,

    estado ENUM(
        'programado',
        'en_transito',
        'retrasado',
        'finalizado',
        'cancelado'
    ) DEFAULT 'programado',

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO rutas (
    ciudad_origen,
    ciudad_destino,
    distancia,
    tiempo_estimado,
    observaciones,
    created_at,
    updated_at
)
VALUES
(
    'Bogota',
    'Medellin',
    420,
    '8 horas',
    'Ruta principal nacional',
    NOW(),
    NOW()
),
(
    'Tunja',
    'Bogota',
    150,
    '3 horas',
    'Ruta regional',
    NOW(),
    NOW()
);

INSERT INTO programaciones_viajes (
    conductor_id,
    vehiculo_id,
    ruta_id,
    fecha_salida,
    hora_salida,
    fecha_estimada_llegada,
    observaciones,
    estado,
    created_at,
    updated_at
)
VALUES
(
    1,
    1,
    1,
    '2026-06-15',
    '06:00:00',
    '2026-06-15',
    'Carga de alimentos',
    'programado',
    NOW(),
    NOW()
);
