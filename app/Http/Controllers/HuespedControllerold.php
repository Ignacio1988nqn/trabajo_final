<?php

namespace App\Http\Controllers;

use App\Models\Huespedes;

use Illuminate\Http\Request;
use Inertia\Inertia;

class HuespedController extends Controller
{
    public function index()
    {
        return Inertia::render('Huespedes/Index', [
            'huespedes' => Huespedes::with(['persona', 'empresa'])->get(),
        ]);
        // return Inertia::render('Huespedes/Index', [
        //     'huespedes' => Huespedes::all()
        // ]);
    }

    public function create()
    {
        return Inertia::render('Huespedes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        Huespedes::create($validated);

        return redirect()->route('huesped.index')->with('success', 'Producto creado con éxito.');
    }

    public function edit(Huespedes $product)
    {
        return Inertia::render('Huesped/Edit', [
            'product' => $product
        ]);
    }

    public function update(Request $request, Huespedes $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('huesped.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Huespedes $product)
    {
        $product->delete();

        return redirect()->route('huesped.index')->with('success', 'Producto eliminado con éxito.');
    }
}
