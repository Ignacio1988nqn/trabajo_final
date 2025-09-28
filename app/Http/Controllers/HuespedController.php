<?php

namespace App\Http\Controllers;

use App\Models\Huespedes;
use App\Models\personas;
use App\Models\empresas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class HuespedController extends Controller
{
    public function index()
    {
        return Inertia::render('Huespedes/Index', [
            'huespedes' => Huespedes::with(['personas', 'empresas'])->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Huespedes/Create');
    }

    public function store(Request $request)
    {
        // Validar los datos
        $validated = $request->validate([
            'tipo_huesped' => 'required|in:persona,empresa',
            'telefono' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:huespedes,email',
            'nombre' => 'required_if:tipo_huesped,persona|string|max:255|nullable',
            'apellido' => 'required_if:tipo_huesped,persona|string|max:255|nullable',
            'documento' => 'required_if:tipo_huesped,persona|string|max:255|nullable|unique:personas,documento',
            'razon_social' => 'required_if:tipo_huesped,empresa|string|max:255|nullable',
            'cuit' => 'required_if:tipo_huesped,empresa|string|max:255|unique:empresas,cuit|nullable',
        ]);

        // Usar una transacción para garantizar consistencia
        return DB::transaction(function () use ($validated) {

            $huesped = Huespedes::create([
                'tipo_huesped' => $validated['tipo_huesped'],
                'telefono' => $validated['telefono'],
                'email' => $validated['email'],
                'fecha_registro' => now(),
            ]);

            // Crear persona o empresa según el tipo
            if ($validated['tipo_huesped'] === 'persona') {
                personas::create([
                    'huesped_id' => $huesped->id,
                    'nombre' => $validated['nombre'],
                    'apellido' => $validated['apellido'],
                    'documento' => $validated['documento'],
                ]);
            } else {
                empresas::create([
                    'huesped_id' => $huesped->id,
                    'razon_social' => $validated['razon_social'],
                    'cuit' => $validated['cuit'],
                ]);
            }

            return redirect()->route('huesped.index')->with('success', 'Huésped creado con éxito.');
        });
    }

    public function update(Request $request, $id)
    {
        $huesped = Huespedes::findOrFail($id);

        $validated = $request->validate([
            'tipo_huesped' => 'required|in:persona,empresa',
            'telefono' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:huespedes,email,' . $huesped->id,
            'nombre' => 'required_if:tipo_huesped,persona|string|max:255|nullable',
            'apellido' => 'required_if:tipo_huesped,persona|string|max:255|nullable',
            'documento' => 'required_if:tipo_huesped,persona|string|max:255|nullable',
            'razon_social' => 'required_if:tipo_huesped,empresa|string|max:255|nullable',
            'cuit' => 'required_if:tipo_huesped,empresa|string|max:255|unique:empresas,cuit,' . ($huesped->empresa ? $huesped->empresa->huesped_id : null) . ',huesped_id|nullable',
        ]);

        try {
            return DB::transaction(function () use ($validated, $huesped) {
                // Actualizar el registro de huesped
                $huesped->update([
                    'tipo_huesped' => $validated['tipo_huesped'],
                    'telefono' => $validated['telefono'],
                    'email' => $validated['email'],
                ]);

                // Manejar persona o empresa según tipo_huesped
                if ($validated['tipo_huesped'] === 'persona') {
                    // Eliminar registro de empresa si existe
                    if ($huesped->empresa) {
                        $huesped->empresa->delete();
                    }
                    // Actualizar o crear registro de persona
                    Personas::updateOrCreate(
                        ['huesped_id' => $huesped->id], // Condición para buscar
                        [
                            'nombre' => $validated['nombre'],
                            'apellido' => $validated['apellido'],
                            'documento' => $validated['documento'],
                        ]
                    );
                } elseif ($validated['tipo_huesped'] === 'empresa') {
                    // Eliminar registro de persona si existe
                    if ($huesped->persona) {
                        $huesped->persona->delete();
                    }
                    // Actualizar o crear registro de empresa
                    Empresas::updateOrCreate(
                        ['huesped_id' => $huesped->id], // Condición para buscar
                        [
                            'razon_social' => $validated['razon_social'],
                            'cuit' => $validated['cuit'],
                        ]
                    );
                }

                return redirect()->route('huesped.index')->with('success', 'Huésped actualizado con éxito.');
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error al actualizar el huésped: ' . $e->getMessage()]);
        }
    }
    public function edit($id)
    {
        $huesped = Huespedes::with(['personas', 'empresas'])->findOrFail($id);

        return Inertia::render('Huespedes/Edit', [
            'huesped' => [
                'id' => $huesped->id,
                'tipo_huesped' => $huesped->tipo_huesped,
                'telefono' => $huesped->telefono,
                'email' => $huesped->email,
                'nombre' => $huesped->personas ? $huesped->personas->nombre : null,
                'apellido' => $huesped->personas ? $huesped->personas->apellido : null,
                'documento' => $huesped->personas ? $huesped->personas->documento : null,
                'razon_social' => $huesped->empresas ? $huesped->empresas->razon_social : null,
                'cuit' => $huesped->empresas ? $huesped->empresas->cuit : null,
            ],
        ]);
    }

    public function destroy($id)
    {
        try {
            $huesped = Huespedes::findOrFail($id);
            $huesped->delete();

            return redirect()->route('huesped.index')->with('success', 'Huésped eliminado con éxito.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error al eliminar el huésped: ' . $e->getMessage()]);
        }
    }
}
