<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/** Único canal de contacto: WhatsApp del administrador. */
define('WHATSAPP_ADMIN_URL', 'https://wa.me/59162585878');
define('WHATSAPP_ADMIN_NUMERO', '+591 62585878');

/** Inicia la sesión una sola vez, con cookies seguras. */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Escapa salida para evitar XSS. */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Token CSRF de la sesión (se crea si no existe). */
function csrfToken(): string
{
    iniciarSesion();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Campo hidden para formularios HTML clásicos. */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/** Comparación en tiempo constante. */
function csrfValidar(?string $token): bool
{
    iniciarSesion();
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

/** Lee el token de la cabecera X-CSRF-Token o del cuerpo JSON. */
function csrfTokenPeticion(): ?string
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if ($token === null) {
        $body = leerJson();
        $token = $body['csrf_token'] ?? null;
    }
    return is_string($token) ? $token : null;
}

/** Cuerpo JSON de la petición (se cachea para no leer php://input dos veces). */
function leerJson(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $raw = file_get_contents('php://input');
    $datos = json_decode($raw === false ? '' : $raw, true);
    return $cache = is_array($datos) ? $datos : [];
}

/** Método HTTP en mayúsculas. */
function metodo(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

/** Respuesta estándar de la API. Termina la petición. */
function responder(bool $ok, array $data = [], string $message = '', array $errors = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    $payload = $ok
        ? ['success' => true, 'data' => $data, 'message' => $message]
        : ['success' => false, 'errors' => $errors];
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Valida una fecha YYYY-MM-DD y que no sea pasada. */
function esFechaFutura(string $fecha): bool
{
    $f = DateTime::createFromFormat('!Y-m-d', $fecha);
    if ($f === false || $f->format('Y-m-d') !== $fecha) {
        return false;
    }
    return $f >= new DateTime('today');
}

/** Guarda un mensaje para mostrar tras un redirect (patrón PRG). */
function flashSet(string $mensaje, string $tipo = 'success'): void
{
    iniciarSesion();
    $_SESSION['flash'] = ['mensaje' => $mensaje, 'tipo' => $tipo];
}

/** Devuelve y borra el mensaje pendiente. */
function flashLeer(): array
{
    iniciarSesion();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : [];
}

/**
 * Precio para el catálogo público. Mientras no se cargue el precio real
 * (seed con price = 0.00) se muestra "A consultar" en lugar de un Bs 0.00 engañoso.
 */
function etiquetaPrecio($valor): string
{
    $precio = (float) $valor;
    return $precio > 0 ? 'Bs ' . number_format($precio, 2) : 'A consultar';
}

/** Disponibilidad legible para el catálogo público. */
function etiquetaDisponibilidad($stock): string
{
    return (int) $stock > 0 ? 'Consultar disponibilidad' : 'Agotado temporalmente';
}

/** Lee un valor global. Si la tabla aún no existe, devuelve el valor por defecto. */
function settingObtener(string $clave, string $porDefecto = ''): string
{
    try {
        $stmt = db()->prepare('SELECT valor FROM settings WHERE setting_key = :clave LIMIT 1');
        $stmt->execute([':clave' => $clave]);
        $valor = $stmt->fetchColumn();
        return $valor === false || $valor === null ? $porDefecto : (string) $valor;
    } catch (Throwable $e) {
        return $porDefecto;
    }
}

/** Inserta o actualiza un valor global (marcadores distintos para PDO nativo). */
function settingGuardar(string $clave, string $valor): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (setting_key, valor) VALUES (:clave, :valor)
         ON DUPLICATE KEY UPDATE valor = :valor_nuevo'
    );
    $stmt->execute([':clave' => $clave, ':valor' => $valor, ':valor_nuevo' => $valor]);
}

function settingEliminar(string $clave): void
{
    $stmt = db()->prepare('DELETE FROM settings WHERE setting_key = :clave');
    $stmt->execute([':clave' => $clave]);
}

/** Configuración actual del QR de pago subido desde el panel. */
function qrPago(): array
{
    return [
        'url'       => settingObtener('qr_pago_url'),
        'public_id' => settingObtener('qr_pago_public_id'),
    ];
}

/** URL de Cloudinary que fuerza la descarga con nombre de archivo. */
function urlDescargaCloudinary(string $url): string
{
    return str_replace('/image/upload/', '/image/upload/fl_attachment/qr-pago/', $url);
}
