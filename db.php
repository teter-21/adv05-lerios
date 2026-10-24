
<?php
// Database configuration from Render Environment Variables

$host = getenv('DB_HOST') ?: '';
$port = (int) (getenv('DB_PORT') ?: 3306);
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASSWORD');
$name = getenv('DB_NAME') ?: 'defaultdb';

// Default Render Secret File location
$caPath = getenv('DB_SSL_CA_PATH') ?: '/etc/secrets/ca.pem';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Validate database configuration
    if (!$host || !$user || $pass === false || !$name) {
        throw new RuntimeException(
            'Missing database environment variables.'
        );
    }

    // Diagnose certificate location
    error_log('MySQL CA path: ' . $caPath);

    if (!is_file($caPath)) {
        throw new RuntimeException(
            'MySQL CA certificate does not exist at: ' . $caPath
        );
    }

    if (!is_readable($caPath)) {
        throw new RuntimeException(
            'MySQL CA certificate cannot be read at: ' . $caPath
        );
    }

    // Initialize MySQLi
    $conn = mysqli_init();

    // Enforce certificate verification
    $conn->options(
        MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,
        true
    );

    // Configure TLS using Aiven CA certificate
    $conn->ssl_set(
        null,
        null,
        $caPath,
        null,
        null
    );

    // Connect to Aiven MySQL
    $conn->real_connect(
        $host,
        $user,
        $pass,
        $name,
        $port,
        null,
        MYSQLI_CLIENT_SSL
    );

    $conn->set_charset('utf8mb4');

    error_log('MySQL connection successful.');

} catch (Throwable $e) {
    error_log(
        'Database connection error: ' . $e->getMessage()
    );

    http_response_code(503);

    exit(
        'Database connection unavailable. Please contact the administrator.'
    );
}
