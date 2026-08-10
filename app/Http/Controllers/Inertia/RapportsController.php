<?php
namespace App\Http\Controllers\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RapportsController extends Controller
{
    public function comptables(Request $request)
    {
        return Inertia::render('Reports/Index', ['activeTab' => 'comptables']);
    }

    public function rh(Request $request)
    {
        return Inertia::render('Reports/Rh', ['activeTab' => 'rh']);
    }

    public function paie(Request $request)
    {
        return Inertia::render('Reports/Paie', ['activeTab' => 'paie']);
    }

    public function fiscaux(Request $request)
    {
        return Inertia::render('Reports/Fiscaux', ['activeTab' => 'fiscaux']);
    }
}
