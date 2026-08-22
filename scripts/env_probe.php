<?php

foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'API_BASE_URL', 'MOCKSERVER_BASE_URL'] as $key) {
    printf("%s=%s\n", $key, var_export(getenv($key), true));
}
