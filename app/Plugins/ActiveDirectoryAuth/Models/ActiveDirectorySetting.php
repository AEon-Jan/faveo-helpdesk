<?php

namespace App\Plugins\ActiveDirectoryAuth\Models;

use App\BaseModel;

class ActiveDirectorySetting extends BaseModel
{
    protected $table = 'active_directory_settings';

    protected $fillable = [
        'enabled',
        'host',
        'base_dn',
        'bind_dn',
        'bind_password',
        'port',
        'encryption',
        'tls_verify',
        'ca_cert',
        'login_attribute',
        'user_filter',
        'match_field',
        'auto_provision',
        'sync_attributes',
        'default_role',
        'map_user_name',
        'map_email',
        'map_first_name',
        'map_last_name',
    ];
}
