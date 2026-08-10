<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'companies_total' => Company::count(),
            'companies_active' => Company::where('is_active', true)->count(),
            'users_total' => User::count(),
            'employees_total' => DB::table('employees')->count(),
            'payslips_total' => DB::table('payslips')->count(),
            'invoices_total' => DB::table('sales_invoices')->count(),
        ];

        $recentCompanies = Company::latest()->take(5)->get(['id', 'name', 'slug', 'currency', 'is_active', 'created_at']);

        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => $stats,
            'recentCompanies' => $recentCompanies,
        ]);
    }
}
