@extends('themes.default1.admin.layout.admin')

@section('HeadInclude')
@stop

@section('PageHeader')
<h1>Active Directory Authentication</h1>
@stop

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('plugins') }}">Plugins</a></li>
    <li class="breadcrumb-item active">Active Directory Authentication</li>
</ol>
@stop

@section('content')
@if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {!! Session::get('success') !!}
    </div>
@endif
@if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {!! Session::get('fails') !!}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">Configuration</h3>
    </div>
    <div class="card-body">
        @php
            $settings = $settings ?? [];
        @endphp
        <form method="post" action="{{ route('active-directory-auth.settings.update') }}">
            @csrf
            <div class="form-group">
                <label>
                    <input type="checkbox" name="enabled" value="1" {{ old('enabled', $settings['enabled'] ?? false) ? 'checked' : '' }}>
                    Enable Active Directory authentication
                </label>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="host">Domain controller hostname</label>
                        <input type="text" class="form-control" id="host" name="host" value="{{ old('host', $settings['host'] ?? '') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="port">Port</label>
                        <input type="number" class="form-control" id="port" name="port" value="{{ old('port', $settings['port'] ?? 389) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="base_dn">Base DN</label>
                <input type="text" class="form-control" id="base_dn" name="base_dn" value="{{ old('base_dn', $settings['base_dn'] ?? '') }}">
            </div>

            <div class="form-group">
                <label for="bind_dn">Bind DN</label>
                <input type="text" class="form-control" id="bind_dn" name="bind_dn" value="{{ old('bind_dn', $settings['bind_dn'] ?? '') }}">
            </div>

            <div class="form-group">
                <label for="bind_password">Bind password</label>
                <input type="password" class="form-control" id="bind_password" name="bind_password" placeholder="Leave blank to keep current password">
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="encryption">Encryption</label>
                        <select class="form-control" id="encryption" name="encryption">
                            @php $encryption = old('encryption', $settings['encryption'] ?? ''); @endphp
                            <option value="" {{ $encryption === '' ? 'selected' : '' }}>None</option>
                            <option value="ldaps" {{ $encryption === 'ldaps' ? 'selected' : '' }}>LDAPS</option>
                            <option value="starttls" {{ $encryption === 'starttls' ? 'selected' : '' }}>StartTLS</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="tls_verify" value="1" {{ old('tls_verify', $settings['tls_verify'] ?? true) ? 'checked' : '' }}>
                            Verify TLS certificates
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ca_cert">CA certificate path</label>
                        <input type="text" class="form-control" id="ca_cert" name="ca_cert" value="{{ old('ca_cert', $settings['ca_cert'] ?? '') }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="login_attribute">Login attribute</label>
                        <input type="text" class="form-control" id="login_attribute" name="login_attribute" value="{{ old('login_attribute', $settings['login_attribute'] ?? 'sAMAccountName') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="user_filter">User filter</label>
                        <input type="text" class="form-control" id="user_filter" name="user_filter" value="{{ old('user_filter', $settings['user_filter'] ?? '(objectClass=user)') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="match_field">Match field</label>
                        @php $matchField = old('match_field', $settings['match_field'] ?? 'email'); @endphp
                        <select class="form-control" id="match_field" name="match_field">
                            <option value="email" {{ $matchField === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="user_name" {{ $matchField === 'user_name' ? 'selected' : '' }}>Username</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="auto_provision" value="1" {{ old('auto_provision', $settings['auto_provision'] ?? false) ? 'checked' : '' }}>
                            Auto provision new users
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="sync_attributes" value="1" {{ old('sync_attributes', $settings['sync_attributes'] ?? false) ? 'checked' : '' }}>
                            Sync attributes on login/import
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="default_role">Default role</label>
                        <input type="text" class="form-control" id="default_role" name="default_role" value="{{ old('default_role', $settings['default_role'] ?? 'user') }}">
                    </div>
                </div>
            </div>

            <h5>Attribute mapping</h5>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="map_user_name">Username attribute</label>
                        <input type="text" class="form-control" id="map_user_name" name="map_user_name" value="{{ old('map_user_name', $settings['map']['user_name'] ?? '') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="map_email">Email attribute</label>
                        <input type="text" class="form-control" id="map_email" name="map_email" value="{{ old('map_email', $settings['map']['email'] ?? 'mail') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="map_first_name">First name attribute</label>
                        <input type="text" class="form-control" id="map_first_name" name="map_first_name" value="{{ old('map_first_name', $settings['map']['first_name'] ?? 'givenName') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="map_last_name">Last name attribute</label>
                        <input type="text" class="form-control" id="map_last_name" name="map_last_name" value="{{ old('map_last_name', $settings['map']['last_name'] ?? 'sn') }}">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save settings</button>
        </form>
    </div>
</div>

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">Import users</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">Imports users from Active Directory using the configured filter and base DN.</p>
        <form method="post" action="{{ route('active-directory-auth.import') }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="limit">Limit (optional)</label>
                        <input type="number" class="form-control" id="limit" name="limit" min="0" value="{{ old('limit', 0) }}">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-secondary">Run import</button>
        </form>
    </div>
</div>
@stop
