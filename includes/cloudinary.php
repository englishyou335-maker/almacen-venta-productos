<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/** Lee una variable de entorno suelta como cadena limpia. */
function cloudinaryValorDirecto(string $clave): string
{
    $valor = getenv($clave);
    return $valor === false ? '' : $valor;
}

/** Interpreta cloudinary://API_KEY:API_SECRET@CLOUD_NAME, incluso con comillas o prefijo. */
function cloudinaryParsearUrl(string $url): ?array
{
    $url = trim(trim($url), "\"'");
    if (stripos($url, 'CLOUDINARY_URL=') === 0) {
        $url = substr($url, strlen('CLOUDINARY_URL='));
    }
    if ($url === '') {
        return null;
    }

    $partes = parse_url($url);
    $cloud = is_array($partes) ? (string) ($partes['host'] ?? '') : '';
    $key = is_array($partes) ? (string) ($partes['user'] ?? '') : '';
    $secret = is_array($partes) ? (string) ($partes['pass'] ?? '') : '';

    if ($cloud === '' || $key === '' || $secret === '') {
        $sinEsquema = preg_replace('/^[a-z][a-z0-9+.-]*:\/\//i', '', $url) ?? '';
        $arrobas = strrpos($sinEsquema, '@');
        if ($arrobas !== false) {
            $credenciales = substr($sinEsquema, 0, $arrobas);
            $cloud = explode('/', substr($sinEsquema, $arrobas + 1))[0];
            $dosPuntos = strpos($credenciales, ':');
            if ($dosPuntos !== false) {
                $key = substr($credenciales, 0, $dosPuntos);
                $secret = substr($credenciales, $dosPuntos + 1);
            }
        }
    }

    $cloud = rawurldecode($cloud);
    $key = rawurldecode($key);
    $secret = rawurldecode($secret);
    if ($cloud === '' || $key === '' || $secret === '') {
        return null;
    }

    return [
        'CLOUDINARY_CLOUD_NAME' => $cloud,
        'CLOUDINARY_API_KEY'    => $key,
        'CLOUDINARY_API_SECRET' => $secret,
    ];
}

/**
 * Prioridad: cadena guardada desde el panel, CLOUDINARY_URL del entorno,
 * y finalmente las tres variables separadas (despliegues anteriores).
 * En Render, donde la variable a veces no llega a PHP, la cadena de la BD
 * permite seguir subiendo imágenes sin tocar el servidor.
 */
function cloudinaryCredenciales(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $guardada = settingObtener('cloudinary_url');
    if ($guardada !== '') {
        $desdeBd = cloudinaryParsearUrl($guardada);
        if ($desdeBd !== null) {
            $desdeBd['origen'] = 'base-de-datos';
            return $cache = $desdeBd;
        }
    }

    $desdeEntorno = cloudinaryParsearUrl(cloudinaryValorDirecto('CLOUDINARY_URL'));
    if ($desdeEntorno !== null) {
        $desdeEntorno['origen'] = 'variable-de-entorno';
        return $cache = $desdeEntorno;
    }

    $separadas = [
        'CLOUDINARY_CLOUD_NAME' => cloudinaryValorDirecto('CLOUDINARY_CLOUD_NAME'),
        'CLOUDINARY_API_KEY'    => cloudinaryValorDirecto('CLOUDINARY_API_KEY'),
        'CLOUDINARY_API_SECRET' => cloudinaryValorDirecto('CLOUDINARY_API_SECRET'),
        'origen'                => 'variables-separadas',
    ];
    if ($separadas['CLOUDINARY_CLOUD_NAME'] !== ''
        && $separadas['CLOUDINARY_API_KEY'] !== ''
        && $separadas['CLOUDINARY_API_SECRET'] !== '') {
        return $cache = $separadas;
    }

    return $cache = [
        'CLOUDINARY_CLOUD_NAME' => '',
        'CLOUDINARY_API_KEY'    => '',
        'CLOUDINARY_API_SECRET' => '',
        'origen'                => 'sin-configurar',
    ];
}

/** Devuelve una credencial de Cloudinary ya resuelta. */
function cloudinaryValor(string $clave): string
{
    $credenciales = cloudinaryCredenciales();
    return $credenciales[$clave] ?? '';
}

