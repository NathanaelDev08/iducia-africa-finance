<?php
$envFile = __DIR__ . '/.env';
$envContent = file_exists($envFile) ? file_get_contents($envFile) : '';

$replacements = [
    '/^APP_NAME=.*/m' => 'APP_NAME="FIDUCIA AFRICA Conseil & Finance"',
    '/^DB_CONNECTION=.*/m' => 'DB_CONNECTION=pgsql',
    '/^DB_HOST=.*/m' => 'DB_HOST=127.0.0.1',
    '/^DB_PORT=.*/m' => 'DB_PORT=5432',
    '/^DB_DATABASE=.*/m' => 'DB_DATABASE=fiducia_africa',
    '/^DB_USERNAME=.*/m' => 'DB_USERNAME=postgres',
    '/^DB_PASSWORD=.*/m' => 'DB_PASSWORD=postgres',
    '/^APP_LOCALE=.*/m' => 'APP_LOCALE=fr',
    '/^APP_FAKER_LOCALE=.*/m' => 'APP_FAKER_LOCALE=fr_FR',
    '/^APP_TIMEZONE=.*/m' => 'APP_TIMEZONE=Africa/Abidjan',
];

foreach ($replacements as $pattern => $replacement) {
    if (preg_match($pattern, $envContent)) {
        $envContent = preg_replace($pattern, $replacement, $envContent);
    } else {
        $envContent .= "\n" . $replacement;
    }
}
file_put_contents($envFile, $envContent);

$permConfig = __DIR__ . '/config/permission.php';
if (file_exists($permConfig)) {
    $content = file_get_contents($permConfig);
    $content = str_replace("'teams' => false,", "'teams' => true,", $content);
    $content = str_replace("'team_foreign_key' => null,", "'team_foreign_key' => 'company_id',", $content);
    file_put_contents($permConfig, $content);
    echo "✅ Configuration Spatie (Teams) activée pour les rôles par entreprise.\n";
}
echo "✅ Fichier .env configuré avec succès.\n";
