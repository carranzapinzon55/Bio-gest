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
            'codigo_cupon'        => ['required', 'string', 'min:3', 'max:50', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9_-]+$/', 'unique:promocion,codigo_cupon'],
            'descuento_porcentaje'=> ['required', 'numeric', 'min:1', 'max:100'],
            'fecha_inicio'        => ['required', 'date', 'after_or_equal:' . now()->startOfDay()->format('Y-m-d H:i:s')],
            'fecha_fin'           => ['required', 'date', 'after:fecha_inicio', 'after_or_equal:' . now()->addDay()->startOfDay()->format('Y-m-d H:i:s')],
            'estado'              => ['required', 'in:Activo,Inactivo'],
        ], [
            'codigo_cupon.required'         => 'El código del cupón es obligatorio.',
            'codigo_cupon.regex'            => 'El código del cupón debe ser alfanumérico sin espacios y contener al menos una letra (Ej. TENEBRIO10).',
            'codigo_cupon.unique'           => 'Ya existe una promoción con este código de cupón.',
            'descuento_porcentaje.required' => 'El porcentaje de descuento es obligatorio.',
            'descuento_porcentaje.min'      => 'El descuento mínimo es 1%.',
            'descuento_porcentaje.max'      => 'El descuento máximo es 100%.',
            'fecha_inicio.required'         => 'La fecha de inicio es requerida.',
            'fecha_inicio.after_or_equal'   => 'La fecha de inicio de una nueva promoción no puede estar en el pasado.',
            'fecha_fin.required'            => 'La fecha de vencimiento es requerida.',
            'fecha_fin.after'               => 'La fecha de cierre/vencimiento debe ser estrictamente posterior a la fecha de inicio.',
            'fecha_fin.after_or_equal'      => 'La fecha de cierre/vencimiento de la promoción debe ser mínimo a partir de mañana (no puede vencer hoy mismo).',
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
            'codigo_cupon'        => ['required', 'string', 'min:3', 'max:50', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9_-]+$/', 'unique:promocion,codigo_cupon,' . $id_promocion . ',id_promocion'],
            'descuento_porcentaje'=> ['required', 'numeric', 'min:1', 'max:100'],
            'fecha_inicio'        => ['required', 'date'],
            'fecha_fin'           => ['required', 'date', 'after:fecha_inicio', 'after_or_equal:' . now()->addDay()->startOfDay()->format('Y-m-d H:i:s')],
            'estado'              => ['required', 'in:Activo,Inactivo'],
        ], [
            'codigo_cupon.regex'       => 'El código del cupón debe ser alfanumérico sin espacios y contener al menos una letra.',
            'fecha_fin.after'          => 'La fecha de cierre/vencimiento debe ser estrictamente posterior a la fecha de inicio.',
            'fecha_fin.after_or_equal' => 'La fecha de cierre/vencimiento de la promoción debe ser mínimo a partir de mañana (no puede vencer hoy).',
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
