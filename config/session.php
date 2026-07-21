<?php

return [
    'name'     => $_ENV['SESSION_NAME'] ?? 'rms_session',
    'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120), // minutes
    'secure'   => ($_ENV['APP_ENV'] ?? 'production') === 'production',
    'httponly' => true,
    'samesite' => 'Lax',
];
