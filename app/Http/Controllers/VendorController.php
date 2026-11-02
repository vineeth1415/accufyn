<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VendorController extends Controller
{
    public function index()
    {
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
        $vendors = Vendor::withCount('bills')->latest()->paginate(15);

        return view('vendors.index', compact('vendors', 'company'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:150',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $vendor = Vendor::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vendor created successfully.',
                'vendor' => $vendor,
            ]);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function show($id)
    {
        $vendor = $id instanceof Vendor ? $id : Vendor::with(['bills.items', 'transactions.bankAccount'])->findOrFail($id);
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('vendors.show', compact('vendor', 'company'));
    }

    public function edit($id)
    {
        $vendor = $id instanceof Vendor ? $id : Vendor::findOrFail($id);
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, $id)
    {
        $vendor = $id instanceof Vendor ? $id : Vendor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:150',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy($id)
    {
        $vendor = $id instanceof Vendor ? $id : Vendor::findOrFail($id);
        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
