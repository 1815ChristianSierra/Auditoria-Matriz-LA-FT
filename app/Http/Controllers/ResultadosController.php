<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\ParametroRegla;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResultadosController extends Controller
{
    // Pantalla "Resultado" de un evento (se abre con el código del evento: /resultados/EV-004)
    public function show($codigo)
    {
        $evento = Evento::with(['evaluacion.version', 'cliente'])->where('codigo', $codigo)->firstOrFail();
        $evaluacion = $evento->evaluacion;
        $version = $evaluacion->version;

        // Resultado vigente del evento; si todavía no se calcula no hay nada que mostrar
        $resultado = DB::table('resultado')->where('evento_id', $evento->id)->where('vigente', true)->first();
        //dd($resultado);

        if (!$resultado) {
            return redirect()->route('eventos.calificar', $evento->id)
                ->with('error', 'El evento ' . $evento->codigo . ' todavía no tiene resultado: califíquelo y use «Calcular resultado».');
        }

        // Regla de escalamiento que se cumplió (si hubo una)
        $regla = $resultado->regla_id ? ParametroRegla::find($resultado->regla_id) : null;

        // Detalle por factor: nivel, peso de la versión, aporte calculado por la BD y justificación
        $detalle = DB::table('calificacion as c')
            ->join('factor as f', 'f.id', '=', 'c.factor_id')
            ->join('parametro_factor as pf', function ($join) use ($evaluacion) {
                $join->on('pf.factor_id', '=', 'c.factor_id')->where('pf.version_id', '=', $evaluacion->version_id);
            })
            ->where('c.evento_id', $evento->id)
            ->orderBy('f.id')
            ->get(['c.factor_id', 'f.nombre', 'c.nivel', 'c.aporte', 'c.dato_faltante', 'c.justificacion', 'pf.peso']);

        // El factor que activó la regla se marca en la tabla
        $factores = $detalle->map(function ($d) use ($regla) {
            return [
                'nombre' => $d->nombre,
                'peso' => $this->numero($d->peso),
                'nivel' => (int) $d->nivel,
                'aporte' => (float) $d->aporte,
                'justificacion' => $d->justificacion,
                'destacado' => $regla && $regla->factor_id == $d->factor_id && $d->nivel >= $regla->nivel_minimo,
                'dato_faltante' => (bool) $d->dato_faltante,
            ];
        })->all();

        // Prioridad del evento dentro de su evaluación (vista v_ranking_prioridad de la BD)
        $ranking = DB::table('v_ranking_prioridad')->where('evaluacion', $evaluacion->codigo)->get();
        $fila = $ranking->firstWhere('evento', $evento->codigo);
        $prioridad = ['posicion' => $fila ? (int) $fila->prioridad : 1, 'total' => max($ranking->count(), 1)];

        // Todos los resultados que ha tenido el evento, el más reciente primero
        $historial = DB::table('resultado')->where('evento_id', $evento->id)->orderByDesc('id')->get()->map(function ($r) use ($version) {
            return [
                'fecha' => substr($r->calculado_en, 0, 16),
                'version' => $version->numero,
                'residual' => (float) $r->riesgo_residual,
                'banda' => Str::slug($r->banda),
                'estado' => $r->vigente ? 'vigente' : 'reemplazado',
            ];
        })->all();

        $evaluacionId = $evaluacion->id;
        $evaluacionCodigo = $evaluacion->codigo;
        $resultadoId = $resultado->id;
        $cerrada = $evaluacion->estado === 'cerrada';

        $banda = Str::slug($resultado->banda);
        $inherente = (float) $resultado->riesgo_inherente;
        $agravantes = (float) $resultado->puntos_agravantes;
        $controles = (float) $resultado->puntos_controles;
        $residual = (float) $resultado->riesgo_residual;
        $topeAgravantes = $this->numero($version->tope_agravantes);
        $topeControles = $this->numero($version->tope_controles);

        $evento = ['id' => $evento->codigo, 'pk' => $evento->id, 'cliente' => $evento->cliente ? $evento->cliente->codigo : '—'];
        $version = $version->numero;
        $regla = $regla ? ['codigo' => $regla->codigo, 'texto' => $regla->descripcion] : null;

        return view('resultados.show', compact(
            'evaluacionId', 'evaluacionCodigo', 'evento', 'version', 'banda', 'prioridad', 'regla', 'factores',
            'inherente', 'agravantes', 'controles', 'residual', 'topeAgravantes', 'topeControles', 'historial',
            'resultadoId', 'cerrada'
        ));
    }

    // Botón "Recalcular": calcula un resultado nuevo con sp_calcular_resultado; el anterior queda como "Reemplazado"
    public function store($codigo)
    {
        $evento = Evento::with('evaluacion')->where('codigo', $codigo)->firstOrFail();

        if ($evento->evaluacion->estado === 'cerrada') {
            return redirect()->route('evaluaciones.show', $evento->evaluacion->id)->with('error', 'La evaluación está cerrada: solo lectura.');
        }

        $teniaResultado = DB::table('resultado')->where('evento_id', $evento->id)->where('vigente', true)->exists();

        try {
            DB::transaction(function () use ($evento) {
                // El procedimiento completa los factores sin calificar, calcula y deja el registro en la bitácora
                DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [$evento->id, auth()->id()]);
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return redirect()->route('evaluaciones.show', $evento->evaluacion->id)->with('error', 'No se pudo calcular: ' . $mensaje);
        }

        $texto = $teniaResultado
            ? 'Resultado de ' . $evento->codigo . ' recalculado. El anterior quedó como «Reemplazado».'
            : 'Resultado de ' . $evento->codigo . ' calculado.';

        // Redirigir a la pantalla del resultado con un mensaje de éxito
        return redirect()->route('resultados.show', $evento->codigo)->with('success', $texto);
    }

    // Quita los ceros sobrantes: 20.00 → 20, 2.50 → 2.5
    private function numero($valor)
    {
        $valor = (float) $valor;

        return $valor == floor($valor) ? (int) $valor : $valor;
    }
}
 