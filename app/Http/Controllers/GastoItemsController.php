<?php

namespace App\Http\Controllers;

use App\Models\GastoItems;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string',
            // 'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $gastoItem = GastoItems::create($request->only([
            'nombre',
            'precio',
            'descripcion',
            'tipo',
            'stock'
        ]));

        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {

            $folder = 'gastoitems';
            $extension = $request->file('imagen')->getClientOriginalExtension();
            $filename = "gastoitem_{$gastoItem->id}.{$extension}";
            $request->file('imagen')->storeAs($folder, $filename, 'public');
        }

        return redirect()->route('gasto-items.index')->with('success', 'Ítem creado');
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
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $gastoItem->update($request->only(['nombre', 'precio', 'stock', 'descripcion', 'tipo']));


        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {

            $folder = 'gastoitems';
            $extension = $request->file('imagen')->getClientOriginalExtension();
            $filename = "gastoitem_{$gastoItem->id}.{$extension}";
            $request->file('imagen')->storeAs($folder, $filename, 'public');
        }

        return redirect()->route('gasto-items.index');
    }

    public function destroy(GastoItems $gastoItem)
    {
        $gastoItem->delete();

        return redirect()->route('gasto-items.index')->with('success', 'Ítem eliminado correctamente.');
    }
}
