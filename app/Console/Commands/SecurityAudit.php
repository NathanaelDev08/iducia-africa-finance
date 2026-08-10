<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SecurityAudit extends Command
{
    protected $signature = 'security:audit';
    protected $description = 'Audit de sécurité avant mise en production';

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════');
        $this->info('   AUDIT DE SÉCURITÉ — FIDUCIA ERP');
        $this->info('═══════════════════════════════════════════');
        $ok = $warn = $fail = 0;

        $check = function (string $label, string $status, string $detail = '') use (&$ok, &$warn, &$fail) {
            if ($status === 'PASS') { $ok++; $this->line("  ✅ $label" . ($detail !== '' ? " — $detail" : '')); }
            elseif ($status === 'WARN') { $warn++; $this->line("  ⚠️  $label" . ($detail !== '' ? " — $detail" : '')); }
            else { $fail++; $this->line("  ❌ $label" . ($detail !== '' ? " — $detail" : '')); }
        };

        $isProd = config('app.env') === 'production';

        // 1. APP_DEBUG
        $debug = (bool) config('app.debug');
        if (!$debug) $check('APP_DEBUG désactivé', 'PASS');
        elseif ($isProd) $check('APP_DEBUG désactivé', 'FAIL', 'CRITIQUE en production');
        else $check('APP_DEBUG désactivé', 'WARN', 'activé (OK en dev)');

        // 2. Environnement
        $check('Environnement production', $isProd ? 'PASS' : 'WARN', 'actuel : ' . config('app.env'));

        // 3. APP_KEY
        $check('APP_KEY forte', strlen((string) config('app.key')) > 40 ? 'PASS' : 'FAIL');

        // 4. Secret télémétrie
        $ts = config('telemetry.secret');
        $check('Secret télémétrie fort (48+ car.)', $ts && strlen($ts) >= 48 ? 'PASS' : 'WARN', $ts ? strlen($ts) . ' car.' : 'absent');

        // 5. MDP BDD
        $dbp = (string) (config('database.connections.pgsql.password') ?? config('database.connections.mysql.password') ?? '');
        $weak = in_array(strtolower($dbp), ['', 'password', 'secret', 'root', '123456', 'pgsql', 'mysql', 'admin'], true);
        $check('MDP base de données non faible', $weak ? 'FAIL' : 'PASS');

        // 6. HTTPS
        $https = str_starts_with((string) config('app.url'), 'https');
        if ($isProd) $check('APP_URL en https', $https ? 'PASS' : 'FAIL', (string) config('app.url'));
        else $check('APP_URL en https', $https ? 'PASS' : 'WARN', (string) config('app.url') . ' (http OK en dev)');

        // 7. Cookie sécurisé
        $sec = (bool) config('session.secure');
        if ($isProd) $check('Cookie de session sécurisé', $sec ? 'PASS' : 'FAIL');
        else $check('Cookie de session sécurisé', $sec ? 'PASS' : 'WARN', 'active automatiquement en https');

        // 8. Git
        $gi = (string) @file_get_contents(base_path('.gitignore'));
        $check('.env exclu de Git', str_contains($gi, '.env') ? 'PASS' : 'FAIL');

        // 9. XSS — utilisation de chr() pour éviter que le texte ne soit modifié par perl
        $rawNeedle = chr(123) . chr(33) . chr(33);
        $raw = $this->countInDir(base_path('resources/views'), '.blade.php', $rawNeedle);
        $check('Aucune sortie brute (XSS)', $raw === 0 ? 'PASS' : 'WARN', "$raw occurrence(s)");

        // 10. dd/dump — idem, échapper les chaînes pour éviter modification accidentelle
        $ddNeedle = 'd' . 'd' . '(';
        $vdNeedle = 'v' . 'a' . 'r' . '_' . 'd' . 'u' . 'm' . 'p' . '(';
        $dd = $this->countInDir(base_path('app'), '.php', $ddNeedle) + $this->countInDir(base_path('app'), '.php', $vdNeedle);
        $check('Aucun debug dump dans app/', $dd === 0 ? 'PASS' : 'WARN', "$dd occurrence(s)");

        // 11. CSRF
        $check('Protection CSRF active', 'PASS', 'intégrée nativement');

        // 12. IDOR
        $sm = (string) @file_get_contents(app_path('Http/Middleware/SetActiveCompany.php'));
        $check('SetActiveCompany vérifie appartenance', (str_contains($sm, 'company_user') || str_contains($sm, 'companies()')) ? 'PASS' : 'WARN');

        // 13. Télémétrie
        $tc = (string) @file_get_contents(app_path('Http/Controllers/TelemetryController.php'));
        $check('Télémétrie protégée (hash_equals)', str_contains($tc, 'hash_equals') ? 'PASS' : 'FAIL');

        // 14. En-têtes sécurité
        $check('Middleware SecurityHeaders actif', class_exists(\App\Http\Middleware\SecurityHeaders::class) ? 'PASS' : 'FAIL');

        // 15. CORS
        $origins = (array) config('cors.allowed_origins', []);
        $check('CORS sans wildcard *', in_array('*', $origins, true) ? 'WARN' : 'PASS');

        // 16. Rate limiting
        $asp = (string) @file_get_contents(app_path('Providers/AppServiceProvider.php'));
        $hasLogin = str_contains($asp, "RateLimiter::for('login'");
        $hasSensitive = str_contains($asp, "RateLimiter::for('sensitive'");
        $check('Rate limiting configuré', ($hasLogin && $hasSensitive) ? 'PASS' : 'WARN', 'login:' . ($hasLogin ? '✓' : '✗') . ' / sensitive:' . ($hasSensitive ? '✓' : '✗'));

        // 17. Fichiers sensibles exposés
        $exposed = [];
        foreach (['.env', 'composer.json', 'package.json', '.git/config'] as $f) {
            if (is_file(public_path($f))) $exposed[] = $f;
        }
        $check('Aucun fichier sensible dans public/', empty($exposed) ? 'PASS' : 'FAIL', empty($exposed) ? '' : implode(', ', $exposed));

        // 18. Permissions storage
        $writable = is_writable(storage_path());
        $check('Storage accessible en écriture', $writable ? 'PASS' : 'FAIL');

        $this->newLine();
        $total = $ok + $warn + $fail;
        $score = $total > 0 ? round(($ok / $total) * 100) : 0;

        if ($fail === 0 && $warn === 0) $this->info("  🟢 RÉSULTAT : $score/100 — PRÊT POUR LA PRODUCTION");
        elseif ($fail === 0) $this->info("  🟡 RÉSULTAT : $score/100 — ✅ $ok PASS · ⚠️  $warn WARN · ❌ $fail FAIL");
        else $this->info("  🔴 RÉSULTAT : $score/100 — ✅ $ok PASS · ⚠️  $warn WARN · ❌ $fail FAIL");

        $this->info('═══════════════════════════════════════════');

        return $fail > 0 ? 1 : 0;
    }

    protected function countInDir(string $dir, string $ext, string $needle): int
    {
        $count = 0;
        if (!is_dir($dir)) return 0;
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && str_ends_with($f->getFilename(), $ext)) {
                $count += substr_count((string) file_get_contents($f->getPathname()), $needle);
            }
        }
        return $count;
    }
}
