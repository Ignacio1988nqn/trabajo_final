<?php

namespace App\Http\Controllers;

use App\Models\GastoItems;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GastoItemsController extends Controller
{
    public function index()
    {
        $items = GastoItems::orderBy('nombre')->get(['id', 'nombre', 'descripcion', 'precio', 'tipo', 'stock']);
        
        return Inertia::render('GastoItems/Index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return Inertia::render('GastoItems/Form', [
            'item' => null,
            'tipos' => ['habitacion', 'servicios', 'minibar', 'confiteria'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'tipo' => 'required|in:habitacion,servicios,minibar,confiteria',
            'stock' => 'nullable|integer|min:0',
        ]);

        GastoItems::create($validated);

        return redirect()->route('gasto-items.index')->with('success', 'Ítem creado correctamente.');
    }

    public function edit(GastoItems $gastoItem)
    {
        return Inertia::render('GastoItems/Form', [
            'item' => $gastoItem,
            'tipos' => ['habitacion', 'servicios', 'minibar', 'confiteria'],
        ]);
    }

    public function update(Request $request, GastoItems $gastoItem)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'tipo' => 'required|in:habitacion,servicios,minibar,confiteria',
            'stock' => 'nullable|integer|min:0',
        ]);

        $gastoItem->update($validated);

        return redirect()->route('gasto-items.index')->with('success', 'Ítem actualizado correctamente.');
    }

    public function destroy(GastoItems $gastoItem)
    {
        $gastoItem->delete();

        return redirect()->route('gasto-items.index')->with('success', 'Ítem eliminado correctamente.');
    }
}