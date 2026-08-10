<?php
namespace App\Http\Controllers;

use App\Models\SystemTelemetry;
use App\Services\TelemetryService;
use Illuminate\Http\Request;

class TelemetryController extends Controller
{
    public function index(string $token, TelemetryService $service)
    {
        $this->guard($token);
        
        $days = request()->input('days', 30);
        
        $companies = $this->companiesData();

        return view('telemetry.insights', [
            'token'     => $token,
            'live'      => $service->collect(),
            'snapshots' => SystemTelemetry::orderByDesc('recorded_at')->limit(30)->get(),
            'activityByHour' => $service->userActivityByHour($days),
            'topUsers' => $service->topUsers(10, $days),
            'deviceBreakdown' => $service->deviceBreakdown($days),
            'countryBreakdown' => $service->countryBreakdown($days),
            'dailyActive' => $service->dailyActiveUsers($days),
            'funnel' => $service->eventFunnel($days),
            'days' => $days,
            'companies' => $companies,
        ]);
    }

    public function json(string $token, TelemetryService $service)
    {
        $this->guard($token);
        return response()->json($service->collect());
    }

    public function realtime(string $token, TelemetryService $service)
    {
        $this->guard($token);
        
        $activeNow = \App\Models\User::where('last_seen_at', '>=', now()->subMinutes(5))->count();
        
        return response()->json([
            'active_now' => $activeNow,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function export(string $token, TelemetryService $service)
    {
        $this->guard($token);
        
        $days = request()->input('days', 30);
        $data = [
            'summary' => $service->collect(),
            'activity_by_hour' => $service->userActivityByHour($days),
            'top_users' => $service->topUsers(50, $days),
            'device_breakdown' => $service->deviceBreakdown($days),
            'country_breakdown' => $service->countryBreakdown($days),
            'daily_active' => $service->dailyActiveUsers($days),
        ];
        
        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="telemetry-' . now()->format('Y-m-d') . '.json"'
        ]);
    }

    protected function guard(string $token): void
    {
        $secret = config('telemetry.secret');
        if (!$secret || !hash_equals((string) $secret, (string) $token)) abort(404);
    }

    /** Liste des entreprises avec contacts, utilisateurs et stats */
    protected function companiesData(): array
    {
        $out = [];
        try {
            foreach (\Illuminate\Support\Facades\DB::table('companies')->orderBy('name')->get() as $co) {
                $users = [];
                try {
                    $users = \Illuminate\Support\Facades\DB::table('company_user')
                        ->join('users', 'company_user.user_id', '=', 'users.id')
                        ->where('company_user.company_id', $co->id)
                        ->select('users.name', 'users.email')
                        ->get()->toArray();
                } catch (\Throwable $e) {}

                $count = fn(string $t) => \Illuminate\Support\Facades\Schema::hasTable($t)
                    ? (int) \Illuminate\Support\Facades\DB::table($t)->where('company_id', $co->id)->count() : 0;

                $out[] = [
                    'id' => $co->id,
                    'name' => $co->name,
                    'tax_number' => $co->tax_number ?? null,
                    'email' => $co->email ?? null,
                    'phone' => $co->phone ?? null,
                    'address' => $co->address ?? null,
                    'contact_name' => $co->contact_name ?? null,
                    'is_blocked' => (bool) ($co->is_blocked ?? false),
                    'users' => $users,
                    'employees' => $count('employees'),
                    'invoices' => $count('sales_invoices') + $count('purchase_invoices'),
                ];
            }
        } catch (\Throwable $e) {}
        return $out;
    }

    public function blockCompany(string $token, int $id)
    {
        $this->guard($token);
        $this->setBlocked($id, true);
        return back()->with('ok', 'Entreprise bloquée.');
    }

    public function unblockCompany(string $token, int $id)
    {
        $this->guard($token);
        $this->setBlocked($id, false);
        return back()->with('ok', 'Entreprise débloquée.');
    }

    protected function setBlocked(int $id, bool $v): void
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('companies', 'is_blocked')) {
            \Illuminate\Support\Facades\DB::table('companies')->where('id', $id)->update([
                'is_blocked' => $v,
                'blocked_at' => $v ? now() : null,
                'updated_at' => now(),
            ]);
        }
    }
}
