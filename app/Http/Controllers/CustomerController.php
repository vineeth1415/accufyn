<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerController extends Controller
{
    public function index()
    {
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
        $currencySymbol = $company->currency_symbol ?? '₹';
        $customers = Customer::withCount('invoices')->latest()->paginate(15);

        return view('customers.index', compact('customers', 'company', 'currencySymbol'));
    }

    public function create()
    {
        return view('customers.create');
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

        $customer = Customer::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully.',
                'customer' => $customer,
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show($id)
    {
        $customer = $id instanceof Customer ? $id : Customer::with(['invoices.items', 'transactions.bankAccount'])->findOrFail($id);
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('customers.show', compact('customer', 'company'));
    }

    public function edit($id)
    {
        $customer = $id instanceof Customer ? $id : Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = $id instanceof Customer ? $id : Customer::findOrFail($id);
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

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = $id instanceof Customer ? $id : Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
