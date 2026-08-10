<?php
namespace App\Services;

use App\Models\Company;
use App\Models\SystemTelemetry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class TelemetryService
{
    public function installId(): string
    {
        return hash('sha256', config('app.key') . '|' . config('app.url'));
    }

    public function collect(): array
    {
        $count = fn(string $t) => Schema::hasTable($t) ? (int) DB::table($t)->count() : 0;

        return [
            'install_id'  => $this->installId(),
            'app_name'    => config('app.name'),
            'app_url'     => config('app.url'),
            'version'     => config('app.version', '1.0.0'),
            'php'         => PHP_VERSION,
            'laravel'     => app()->version(),
            'recorded_at' => now()->toIso8601String(),
            'stats' => [
                'users_total'        => User::count(),
                'users_active_30d'   => $this->activeUsers(30),
                'users_active_7d'    => $this->activeUsers(7),
                'users_active_24h'   => $this->activeUsers(1),
                'companies_total'    => Company::count(),
                'employees_total'    => $count('employees'),
                'clients_total'      => $count('clients'),
                'suppliers_total'    => $count('suppliers'),
                'invoices_total'     => $count('sales_invoices') + $count('purchase_invoices'),
                'payslips_total'     => $count('payslips'),
                'journal_entries'    => $count('journal_entries'),
                'sessions_today'     => $this->sessionsToday(),
                'events_today'       => $this->eventsToday(),
            ],
        ];
    }

    protected function activeUsers(int $days): int
    {
        try {
            if (!Schema::hasColumn('users', 'last_seen_at')) return 0;
            return User::where('last_seen_at', '>=', now()->subDays($days))->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function sessionsToday(): int
    {
        try {
            if (!Schema::hasTable('telemetry_sessions')) return 0;
            return DB::table('telemetry_sessions')
                ->where('started_at', '>=', now()->startOfDay())
                ->distinct('session_id')
                ->count('session_id');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function eventsToday(): int
    {
        try {
            if (!Schema::hasTable('telemetry_events')) return 0;
            return DB::table('telemetry_events')
                ->where('occurred_at', '>=', now()->startOfDay())
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function snapshot(): SystemTelemetry
    {
        return SystemTelemetry::create([
            'install_id'  => $this->installId(),
            'payload'     => $this->collect(),
            'recorded_at' => now(),
        ]);
    }

    public function beacon(): void
    {
        $url = config('telemetry.beacon_url');
        if (!$url || !config('telemetry.enabled')) return;
        try {
            Http::timeout(5)->post($url, $this->collect());
        } catch (\Throwable $e) {}
    }

    public function userActivityByHour(int $days = 30): array
    {
        try {
            if (!Schema::hasTable('telemetry_events')) return [];
            return DB::table('telemetry_events')
                ->select(DB::raw('HOUR(occurred_at) as hour'), DB::raw('COUNT(*) as count'))
                ->where('occurred_at', '>=', now()->subDays($days))
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->pluck('count', 'hour')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function topUsers(int $limit = 10, int $days = 30): array
    {
        try {
            if (!Schema::hasTable('telemetry_events')) return [];
            return DB::table('telemetry_events')
                ->join('users', 'telemetry_events.user_id', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(*) as event_count'))
                ->where('telemetry_events.occurred_at', '>=', now()->subDays($days))
                ->whereNotNull('telemetry_events.user_id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderByDesc('event_count')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function deviceBreakdown(int $days = 30): array
    {
        try {
            if (!Schema::hasTable('telemetry_sessions')) return [];
            return DB::table('telemetry_sessions')
                ->select('device_type', DB::raw('COUNT(*) as count'))
                ->where('started_at', '>=', now()->subDays($days))
                ->whereNotNull('device_type')
                ->groupBy('device_type')
                ->get()
                ->pluck('count', 'device_type')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function countryBreakdown(int $days = 30): array
    {
        try {
            if (!Schema::hasTable('telemetry_sessions')) return [];
            return DB::table('telemetry_sessions')
                ->select('country', DB::raw('COUNT(*) as count'))
                ->where('started_at', '>=', now()->subDays($days))
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->pluck('count', 'country')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function dailyActiveUsers(int $days = 30): array
    {
        try {
            if (!Schema::hasTable('telemetry_sessions')) return [];
            return DB::table('telemetry_sessions')
                ->select(DB::raw('DATE(started_at) as date'), DB::raw('COUNT(DISTINCT session_id) as users'))
                ->where('started_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function eventFunnel(int $days = 30): array
    {
        try {
            if (!Schema::hasTable('telemetry_events')) return [];
            $events = ['login', 'view_dashboard', 'create_invoice', 'generate_payslip'];
            $funnel = [];
            foreach ($events as $event) {
                $funnel[$event] = DB::table('telemetry_events')
                    ->where('event_name', $event)
                    ->where('occurred_at', '>=', now()->subDays($days))
                    ->count();
            }
            return $funnel;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
