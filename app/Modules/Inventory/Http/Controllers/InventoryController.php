<?php
namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Inventory\Models\StockItem;
use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:inventory');
    }

    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    public function index(Request $request)
    {
        $company = $this->company($request);

        $items = StockItem::where('company_id', $company->id)
            ->orderBy('code')->get()
            ->map(fn ($i) => [
                'id' => $i->id, 'code' => $i->code, 'name' => $i->name,
                'category' => $i->category, 'unit' => $i->unit,
                'quantity_on_hand' => (float) $i->quantity_on_hand,
                'unit_cost' => (float) $i->unit_cost,
                'value' => round((float) $i->quantity_on_hand * (float) $i->unit_cost, 2),
                'reorder_threshold' => (float) $i->reorder_threshold,
                'below_threshold' => $i->isBelowThreshold(),
                'is_active' => (bool) $i->is_active,
            ]);

        $movements = StockMovement::where('company_id', $company->id)
            ->with('stockItem')->latest('movement_date')->latest('id')->take(100)->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'item' => $m->stockItem ? ['code' => $m->stockItem->code, 'name' => $m->stockItem->name] : null,
                'type' => $m->type, 'quantity' => (float) $m->quantity,
                'reference' => $m->reference, 'movement_date' => $m->movement_date->toDateString(),
            ]);

        $stats = [
            'items_count' => $items->count(),
            'total_value' => round($items->sum('value'), 2),
            'below_threshold_count' => $items->where('below_threshold', true)->count(),
        ];

        return Inertia::render('Inventory/Index', [
            'items' => $items,
            'movements' => $movements,
            'stats' => $stats,
            'initialTab' => $request->query('tab', 'items'),
        ]);
    }

    public function storeItem(Request $request)
    {
        $company = $this->company($request);
        $d = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('stock_items')->where(fn ($q) => $q->where('company_id', $company->id))],
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:20',
            'unit_cost' => 'nullable|numeric|min:0',
            'reorder_threshold' => 'nullable|numeric|min:0',
        ]);

        StockItem::create(array_merge($d, [
            'company_id' => $company->id,
            'unit' => $d['unit'] ?? 'unité',
            'unit_cost' => $d['unit_cost'] ?? 0,
            'reorder_threshold' => $d['reorder_threshold'] ?? 0,
            'quantity_on_hand' => 0,
            'is_active' => true,
        ]));

        return back()->with('success', 'Article créé.');
    }

    public function updateItem(Request $request, StockItem $item)
    {
        if ($item->company_id !== $this->company($request)->id) abort(403);

        $d = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('stock_items')->where(fn ($q) => $q->where('company_id', $item->company_id))->ignore($item->id)],
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:20',
            'unit_cost' => 'nullable|numeric|min:0',
            'reorder_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $item->update($d);

        return back()->with('success', 'Article mis à jour.');
    }

    public function destroyItem(Request $request, StockItem $item)
    {
        if ($item->company_id !== $this->company($request)->id) abort(403);

        if ($item->movements()->exists()) {
            return back()->with('error', 'Impossible : cet article a des mouvements enregistrés.');
        }

        $item->delete();

        return back()->with('success', 'Article supprimé.');
    }

    public function storeMovement(Request $request)
    {
        $company = $this->company($request);

        $d = $request->validate([
            'stock_item_id' => 'required|exists:stock_items,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
            'movement_date' => 'required|date',
        ]);

        $item = StockItem::where('company_id', $company->id)->findOrFail($d['stock_item_id']);

        return DB::transaction(function () use ($d, $item, $company) {
            $current = (float) $item->quantity_on_hand;
            $qty = (float) $d['quantity'];

            if ($d['type'] === 'in') {
                $delta = $qty;
            } elseif ($d['type'] === 'out') {
                if ($qty > $current) {
                    return back()->with('error', "Stock insuffisant : {$current} {$item->unit} disponible(s).");
                }
                $delta = -$qty;
            } else {
                // adjustment : la quantité saisie est le nouveau niveau de stock absolu
                $delta = $qty - $current;
            }

            $item->quantity_on_hand = $current + $delta;
            if ($d['type'] === 'in' && !empty($d['unit_cost'])) {
                $item->unit_cost = $d['unit_cost'];
            }
            $item->save();

            StockMovement::create([
                'company_id' => $company->id,
                'stock_item_id' => $item->id,
                'type' => $d['type'],
                'quantity' => $d['type'] === 'adjustment' ? $delta : $qty,
                'unit_cost' => $d['unit_cost'] ?? null,
                'reference' => $d['reference'] ?? null,
                'note' => $d['note'] ?? null,
                'movement_date' => $d['movement_date'],
            ]);

            return back()->with('success', 'Mouvement enregistré.');
        });
    }
}