/** ¿Hay credenciales suficientes para subir imágenes? */
function cloudinaryConfigurado(): bool
{
    return cloudinaryValor('CLOUDINARY_CLOUD_NAME') !== ''
        && cloudinaryValor('CLOUDINARY_API_KEY') !== ''
        && cloudinaryValor('CLOUDINARY_API_SECRET') !== '';
}

/** Estado para el panel: nunca devuelve secretos, solo la clave enmascarada. */
function cloudinaryDiagnostico(): array
{
    $credenciales = cloudinaryCredenciales();
    $key = (string) ($credenciales['CLOUDINARY_API_KEY'] ?? '');
    $enmascarada = $key !== ''
        ? substr($key, 0, 4) . '••••' . substr($key, -2)
        : '—';

    return [
        'configurado' => cloudinaryConfigurado(),
        'origen'      => (string) ($credenciales['origen'] ?? 'sin-configurar'),
        'cloud'       => (string) ($credenciales['CLOUDINARY_CLOUD_NAME'] ?? ''),
        'api_key'     => $enmascarada,
    ];
}

/**
 * Sube una imagen a Cloudinary con autenticación Basic (la firma SHA1 de esta
 * cuenta no es aceptada). No necesita Composer, solo cURL.
 * Devuelve ['ok'=>bool, 'url'=>..., 'public_id'=>...] o ['ok'=>false,'error'=>...].
 */
function cloudinarySubir(array $archivo, string $carpeta = 'productos'): array
{
    $carpeta = preg_replace('/[^a-zA-Z0-9_-]/', '', $carpeta) ?: 'productos';
    if (!cloudinaryConfigurado()) {
        return ['ok' => false, 'error' => 'Cloudinary no está configurado en este servidor. Revisa el estado en /admin/qr-pago.php.'];
    }
    if (!isset($archivo['tmp_name'], $archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'No se recibió ninguna imagen válida.'];
    }
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'La extensión curl de PHP no está disponible.'];
    }

    $tipos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($archivo['tmp_name']);
    if (!isset($tipos[$mime])) {
        return ['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.'];
    }
    if ((int) $archivo['size'] > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'La imagen supera los 5 MB.'];
    }

    $parametros = [
        'public_id' => $carpeta . '/' . bin2hex(random_bytes(10)),
        'timestamp' => time(),
        'overwrite' => 'true',
    ];

    $url = 'https://api.cloudinary.com/v1_1/'
        . rawurlencode(cloudinaryValor('CLOUDINARY_CLOUD_NAME')) . '/image/upload';
    $parametros['file'] = new CURLFile($archivo['tmp_name'], $mime, 'imagen.' . $tipos[$mime]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $parametros,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_USERPWD        => cloudinaryValor('CLOUDINARY_API_KEY') . ':' . cloudinaryValor('CLOUDINARY_API_SECRET'),
    ]);
    $respuesta = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($respuesta === false) {
        return ['ok' => false, 'error' => 'Error de red al subir la imagen: ' . $error];
    }
    $json = json_decode($respuesta, true);
    if (!is_array($json) || !isset($json['secure_url'], $json['public_id'])) {
        return ['ok' => false, 'error' => $json['error']['message'] ?? 'Respuesta inesperada de Cloudinary.'];
    }
    return [
        'ok'        => true,
        'url'       => (string) $json['secure_url'],
        'public_id' => (string) $json['public_id'],
    ];
}

/** Borra una imagen de Cloudinary por su public_id. */
function cloudinaryEliminar(string $publicId): bool
{
    if ($publicId === '' || !cloudinaryConfigurado() || !function_exists('curl_init')) {
        return false;
    }
    $parametros = ['public_id' => $publicId, 'timestamp' => time()];

    $url = 'https://api.cloudinary.com/v1_1/'
        . rawurlencode(cloudinaryValor('CLOUDINARY_CLOUD_NAME')) . '/image/destroy';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $parametros,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_USERPWD        => cloudinaryValor('CLOUDINARY_API_KEY') . ':' . cloudinaryValor('CLOUDINARY_API_SECRET'),
    ]);
    $respuesta = curl_exec($ch);
    curl_close($ch);
    $json = is_string($respuesta) ? json_decode($respuesta, true) : null;
    return is_array($json) && isset($json['result']) && $json['result'] === 'ok';
}
