<?php
// Configure database credentials in Render > Environment, never in GitHub.
$host = getenv('DB_HOST') ?: 'localhost';
$port = (int) (getenv('DB_PORT') ?: 3306);
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$name = getenv('DB_NAME') ?: '4alerios';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = mysqli_init();
    $caPath = getenv('DB_SSL_CA_PATH');
    if ($caPath) {
        if (!is_readable($caPath)) {
            throw new RuntimeException('MySQL CA certificate file is missing.');
        }
        mysqli_ssl_set($conn, null, null, $caPath, null, null);
        $conn->real_connect($host, $user, $pass, $name, $port, null, MYSQLI_CLIENT_SSL);
    } else {
        $conn->real_connect($host, $user, $pass, $name, $port);
    }
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    error_log('Database connection error: ' . $e->getMessage());
    http_response_code(503);
    exit('Database connection unavailable. Please contact the administrator.');
}
