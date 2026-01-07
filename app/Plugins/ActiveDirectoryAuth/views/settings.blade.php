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
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">Configuration</h3>
    </div>
    <div class="card-body">
        <p>Configure Active Directory connection values in your <code>.env</code> file and enable the plugin.</p>
        <ul>
            <li><strong>AD_AUTH_ENABLED</strong> (true/false)</li>
            <li><strong>AD_HOST</strong> (domain controller hostname)</li>
            <li><strong>AD_BASE_DN</strong> (base DN)</li>
            <li><strong>AD_BIND_DN</strong> (service account DN)</li>
            <li><strong>AD_BIND_PASSWORD</strong> (service account password)</li>
            <li><strong>AD_PORT</strong> (default 389)</li>
            <li><strong>AD_ENCRYPTION</strong> (ldaps/starttls/empty)</li>
            <li><strong>AD_TLS_VERIFY</strong> (true/false)</li>
            <li><strong>AD_LOGIN_ATTR</strong> (default sAMAccountName)</li>
            <li><strong>AD_USER_FILTER</strong> (default (objectClass=user))</li>
            <li><strong>AD_MATCH_FIELD</strong> (email/user_name)</li>
            <li><strong>AD_AUTO_PROVISION</strong> (true/false)</li>
            <li><strong>AD_SYNC_ATTRIBUTES</strong> (true/false)</li>
        </ul>
        <p class="text-muted">After changing <code>.env</code>, clear config cache if used.</p>
    </div>
</div>
@stop
