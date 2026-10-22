<?php

namespace App\Http\Controllers;

use App\Models\Evaluacion;
use App\Models\ParametroFactor;
use App\Models\User;
use App\Models\VersionParametros;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EvaluacionesController extends Controller
{
    public function index(Request $request)
    {
        // Obtener las evaluaciones con su responsable, su versión y el número de eventos; se filtran con los datos de la barra
        $consulta = Evaluacion::with(['responsable', 'version'])->withCount('eventos');

        if ($request->filled('estado')) {
            $consulta->where('estado', $request->estado);
        }
        if ($request->filled('responsable')) {
            $consulta->whereHas('responsable', function ($q) use ($request) {
                $q->where('username', $request->responsable);
            });
        }
        if ($request->filled('q')) {
            $consulta->where('codigo', 'like', '%' . $request->q . '%');
        }

        $filas = $consulta->orderByDesc('id')->get();
        //dd($filas);

        // Evaluaciones con la forma que espera la vista
        $evaluaciones = $filas->map(function ($e) {
            return [
                'id' => $e->id,
                'codigo' => $e->codigo,
                'inicio' => $e->periodo_inicio->format('Y-m-d'),
                'fin' => $e->periodo_fin->format('Y-m-d'),
                'alcance' => $e->alcance,
                'version' => $e->version ? $e->version->numero : '—',
                'eventos' => $e->eventos_count,
                'estado' => $e->estado,
                'responsable' => $e->responsable ? $e->responsable->username : '—',
            ];
        })->all();

        $total = Evaluacion::count();

        // Responsables que aparecen en las evaluaciones, para el filtro
        $responsables = User::whereIn('id', Evaluacion::select('responsable_id'))->orderBy('username')->pluck('username')->all();

        // Datos del modal "Nueva evaluación"
        $siguienteCodigo = $this->siguienteCodigo();

        $responsablesModal = User::where('estado', 'activo')
            ->whereHas('rol', function ($q) {
                $q->whereIn('nombre', ['analista', 'supervisor']);
            })
            ->orderBy('username')->pluck('username', 'username')->all();

        $versionesAprobadas = VersionParametros::where('estado', 'aprobada')->orderByDesc('id')->get()->mapWithKeys(function ($v) {
            return [$v->numero => $v->numero . ' · aprobada'];
        })->all();

        return view('evaluaciones.index', compact('evaluaciones', 'total', 'responsables', 'siguienteCodigo', 'responsablesModal', 'versionesAprobadas'));
    }

    public function store(Request $request)
    {
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'responsable' => 'required|string|exists:users,username',
            'inicio' => 'required|date',
            'fin' => 'required|date|after_or_equal:inicio',
            'alcance' => 'required|string',
            'version' => 'required|string|exists:version_parametros,numero',
        ]);

        // Solo se pueden usar versiones aprobadas (la BD también lo rechaza)
        $version = VersionParametros::where('numero', $request->version)->firstOrFail();
        if ($version->estado !== 'aprobada') {
            return back()->withInput()->withErrors(['version' => 'La versión ' . $version->numero . ' no está aprobada: solo se pueden usar versiones aprobadas.']);
        }

        $responsable = User::where('username', $request->responsable)->firstOrFail();

        try {
            $evaluacion = DB::transaction(function () use ($request, $version, $responsable) {
                $nueva = Evaluacion::create([
                    'codigo' => $this->siguienteCodigo(),
                    'periodo_inicio' => $request->inicio,
                    'periodo_fin' => $request->fin,
                    'alcance' => trim($request->alcance),
                    'responsable_id' => $responsable->id,
                    'version_id' => $version->id,
                    'estado' => 'abierta',
                ]);

                $this->registrarBitacora('CREAR_EVALUACION', $nueva->id, null, [
                    'codigo' => $nueva->codigo,
                    'version' => $version->numero,
                    'responsable' => $responsable->username,
                ], trim($request->alcance));

                return $nueva;
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return back()->withInput()->withErrors(['version' => 'No se pudo crear la evaluación: ' . $mensaje]);
        }

        // Redirigir a la lista de evaluaciones con un mensaje de éxito
        return redirect()->route('evaluaciones.index')->with('success', 'Evaluación ' . $evaluacion->codigo . ' creada correctamente.');
    }

    public function show($id)
    {
        $registro = Evaluacion::with(['responsable', 'version', 'eventos.cliente', 'eventos.calificaciones', 'eventos.resultadoVigente.decisiones'])->findOrFail($id);

        // Eventos con la forma que espera la vista
        $eventos = $registro->eventos->sortBy('codigo')->map(function ($ev) {
            $resultado = $ev->resultadoVigente;
            $decision = $resultado ? $resultado->decisiones->sortByDesc('id')->first() : null;

            return [
                'id' => $ev->codigo,
                'pk' => $ev->id,
                'cliente' => $ev->cliente ? $ev->cliente->codigo : '—',
                'tipo' => $ev->tipo_riesgo,
                'monto' => (float) $ev->monto,
                // Factores calificados por una persona (los que asignó la regla de dato faltante no cuentan)
                'factores' => $ev->calificaciones->where('dato_faltante', false)->count(),
                'inherente' => $resultado ? (float) $resultado->riesgo_inherente : null,
                'residual' => $resultado ? (float) $resultado->riesgo_residual : null,
                'banda' => $resultado ? Str::slug($resultado->banda) : null,
                'decision' => $resultado ? ($decision ? ($decision->decision === 'en_revision' ? 'revision' : $decision->decision) : 'pendiente') : null,
            ];
        })->values()->all();

        $evaluacion = [
            'id' => $registro->id,
            'codigo' => $registro->codigo,
            'inicio' => $registro->periodo_inicio->format('Y-m-d'),
            'fin' => $registro->periodo_fin->format('Y-m-d'),
            'alcance' => $registro->alcance,
            'version' => $registro->version ? $registro->version->numero : '—',
            'version_estado' => $registro->version ? $registro->version->estado : '—',
            'estado' => $registro->estado,
            'responsable' => $registro->responsable ? $registro->responsable->username : '—',
            'eventos' => $eventos,
        ];

        // Factores que tiene la versión de la evaluación
        $factoresTotal = ParametroFactor::where('version_id', $registro->version_id)->count();

        return view('evaluaciones.show', compact('evaluacion', 'factoresTotal'));
    }

    // Botón "Calcular pendientes": calcula con sp_calcular_resultado los eventos que todavía no tienen resultado vigente
    public function calcular($id)
    {
        $evaluacion = Evaluacion::with('eventos.resultadoVigente')->findOrFail($id);

        if ($evaluacion->estado === 'cerrada') {
            return redirect()->route('evaluaciones.show', $evaluacion->id)->with('error', 'La evaluación está cerrada: solo lectura.');
        }

        $pendientes = $evaluacion->eventos->filter(function ($ev) {
            return !$ev->resultadoVigente;
        });

        if ($pendientes->isEmpty()) {
            return redirect()->route('evaluaciones.show', $evaluacion->id)->with('error', 'No hay eventos pendientes de calcular.');
        }

        try {
            DB::transaction(function () use ($pendientes) {
                foreach ($pendientes as $ev) {
                    // Al calcular, los factores sin calificar reciben el puntaje de dato faltante de la versión
                    DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [$ev->id, auth()->id()]);
                }
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return redirect()->route('evaluaciones.show', $evaluacion->id)->with('error', 'No se pudo calcular: ' . $mensaje);
        }

        $n = $pendientes->count();

        return redirect()->route('evaluaciones.show', $evaluacion->id)
            ->with('success', $n . ($n === 1 ? ' evento calculado.' : ' eventos calculados.'));
    }

    // Botón "Cerrar evaluación": exige que todos los eventos tengan resultado vigente y sus factores calificados
    public function cerrar($id)
    {
        $evaluacion = Evaluacion::with(['eventos.resultadoVigente', 'eventos.calificaciones'])->findOrFail($id);

        if ($evaluacion->estado === 'cerrada') {
            return redirect()->route('evaluaciones.show', $evaluacion->id)->with('error', 'La evaluación ya está cerrada.');
        }

        $factoresTotal = ParametroFactor::where('version_id', $evaluacion->version_id)->count();

        $sinResultado = $evaluacion->eventos->filter(function ($ev) {
            return !$ev->resultadoVigente;
        })->pluck('codigo')->all();

        $incompletos = $evaluacion->eventos->filter(function ($ev) use ($factoresTotal) {
            return $ev->calificaciones->count() < $factoresTotal;
        })->pluck('codigo')->all();

        if ($sinResultado || $incompletos) {
            $pendientes = array_values(array_unique(array_merge($sinResultado, $incompletos)));

            return redirect()->route('evaluaciones.show', $evaluacion->id)
                ->with('error', 'No se puede cerrar: faltan resultados o factores en ' . implode(', ', $pendientes) . '. Use «Calcular pendientes».');
        }

        try {
            DB::transaction(function () use ($evaluacion) {
                $evaluacion->estado = 'cerrada';
                $evaluacion->save();

                $this->registrarBitacora('CERRAR_EVALUACION', $evaluacion->id, ['estado' => 'abierta'], [
                    'estado' => 'cerrada',
                    'codigo' => $evaluacion->codigo,
                    'eventos' => $evaluacion->eventos->count(),
                ], 'Cierre de la evaluación');
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return redirect()->route('evaluaciones.show', $evaluacion->id)->with('error', 'No se pudo cerrar: ' . $mensaje);
        }

        return redirect()->route('evaluaciones.show', $evaluacion->id)->with('success', 'Evaluación ' . $evaluacion->codigo . ' cerrada. Queda en solo lectura.');
    }

    // Siguiente código del año: EVAL-2026-02 → EVAL-2026-03
    private function siguienteCodigo()
    {
        $anio = date('Y');

        $mayor = Evaluacion::where('codigo', 'like', 'EVAL-' . $anio . '-%')->pluck('codigo')->map(function ($c) {
            return (int) preg_replace('/^EVAL-\d{4}-/', '', $c);
        })->max() ?? 0;

        return sprintf('EVAL-%s-%02d', $anio, $mayor + 1);
    }

    // Registro en la bitácora con el procedimiento sp_registrar_bitacora
    private function registrarBitacora($accion, $evaluacionId, $anterior, $nuevo, $motivo)
    {
        DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?, ?, ?, @bitacora)', [
            auth()->id(),
            $accion,
            'evaluacion',
            $evaluacionId,
            $anterior ? json_encode($anterior, JSON_UNESCAPED_UNICODE) : null,
            $nuevo ? json_encode($nuevo, JSON_UNESCAPED_UNICODE) : null,
            $motivo,
            null,
        ]);
    }
}
 