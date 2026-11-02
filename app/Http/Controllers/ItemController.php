<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Item;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ItemController extends Controller
{
    public function index()
    {
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
        $items = Item::with(['category', 'tax'])->latest()->paginate(15);
        $categories = Category::all();
        $taxes = Tax::all();

        return view('items.index', compact('items', 'company', 'categories', 'taxes'));
    }

    public function create()
    {
        $categories = Category::all();
        $taxes = Tax::all();

        return view('items.create', compact('categories', 'taxes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sku' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'tax_id' => 'nullable|exists:taxes,id',
            'unit' => 'nullable|string|max:20',
        ]);

        $validated['unit'] = $request->unit ?: 'pcs';
        $validated['purchase_price'] = $request->purchase_price ?: 0.00;

        Item::create($validated);

        return redirect()->route('items.index')->with('success', 'Product / Service created successfully.');
    }

    public function edit($id)
    {
        $item = $id instanceof Item ? $id : Item::findOrFail($id);
        $categories = Category::all();
        $taxes = Tax::all();

        return view('items.edit', compact('item', 'categories', 'taxes'));
    }

    public function update(Request $request, $id)
    {
        $item = $id instanceof Item ? $id : Item::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sku' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'tax_id' => 'nullable|exists:taxes,id',
            'unit' => 'nullable|string|max:20',
        ]);

        $validated['unit'] = $request->unit ?: ($item->unit ?: 'pcs');
        $validated['purchase_price'] = $request->purchase_price ?: 0.00;

        $item->update($validated);

        return redirect()->route('items.index')->with('success', 'Product / Service updated successfully.');
    }

    public function destroy($id)
    {
        $item = $id instanceof Item ? $id : Item::findOrFail($id);
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Product / Service deleted successfully.');
    }
}
