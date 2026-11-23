-- Ejecutar UNA vez en la base de datos (local y producción) antes de usar
-- la página /admin/qr-pago.php. Seguro si la tabla ya existe.
CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
  valor TEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
