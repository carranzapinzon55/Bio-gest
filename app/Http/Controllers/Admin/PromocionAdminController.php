<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromocionAdminController extends Controller
{
    /**
     * Listado de promociones y cupones desde MySQL.
     */
    public function index(): View
    {
        $promociones = Promocion::orderByDesc('id_promocion')->get();
        return view('admin.promociones.index', compact('promociones'));
    }

    /**
     * Crear una nueva promoción o cupón de descuento.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'codigo_cupon'        => ['required', 'string', 'min:3', 'max:50', 'unique:promocion,codigo_cupon'],
            'descuento_porcentaje'=> ['required', 'numeric', 'min:1', 'max:100'],
            'fecha_inicio'        => ['required', 'date'],
            'fecha_fin'           => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado'              => ['required', 'in:Activo,Inactivo'],
        ], [
            'codigo_cupon.required'        => 'El código del cupón es obligatorio.',
            'codigo_cupon.unique'          => 'Ya existe una promoción con este código de cupón.',
            'descuento_porcentaje.required'=> 'El porcentaje de descuento es obligatorio.',
            'descuento_porcentaje.min'     => 'El descuento mínimo es 1%.',
            'descuento_porcentaje.max'     => 'El descuento máximo es 100%.',
            'fecha_inicio.required'        => 'La fecha de inicio es requerida.',
            'fecha_fin.required'           => 'La fecha de vencimiento es requerida.',
            'fecha_fin.after_or_equal'     => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ]);

        $datos['codigo_cupon'] = strtoupper(trim($datos['codigo_cupon']));

        $promocion = Promocion::create($datos);

        return redirect()->route('admin.promociones.index')
            ->with('success', "Promoción '{$promocion->codigo_cupon}' creada exitosamente.");
    }

    /**
     * Actualizar una promoción existente en MySQL.
     */
    public function update(Request $request, int $id_promocion): RedirectResponse
    {
        $promocion = Promocion::findOrFail($id_promocion);

        $datos = $request->validate([
            'codigo_cupon'        => ['required', 'string', 'min:3', 'max:50', 'unique:promocion,codigo_cupon,' . $id_promocion . ',id_promocion'],
            'descuento_porcentaje'=> ['required', 'numeric', 'min:1', 'max:100'],
            'fecha_inicio'        => ['required', 'date'],
            'fecha_fin'           => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado'              => ['required', 'in:Activo,Inactivo'],
        ]);

        $datos['codigo_cupon'] = strtoupper(trim($datos['codigo_cupon']));

        $promocion->update($datos);

        return redirect()->route('admin.promociones.index')
            ->with('success', "Promoción '{$promocion->codigo_cupon}' actualizada correctamente.");
    }

    /**
     * Alternar estado Activo / Inactivo de un cupón.
     */
    public function toggleEstado(int $id_promocion): RedirectResponse
    {
        $promocion = Promocion::findOrFail($id_promocion);
        $nuevoEstado = $promocion->estado === 'Activo' ? 'Inactivo' : 'Activo';
        $promocion->update(['estado' => $nuevoEstado]);

        return redirect()->route('admin.promociones.index')
            ->with('success', "El cupón '{$promocion->codigo_cupon}' ahora se encuentra {$nuevoEstado}.");
    }

    /**
     * Eliminar una promoción.
     */
    public function destroy(int $id_promocion): RedirectResponse
    {
        $promocion = Promocion::findOrFail($id_promocion);
        $codigo = $promocion->codigo_cupon;
        $promocion->delete();

        return redirect()->route('admin.promociones.index')
            ->with('success', "La promoción '{$codigo}' fue eliminada exitosamente.");
    }
}
