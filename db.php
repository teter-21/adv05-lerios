
<?php
// Database connection for Render + Aiven MySQL

$host = getenv('DB_HOST') ?: '';
$port = (int) (getenv('DB_PORT') ?: 3306);
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASSWORD');
$name = getenv('DB_NAME') ?: 'defaultdb';

$caPath = getenv('DB_SSL_CA_PATH') ?: '/etc/secrets/ca.pem';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (
        $host === '' ||
        $user === '' ||
        $pass === false ||
        $name === ''
    ) {
        throw new RuntimeException(
            'Missing database environment variables.'
        );
    }

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

    $conn = mysqli_init();

    // Verify the Aiven MySQL TLS certificate.
    $conn->options(
        MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,
        true
    );

    $conn->ssl_set(
        null,
        null,
        $caPath,
        null,
        null
    );

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

} catch (Throwable $e) {
    error_log(
        'Database connection error: ' . $e->getMessage()
    );

    http_response_code(503);

    exit(
        'Database connection unavailable. Please contact the administrator.'
    );
}
