<?php
namespace App\Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Assets\Models\Asset;
use App\Modules\Assets\Models\AssetDepreciation;
use App\Modules\Assets\Services\AssetDepreciationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetsController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:assets');
    }

    protected function company(Request $request): Company
    { return $request->attributes->get('company') ?? Company::first(); }

    public function index(Request $request)
    {
        $company = $this->company($request);

        $assets = Asset::where('company_id', $company->id)->orderBy('code')->get()
            ->map(fn ($a) => [
                'id'=>$a->id,'code'=>$a->code,'name'=>$a->name,
                'acquisition_date'=>$a->acquisition_date->toDateString(),
                'acquisition_cost'=>(float)$a->acquisition_cost,'residual_value'=>(float)$a->residual_value,
                'useful_life_months'=>$a->useful_life_months,'monthly'=>$a->monthlyDepreciation(),
                'status'=>$a->status,
                'accumulated'=>(float)$a->depreciations()->sum('amount'),
                'net_book_value'=>(float)$a->acquisition_cost - (float)$a->depreciations()->sum('amount'),
            ]);

        $depreciations = AssetDepreciation::where('company_id', $company->id)
            ->with('asset')->orderByDesc('period')->orderBy('asset_id')->get()
            ->map(fn ($d) => [
                'id'=>$d->id,'asset'=>['code'=>$d->asset->code,'name'=>$d->asset->name],
                'period'=>$d->period,'amount'=>(float)$d->amount,'accumulated'=>(float)$d->accumulated,
                'net_book_value'=>(float)$d->net_book_value,'status'=>$d->status,'is_posted'=>!is_null($d->accounting_entry_id),
            ]);

        $stats = [
            'assets_count' => $assets->count(),
            'gross_value' => $assets->sum('acquisition_cost'),
            'accumulated' => $assets->sum('accumulated'),
            'net_value' => $assets->sum('net_book_value'),
        ];

        return Inertia::render('Assets/Index', [
            'assets'=>$assets,'depreciations'=>$depreciations,'stats'=>$stats,
            'currentPeriod'=>now()->format('Y-m'),
            'initialTab'=>$request->query('tab','assets'),
        ]);
    }

    public function storeAsset(Request $request)
    {
        $d = $request->validate([
            'code'=>'required|string|max:20','name'=>'required|string|max:255',
            'acquisition_date'=>'required|date','acquisition_cost'=>'required|numeric|min:0',
            'residual_value'=>'nullable|numeric|min:0','useful_life_months'=>'required|integer|min:1',
            'account_asset'=>'nullable|string|max:20','account_depreciation'=>'nullable|string|max:20','account_expense'=>'nullable|string|max:20',
        ]);
        Asset::create(array_merge($d, ['company_id'=>$this->company($request)->id, 'residual_value'=>$d['residual_value']??0, 'status'=>'active']));
        return back()->with('success', 'Immobilisation créée.');
    }

    public function updateAsset(Request $request, Asset $asset)
    {
        if ($asset->company_id !== $this->company($request)->id) abort(403);
        $d = $request->validate(['code'=>'required|string|max:20','name'=>'required|string|max:255','acquisition_date'=>'required|date','acquisition_cost'=>'required|numeric|min:0','residual_value'=>'nullable|numeric|min:0','useful_life_months'=>'required|integer|min:1','status'=>'in:active,disposed']);
        $asset->update(array_merge($d, ['residual_value'=>$d['residual_value']??0]));
        return back()->with('success', 'Immobilisation mise à jour.');
    }

    public function destroyAsset(Request $request, Asset $asset)
    {
        if ($asset->company_id !== $this->company($request)->id) abort(403);
        if ($asset->depreciations()->where('status','posted')->count() > 0) return back()->with('error', 'Impossible : des amortissements sont comptabilisés.');
        $asset->delete();
        return back()->with('success', 'Immobilisation supprimée.');
    }

    public function generate(Request $request)
    {
        $request->validate(['period'=>'required|regex:/^\d{4}-\d{2}$/']);
        $count = app(AssetDepreciationService::class)->generateForPeriod($this->company($request), $request->period);
        return back()->with('success', $count . ' dotation(s) générée(s) pour ' . $request->period . '.');
    }

    public function postDepreciation(Request $request, AssetDepreciation $depreciation)
    {
        if ($depreciation->company_id !== $this->company($request)->id) abort(403);
        try { app(AssetDepreciationService::class)->postDepreciation($depreciation); return back()->with('success', 'Dotation comptabilisée.'); }
        catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }
}
