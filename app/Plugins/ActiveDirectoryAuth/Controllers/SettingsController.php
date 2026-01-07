<?php

namespace App\Plugins\ActiveDirectoryAuth\Controllers;

use App\Http\Controllers\Controller;
use Adldap\Exceptions\AdldapException;
use App\Plugins\ActiveDirectoryAuth\AdAuthenticator;
use App\Plugins\ActiveDirectoryAuth\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index(SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->all();

        return view('active-directory-auth::settings', compact('settings'));
    }

    public function update(Request $request, SettingsRepository $settingsRepository)
    {
        $validated = $request->validate([
            'host' => 'nullable|string',
            'base_dn' => 'nullable|string',
            'bind_dn' => 'nullable|string',
            'bind_password' => 'nullable|string',
            'port' => 'nullable|integer|min:1',
            'encryption' => ['nullable', Rule::in(['', 'ldaps', 'starttls'])],
            'ca_cert' => 'nullable|string',
            'login_attribute' => 'nullable|string',
            'user_filter' => 'nullable|string',
            'match_field' => ['nullable', Rule::in(['email', 'user_name'])],
            'default_role' => 'nullable|string',
            'map_user_name' => 'nullable|string',
            'map_email' => 'nullable|string',
            'map_first_name' => 'nullable|string',
            'map_last_name' => 'nullable|string',
        ]);

        $existing = $settingsRepository->all();

        $payload = array_merge($validated, [
            'enabled' => $request->boolean('enabled'),
            'tls_verify' => $request->boolean('tls_verify'),
            'auto_provision' => $request->boolean('auto_provision'),
            'sync_attributes' => $request->boolean('sync_attributes'),
            'port' => $validated['port'] ?? 389,
            'encryption' => $validated['encryption'] ?? '',
        ]);

        if (($payload['bind_password'] ?? '') === '' && !empty($existing['bind_password'])) {
            unset($payload['bind_password']);
        }

        $settingsRepository->save($payload);

        return redirect()
            ->route('active-directory-auth.settings')
            ->with('success', 'Active Directory settings updated.');
    }

    public function import(Request $request, AdAuthenticator $authenticator)
    {
        $limit = (int) $request->input('limit', 0);

        try {
            $provider = $authenticator->connect();
            if (!$provider) {
                return redirect()
                    ->route('active-directory-auth.settings')
                    ->with('fails', 'Active Directory connection could not be established.');
            }

            $settings = $authenticator->settings();
            $baseFilter = $settings['user_filter'] ?? '(objectClass=user)';
            if (trim($baseFilter) === '') {
                $baseFilter = '(objectClass=user)';
            }
            $filter = sprintf('(&%s(!(userAccountControl:1.2.840.113556.1.4.803:=2)))', $baseFilter);

            $query = $provider->search()->rawFilter($filter);
            if ($limit > 0) {
                $query->limit($limit);
            }

            $adUsers = $query->get();
            $synced = 0;
            foreach ($adUsers as $adUser) {
                if ($authenticator->syncAdUser($adUser)) {
                    $synced++;
                }
            }

            return redirect()
                ->route('active-directory-auth.settings')
                ->with('success', "Imported {$synced} Active Directory users.");
        } catch (AdldapException $exception) {
            return redirect()
                ->route('active-directory-auth.settings')
                ->with('fails', 'AD import failed: '.$exception->getMessage());
        } catch (\Throwable $exception) {
            Log::error('Active Directory import failed.', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return redirect()
                ->route('active-directory-auth.settings')
                ->with('fails', 'AD import failed. Check logs for details.');
        }
    }
}
