<?php

namespace App\Plugins\ActiveDirectoryAuth;

use App\Plugins\ActiveDirectoryAuth\Models\ActiveDirectorySetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SettingsRepository
{
    private ?array $cached = null;

    public function all(): array
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $defaults = config('active_directory_auth', []);
        if (!Schema::hasTable('active_directory_settings')) {
            return $this->cached = $defaults;
        }

        $settings = ActiveDirectorySetting::query()->first();
        if (!$settings) {
            return $this->cached = $defaults;
        }

        $mapDefaults = Arr::get($defaults, 'map', []);

        $this->cached = [
            'enabled' => $settings->enabled === null
                ? (bool) Arr::get($defaults, 'enabled', false)
                : (bool) $settings->enabled,
            'host' => $settings->host,
            'base_dn' => $settings->base_dn,
            'bind_dn' => $settings->bind_dn,
            'bind_password' => $settings->bind_password,
            'port' => $settings->port ?? Arr::get($defaults, 'port', 389),
            'encryption' => $settings->encryption ?? Arr::get($defaults, 'encryption', ''),
            'tls_verify' => $settings->tls_verify === null
                ? (bool) Arr::get($defaults, 'tls_verify', true)
                : (bool) $settings->tls_verify,
            'ca_cert' => $settings->ca_cert,
            'login_attribute' => $settings->login_attribute ?? Arr::get($defaults, 'login_attribute', 'sAMAccountName'),
            'user_filter' => $settings->user_filter ?? Arr::get($defaults, 'user_filter', '(objectClass=user)'),
            'match_field' => $settings->match_field ?? Arr::get($defaults, 'match_field', 'email'),
            'auto_provision' => $settings->auto_provision === null
                ? (bool) Arr::get($defaults, 'auto_provision', false)
                : (bool) $settings->auto_provision,
            'sync_attributes' => $settings->sync_attributes === null
                ? (bool) Arr::get($defaults, 'sync_attributes', false)
                : (bool) $settings->sync_attributes,
            'default_role' => $settings->default_role ?? Arr::get($defaults, 'default_role', 'user'),
            'map' => [
                'user_name' => $settings->map_user_name ?: Arr::get($mapDefaults, 'user_name'),
                'email' => $settings->map_email ?: Arr::get($mapDefaults, 'email', 'mail'),
                'first_name' => $settings->map_first_name ?: Arr::get($mapDefaults, 'first_name', 'givenName'),
                'last_name' => $settings->map_last_name ?: Arr::get($mapDefaults, 'last_name', 'sn'),
            ],
        ];

        return $this->cached;
    }

    public function get(string $key, $default = null)
    {
        return data_get($this->all(), $key, $default);
    }

    public function save(array $attributes): ActiveDirectorySetting
    {
        if (!Schema::hasTable('active_directory_settings')) {
            throw new RuntimeException('Active Directory settings table not found. Run migrations first.');
        }

        $settings = ActiveDirectorySetting::query()->first() ?? new ActiveDirectorySetting();
        $settings->fill($attributes);
        $settings->save();

        $this->cached = null;

        return $settings;
    }
}
