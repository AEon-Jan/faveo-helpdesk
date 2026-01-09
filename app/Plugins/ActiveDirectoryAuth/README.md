# LDAP (Active Directory) Plugin

Dieses Plugin ermöglicht die Anmeldung an Faveo über ein Active-Directory/LDAP-Verzeichnis. Zusätzlich können Benutzer aus AD importiert und optional automatisch angelegt bzw. synchronisiert werden.

## Überblick

- **Authentifizierung** gegen AD/LDAP bei der Faveo-Anmeldung
- **Benutzer-Import** aus AD auf Knopfdruck
- **Automatische Anlage** von Benutzern (optional)
- **Attribut-Mapping** (Benutzername, E‑Mail, Vor-/Nachname)
- **TLS/SSL-Unterstützung** (LDAPS oder StartTLS)

## Voraussetzungen

- Faveo Helpdesk mit aktivierten Plugins
- PHP LDAP-Erweiterung und Paket `adldap2/adldap2` (in `composer.json` enthalten)
- Netzwerkzugriff vom Faveo-Server auf den Domain Controller

## Installation

1. **Code bereitstellen**
   - Das Plugin liegt unter `app/Plugins/ActiveDirectoryAuth`.
   - Stelle sicher, dass der Plugin-Ordner im Projekt enthalten ist.

2. **Datenbank-Migrationen ausführen**
   - Die Migrationen legen die Plugin-Registrierung und die Tabelle `active_directory_settings` an.
   - Befehl ausführen:
     ```bash
     php artisan migrate
     ```

3. **Plugin aktivieren**
   - Öffne das Faveo-Backend und aktiviere das Plugin unter **Admin → Plugins** (falls erforderlich).

## Konfiguration (Backend)

Die Einstellungen findest du unter:

**Admin → Plugins → Active Directory Authentication**

### Verbindungsdaten

| Feld | Beschreibung |
| --- | --- |
| Domain controller hostname | Hostname oder IP des DC/LDAP-Servers |
| Port | Standard 389 (LDAP) oder 636 (LDAPS) |
| Base DN | Suchbasis z. B. `DC=example,DC=local` |
| Bind DN | Bind-Benutzer z. B. `CN=svc-ldap,OU=Service Accounts,DC=example,DC=local` |
| Bind password | Passwort des Bind-Benutzers |

### Verschlüsselung

| Feld | Beschreibung |
| --- | --- |
| Encryption | `None`, `LDAPS`, `StartTLS` |
| Verify TLS certificates | Zertifikatsprüfung aktivieren/deaktivieren |
| CA certificate path | Pfad zur CA-Datei, z. B. `/etc/ssl/certs/ca-bundle.crt` |

### Anmelde-/Suchparameter

| Feld | Beschreibung |
| --- | --- |
| Login attribute | AD-Attribut für die Anmeldung, z. B. `sAMAccountName` |
| User filter | LDAP-Filter, z. B. `(objectClass=user)` |
| Match field | Abgleich in Faveo: `email` oder `user_name` |

### Provisionierung & Sync

| Feld | Beschreibung |
| --- | --- |
| Auto provision new users | Legt neue Benutzer in Faveo automatisch an |
| Sync attributes on login/import | Synchronisiert Attribute bei Login/Import |
| Default role | Rolle für neu angelegte Benutzer (z. B. `user`) |

### Attribut-Mapping

| Feld | Beschreibung | Standard |
| --- | --- | --- |
| Username attribute | AD-Attribut für den Faveo-Benutzernamen | `sAMAccountName` |
| Email attribute | AD-Attribut für die E‑Mail | `mail` |
| First name attribute | AD-Attribut für den Vornamen | `givenName` |
| Last name attribute | AD-Attribut für den Nachnamen | `sn` |

## Konfiguration per `.env` (optional)

Standardwerte können in der `.env` gesetzt werden. Diese Werte werden als Defaults übernommen, bis sie im Backend gespeichert werden.

```env
AD_AUTH_ENABLED=false
AD_HOST=ldap.example.local
AD_BASE_DN="DC=example,DC=local"
AD_BIND_DN="CN=svc-ldap,OU=Service Accounts,DC=example,DC=local"
AD_BIND_PASSWORD="secret"
AD_PORT=389
AD_ENCRYPTION=
AD_TLS_VERIFY=true
AD_CA_CERT=/etc/ssl/certs/ca-bundle.crt
AD_LOGIN_ATTR=sAMAccountName
AD_USER_FILTER="(objectClass=user)"
AD_MATCH_FIELD=email
AD_AUTO_PROVISION=false
AD_SYNC_ATTRIBUTES=false
AD_DEFAULT_ROLE=user
AD_MAP_USER_NAME=
AD_MAP_EMAIL=mail
AD_MAP_FIRST_NAME=givenName
AD_MAP_LAST_NAME=sn
```

## Verbindung testen

Im Backend gibt es einen Button **„Test connection“**. Dieser verwendet die aktuell gespeicherten Einstellungen. Eine erfolgreiche Verbindung bestätigt, dass Host, Bind DN, Passwort und TLS-Einstellungen korrekt sind.

## Benutzer importieren

Unter **„Import users“** können Benutzer aus dem AD importiert werden.

- Es wird der konfigurierte **User filter** und **Base DN** verwendet.
- Deaktivierte AD-Accounts werden automatisch ausgeschlossen.
- Optional kann ein **Limit** gesetzt werden.
- Bei aktivierter **Auto provision** werden fehlende Benutzer in Faveo angelegt.
- Bei aktivierter **Sync attributes** werden Attribute bei Import/Anmeldung aktualisiert.

## Häufige Fehler & Tipps

- **„Active Directory settings table not found“**
  - Migrationen ausführen: `php artisan migrate`.

- **„Active Directory client is unavailable“**
  - Stelle sicher, dass `adldap2/adldap2` installiert ist und die Composer-Abhängigkeiten vorhanden sind.

- **TLS-Fehler / Zertifikatsprobleme**
  - Setze `CA certificate path` korrekt oder deaktiviere **Verify TLS certificates** (nur zu Testzwecken!).

- **Keine Benutzer gefunden**
  - `Base DN`, `User filter` und `Login attribute` prüfen.

## Sicherheitshinweise

- Verwende nach Möglichkeit **LDAPS** oder **StartTLS**.
- Deaktiviere **Verify TLS certificates** nur kurzfristig zu Testzwecken.
- Nutze einen dedizierten AD-Service-Account mit minimalen Rechten für den Bind.

---

**Hinweis:** Dieses Plugin heißt intern „ActiveDirectoryAuth“, stellt jedoch LDAP/AD-Authentifizierung für Faveo bereit.
