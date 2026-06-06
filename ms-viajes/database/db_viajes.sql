CREATE TABLE seguimientos_viajes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    programacion_viaje_id BIGINT UNSIGNED NOT NULL,

    fecha DATE NOT NULL,
    hora TIME NOT NULL,

    estado ENUM(
        'programado',
        'en_transito',
        'retrasado',
        'finalizado',
        'cancelado'
    ) NOT NULL,

    novedad TEXT NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO seguimientos_viajes (
    programacion_viaje_id,
    fecha,
    hora,
    estado,
    novedad,
    created_at,
    updated_at
)
VALUES
(
    1,
    '2026-06-15',
    '06:00:00',
    'en_transito',
    'Vehiculo inicia recorrido hacia Medellin',
    NOW(),
    NOW()
),
(
    1,
    '2026-06-15',
    '10:30:00',
    'retrasado',
    'Retraso por trafico en carretera',
    NOW(),
    NOW()
);
