<?php

return [
    'enabled' => env('AD_AUTH_ENABLED', false),
    'host' => env('AD_HOST'),
    'base_dn' => env('AD_BASE_DN'),
    'bind_dn' => env('AD_BIND_DN'),
    'bind_password' => env('AD_BIND_PASSWORD'),
    'port' => env('AD_PORT', 389),
    'encryption' => env('AD_ENCRYPTION', ''),
    'tls_verify' => env('AD_TLS_VERIFY', true),
    'ca_cert' => env('AD_CA_CERT'),
    'login_attribute' => env('AD_LOGIN_ATTR', 'sAMAccountName'),
    'user_filter' => env('AD_USER_FILTER', '(objectClass=user)'),
    'match_field' => env('AD_MATCH_FIELD', 'email'),
    'auto_provision' => env('AD_AUTO_PROVISION', false),
    'sync_attributes' => env('AD_SYNC_ATTRIBUTES', false),
    'default_role' => env('AD_DEFAULT_ROLE', 'user'),
    'map' => [
        'user_name' => env('AD_MAP_USER_NAME'),
        'email' => env('AD_MAP_EMAIL', 'mail'),
        'first_name' => env('AD_MAP_FIRST_NAME', 'givenName'),
        'last_name' => env('AD_MAP_LAST_NAME', 'sn'),
    ],
];
