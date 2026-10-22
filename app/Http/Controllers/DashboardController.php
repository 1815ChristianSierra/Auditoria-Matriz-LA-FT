<?php

namespace App\Http\Controllers;

use App\Models\Evaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Evaluaciones para el selector (la más reciente primero); se abre la de la URL
        $evaluaciones = Evaluacion::with('version')->orderByDesc('id')->get();
        $evaluacion = $request->filled('evaluacion') ? $evaluaciones->firstWhere('codigo', $request->evaluacion) : null;

        // Si no se eligió ninguna: la más reciente que ya tenga resultados calculados; si ninguna, la más reciente
        if (!$evaluacion) {
            $conResultados = DB::table('resultado as r')
                ->join('evento as e', 'e.id', '=', 'r.evento_id')
                ->where('r.vigente', true)
                ->distinct()
                ->pluck('e.evaluacion_id');

            $evaluacion = $evaluaciones->first(function ($ev) use ($conResultados) {
                return $conResultados->contains($ev->id);
            }) ?? $evaluaciones->first();
        }
        //dd($evaluacion);

        // Filtros de la barra: la opción "Solo sin decisión" viene activada la primera vez
        $filtros = [
            'banda' => (string) $request->banda,
            'cliente' => (string) $request->cliente,
            'q' => trim((string) $request->q),
            'solo_sin_decision' => $request->has('solo_sin_decision') ? $request->boolean('solo_sin_decision') : true,
        ];

        $todos = collect();
        $sinCalcular = [];
        $conDatoFaltante = [];
        $coincidenciasPorVerificar = 0;

        if ($evaluacion) {
            // Ranking de la evaluación (vista v_ranking_prioridad): banda más alta primero, luego residual
            $filas = DB::table('v_ranking_prioridad')->where('evaluacion', $evaluacion->codigo)->orderBy('prioridad')->orderBy('evento')->get();

            // Última decisión registrada sobre el resultado vigente de cada evento
            $decisiones = [];
            $registradas = DB::table('decision_seguimiento as d')
                ->join('resultado as r', 'r.id', '=', 'd.resultado_id')
                ->join('evento as e', 'e.id', '=', 'r.evento_id')
                ->where('e.evaluacion_id', $evaluacion->id)
                ->where('r.vigente', true)
                ->orderBy('d.id')
                ->get(['e.codigo as evento', 'd.decision']);
            foreach ($registradas as $d) {
                $decisiones[$d->evento] = $d->decision;
            }

            $todos = $filas->map(function ($f) use ($decisiones) {
                return [
                    'prioridad' => (int) $f->prioridad,
                    'id' => $f->evento,
                    'cliente' => $f->cliente,
                    'inherente' => (float) $f->riesgo_inherente,
                    'agravante' => (float) $f->puntos_agravantes,
                    'controles' => (float) $f->puntos_controles,
                    'residual' => (float) $f->riesgo_residual,
                    'banda' => Str::slug($f->banda),
                    'regla' => $f->regla_escalamiento,
                    'decision' => $decisiones[$f->evento] ?? null,
                ];
            });

            // Eventos que todavía no tienen resultado y eventos con algún factor asignado por dato faltante
            $sinCalcular = DB::table('evento as e')
                ->where('e.evaluacion_id', $evaluacion->id)
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))->from('resultado as r')->whereColumn('r.evento_id', 'e.id')->where('r.vigente', true);
                })
                ->orderBy('e.codigo')->pluck('e.codigo')->all();

            $conDatoFaltante = DB::table('calificacion as c')
                ->join('evento as e', 'e.id', '=', 'c.evento_id')
                ->where('e.evaluacion_id', $evaluacion->id)
                ->where('c.dato_faltante', true)
                ->distinct()->orderBy('e.codigo')->pluck('e.codigo')->all();

            // Coincidencias en listas pendientes de verificar, de los clientes de esta evaluación
            $coincidenciasPorVerificar = DB::table('coincidencia_lista')
                ->where('tipo_coincidencia', 'parcial')
                ->whereIn('cliente_id', DB::table('evento')->where('evaluacion_id', $evaluacion->id)->select('cliente_id'))
                ->count();
        }

        // Resumen de la evaluación completa (las tarjetas y la distribución no dependen de los filtros)
        $total = $todos->count();
        $conteo = $todos->countBy('banda')->all();
        $sinDecision = $todos->filter(function ($e) {
            return $this->sinDecision($e);
        })->count();

        // Ranking filtrado: es lo que muestran la tabla y el gráfico
        $eventos = $todos->filter(function ($e) use ($filtros) {
            if ($filtros['banda'] !== '' && $e['banda'] !== $filtros['banda']) {
                return false;
            }
            if ($filtros['cliente'] !== '' && $e['cliente'] !== $filtros['cliente']) {
                return false;
            }
            if ($filtros['q'] !== '' && stripos($e['id'], $filtros['q']) === false) {
                return false;
            }
            if ($filtros['solo_sin_decision'] && !$this->sinDecision($e)) {
                return false;
            }

            return true;
        })->values()->all();

        // Alertas: solo aparecen las que tienen algo que mostrar
        $alertas = [];
        if ($conDatoFaltante) {
            $alertas[] = ['texto' => count($conDatoFaltante) . (count($conDatoFaltante) === 1 ? ' evento con dato faltante' : ' eventos con dato faltante'), 'detalle' => $this->codigos($conDatoFaltante), 'tipo' => 'dato_faltante'];
        }
        if ($sinCalcular) {
            $alertas[] = ['texto' => count($sinCalcular) . (count($sinCalcular) === 1 ? ' evento sin calcular' : ' eventos sin calcular'), 'detalle' => $this->codigos($sinCalcular), 'tipo' => 'sin_calcular'];
        }
        if ($coincidenciasPorVerificar) {
            $alertas[] = ['texto' => $coincidenciasPorVerificar . ($coincidenciasPorVerificar === 1 ? ' coincidencia en listas por verificar' : ' coincidencias en listas por verificar'), 'detalle' => null, 'tipo' => 'coincidencias'];
        }

        // Datos de la barra de filtros
        $listaEvaluaciones = $evaluaciones->pluck('codigo')->all();
        $evaluacionSel = $evaluacion ? $evaluacion->codigo : null;
        $evaluacionId = $evaluacion ? $evaluacion->id : null;
        $version = $evaluacion && $evaluacion->version ? ['numero' => $evaluacion->version->numero, 'estado' => $evaluacion->version->estado] : null;
        $clientes = $todos->pluck('cliente')->unique()->sort()->values()->all();
        $datoFaltante = count($conDatoFaltante);

        return view('dashboard', compact(
            'listaEvaluaciones', 'evaluacionSel', 'evaluacionId', 'version', 'filtros', 'clientes', 'eventos',
            'total', 'conteo', 'sinDecision', 'datoFaltante', 'alertas'
        ));
    }

    // Un evento sigue "sin decisión" mientras no haya una o solo esté "En revisión"
    private function sinDecision($evento)
    {
        return $evento['decision'] === null || $evento['decision'] === 'en_revision';
    }

    // Hasta tres códigos de evento: EV-001, EV-002, EV-003 …
    private function codigos($lista)
    {
        $texto = implode(', ', array_slice($lista, 0, 3));

        return count($lista) > 3 ? $texto . ' …' : $texto;
    }
}
