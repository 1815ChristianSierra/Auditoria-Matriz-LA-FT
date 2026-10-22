<?php

namespace App\Http\Controllers;

use App\Models\Evaluacion;
use App\Models\VersionParametros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SensibilidadController extends Controller
{
    // Pantalla "Análisis de sensibilidad": pesos base contra pesos simulados (la simulación no se guarda)
    public function index(Request $request)
    {
        // Evaluación: la de la URL; si no, la más reciente que ya tenga resultados (igual que el tablero)
        $evaluaciones = Evaluacion::with('version')->orderByDesc('id')->get();
        $evaluacion = $request->filled('evaluacion') ? $evaluaciones->firstWhere('codigo', $request->evaluacion) : null;

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

        // Versión base: la elegida o la que usa la evaluación (las rechazadas no se ofrecen)
        $listaVersiones = VersionParametros::where('estado', '<>', 'rechazada')->orderByDesc('id')->get();
        $versionBase = $request->filled('version_base') ? $listaVersiones->firstWhere('numero', $request->version_base) : null;
        $versionBase = $versionBase ?? ($evaluacion ? $evaluacion->version : null);
        //dd($versionBase);

        $factores = [];
        $ranking = [];

        if ($evaluacion && $versionBase) {
            $contexto = $this->contexto($versionBase);
            $eventos = $this->eventos($evaluacion);

            // Al abrir, el simulado es igual al base: los cambios aparecen al mover los pesos y pulsar "Simular"
            $simulacion = $this->simulacion($eventos, $contexto['pesos'], $contexto['pesos'], $contexto);
            $ranking = $simulacion['ranking'];

            foreach ($contexto['claves'] as $clave => $factorId) {
                $peso = $this->numero($contexto['pesos'][$factorId]);
                $factores[] = [
                    'clave' => $clave,
                    'nombre' => $contexto['nombres'][$factorId],
                    'base' => $peso,
                    'simulado' => $peso,
                    'max' => max(50, (int) ceil($peso)),
                    'paso' => fmod($peso * 2, 1) == 0 ? 0.5 : 0.01,
                ];
            }
        }

        $listaEvaluaciones = $evaluaciones->pluck('codigo')->all();
        $evaluacionSel = $evaluacion ? $evaluacion->codigo : null;
        $versiones = $listaVersiones->mapWithKeys(function ($v) {
            return [$v->numero => $v->numero . ' · ' . $v->estado];
        })->all();
        $versionSel = $versionBase ? $versionBase->numero : null;

        return view('sensibilidad.index', compact('listaEvaluaciones', 'evaluacionSel', 'versiones', 'versionSel', 'factores', 'ranking'));
    }

    // Botón "Simular": calcula el ranking con los pesos simulados y lo devuelve (no guarda nada)
    public function simular(Request $request)
    {
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'evaluacion' => 'required|string|exists:evaluacion,codigo',
            'version_base' => 'required|string|exists:version_parametros,numero',
            'pesos' => 'required|array',
            'pesos.*' => 'required|numeric|min:0|max:100',
        ]);

        $evaluacion = Evaluacion::where('codigo', $request->evaluacion)->firstOrFail();
        $versionBase = VersionParametros::where('numero', $request->version_base)->firstOrFail();
        $contexto = $this->contexto($versionBase);

        // Pesos simulados de cada factor de la versión base; el que no llegue conserva su peso base
        $simulados = [];
        foreach ($contexto['claves'] as $clave => $factorId) {
            $simulados[$factorId] = $request->has('pesos.' . $clave) ? (float) $request->input('pesos.' . $clave) : $contexto['pesos'][$factorId];
        }

        $suma = round(array_sum($simulados), 2);
        if (abs($suma - 100) > 0.01) {
            return response()->json(['message' => 'Los pesos simulados deben sumar 100 % (suma actual: ' . $suma . ' %).'], 422);
        }

        $simulacion = $this->simulacion($this->eventos($evaluacion), $contexto['pesos'], $simulados, $contexto);

        return response()->json([
            'suma' => $suma,
            'ranking' => $simulacion['ranking'],
            'kpis' => $simulacion['kpis'],
        ]);
    }

    // Lo que define una versión: factores y pesos, bandas, reglas, topes y puntos de agravantes y controles
    private function contexto($version)
    {
        $filas = DB::table('parametro_factor as pf')
            ->join('factor as f', 'f.id', '=', 'pf.factor_id')
            ->where('pf.version_id', $version->id)
            ->orderBy('f.id')
            ->get(['f.id', 'f.codigo', 'f.nombre', 'pf.peso']);

        $pesos = [];
        $claves = [];
        $nombres = [];
        foreach ($filas as $f) {
            $pesos[$f->id] = (float) $f->peso;
            $claves[strtolower($f->codigo)] = $f->id;
            $nombres[$f->id] = $f->nombre;
        }

        // Bandas de la más alta a la más baja (el puntaje cae en la banda más alta cuyo "desde" no supera el residual)
        $bandas = DB::table('banda')->where('version_id', $version->id)->orderByDesc('desde')->get(['nombre', 'orden', 'desde'])->map(function ($b) {
            return ['nombre' => $b->nombre, 'orden' => (int) $b->orden, 'desde' => (float) $b->desde];
        })->all();

        $ordenBandas = [];
        foreach ($bandas as $b) {
            $ordenBandas[$b['nombre']] = $b['orden'];
        }

        $reglas = DB::table('parametro_regla')->where('version_id', $version->id)->get(['factor_id', 'nivel_minimo', 'banda_forzada'])->map(function ($r) {
            return ['factor_id' => $r->factor_id, 'nivel_minimo' => (int) $r->nivel_minimo, 'banda' => $r->banda_forzada];
        })->all();

        $agravantes = DB::table('parametro_agravante')->where('version_id', $version->id)->pluck('puntos', 'agravante_id')->map(function ($p) {
            return (float) $p;
        })->all();
        $controles = DB::table('parametro_control')->where('version_id', $version->id)->pluck('puntos_resta', 'control_id')->map(function ($p) {
            return (float) $p;
        })->all();

        return [
            'pesos' => $pesos,
            'claves' => $claves,
            'nombres' => $nombres,
            'bandas' => $bandas,
            'orden_bandas' => $ordenBandas,
            'reglas' => $reglas,
            'agravantes' => $agravantes,
            'controles' => $controles,
            'tope_agravantes' => (float) $version->tope_agravantes,
            'tope_controles' => (float) $version->tope_controles,
            'dato_faltante' => $version->puntaje_dato_faltante,
        ];
    }

    // Eventos de la evaluación que ya tienen resultado, con sus niveles, agravantes y controles
    private function eventos($evaluacion)
    {
        $filas = DB::table('evento as e')
            ->where('e.evaluacion_id', $evaluacion->id)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('resultado as r')->whereColumn('r.evento_id', 'e.id')->where('r.vigente', true);
            })
            ->orderBy('e.codigo')
            ->get(['e.id', 'e.codigo']);

        $ids = $filas->pluck('id')->all();

        $niveles = DB::table('calificacion')->whereIn('evento_id', $ids)->get(['evento_id', 'factor_id', 'nivel'])->groupBy('evento_id');
        $agravantes = DB::table('evento_agravante')->whereIn('evento_id', $ids)->get(['evento_id', 'agravante_id'])->groupBy('evento_id');
        $controles = DB::table('evento_control')->whereIn('evento_id', $ids)->get(['evento_id', 'control_id'])->groupBy('evento_id');

        return $filas->map(function ($e) use ($niveles, $agravantes, $controles) {
            $porFactor = [];
            foreach ($niveles->get($e->id, collect()) as $n) {
                $porFactor[$n->factor_id] = (int) $n->nivel;
            }

            return [
                'codigo' => $e->codigo,
                'niveles' => $porFactor,
                'agravantes' => $agravantes->get($e->id, collect())->pluck('agravante_id')->all(),
                'controles' => $controles->get($e->id, collect())->pluck('control_id')->all(),
            ];
        })->all();
    }

    // Ranking con los pesos base y con los simulados, y lo que cambia entre uno y otro
    private function simulacion($eventos, $pesosBase, $pesosSimulados, $contexto)
    {
        $base = [];
        $simulado = [];
        foreach ($eventos as $evento) {
            $base[$evento['codigo']] = $this->calcular($evento, $pesosBase, $contexto);
            $simulado[$evento['codigo']] = $this->calcular($evento, $pesosSimulados, $contexto);
        }

        $posicionBase = $this->posiciones($base, $contexto['orden_bandas']);
        $posicionSim = $this->posiciones($simulado, $contexto['orden_bandas']);

        $ranking = [];
        $cambianBanda = 0;
        $cambiaOrden = 0;
        $mayorDelta = 0;

        foreach ($eventos as $evento) {
            $c = $evento['codigo'];
            $delta = abs($simulado[$c]['residual'] - $base[$c]['residual']);

            $cambianBanda += $base[$c]['banda'] !== $simulado[$c]['banda'] ? 1 : 0;
            $cambiaOrden += $posicionBase[$c] !== $posicionSim[$c] ? 1 : 0;
            $mayorDelta = max($mayorDelta, $delta);

            $ranking[] = [
                'evento' => $c,
                'pos_base' => $posicionBase[$c],
                'base' => $base[$c]['residual'],
                'pos_sim' => $posicionSim[$c],
                'sim' => $simulado[$c]['residual'],
                'banda_base' => Str::slug($base[$c]['banda']),
                'banda_base_nombre' => $base[$c]['banda'],
                'banda_sim' => Str::slug($simulado[$c]['banda']),
                'banda_sim_nombre' => $simulado[$c]['banda'],
            ];
        }

        // Se ordena como el ranking oficial: primero la posición base
        usort($ranking, function ($a, $b) {
            return [$a['pos_base'], $a['evento']] <=> [$b['pos_base'], $b['evento']];
        });

        return [
            'ranking' => $ranking,
            'kpis' => ['cambian_banda' => $cambianBanda, 'cambia_orden' => $cambiaOrden, 'mayor_delta' => round($mayorDelta, 2)],
        ];
    }

    // Riesgo residual y banda de un evento con unos pesos: la misma cuenta de sp_calcular_resultado
    private function calcular($evento, $pesos, $contexto)
    {
        // Factor sin calificación: se toma el puntaje de dato faltante de la versión
        $nivelDe = function ($factorId) use ($evento, $contexto) {
            return $evento['niveles'][$factorId] ?? (int) $contexto['dato_faltante'];
        };

        // Riesgo inherente: suma de peso x nivel / 5 de cada factor, cada aporte redondeado a 2 decimales
        $inherente = 0.0;
        foreach ($pesos as $factorId => $peso) {
            $inherente += round($peso * $nivelDe($factorId) / 5, 2);
        }

        // Agravantes suman y controles restan, cada grupo con su tope; solo cuentan los que la versión define
        $agravantes = 0.0;
        foreach ($evento['agravantes'] as $id) {
            $agravantes += $contexto['agravantes'][$id] ?? 0;
        }
        $controles = 0.0;
        foreach ($evento['controles'] as $id) {
            $controles += $contexto['controles'][$id] ?? 0;
        }
        $agravantes = min($agravantes, $contexto['tope_agravantes']);
        $controles = min($controles, $contexto['tope_controles']);

        $residual = round(max(0, min(100, $inherente + $agravantes - $controles)), 2);

        // Banda: la más alta cuyo "desde" no supera el residual
        $banda = $contexto['bandas'] ? end($contexto['bandas'])['nombre'] : '—';
        foreach ($contexto['bandas'] as $b) {
            if ($b['desde'] <= $residual) {
                $banda = $b['nombre'];
                break;
            }
        }

        // Reglas de escalamiento: la más severa que se cumpla sube la banda si es más alta que la del puntaje
        $mejorOrden = 0;
        $mejorBanda = null;
        foreach ($contexto['reglas'] as $r) {
            if (!isset($pesos[$r['factor_id']]) && !isset($evento['niveles'][$r['factor_id']])) {
                continue;
            }
            if ($nivelDe($r['factor_id']) >= $r['nivel_minimo'] && ($contexto['orden_bandas'][$r['banda']] ?? 0) > $mejorOrden) {
                $mejorOrden = $contexto['orden_bandas'][$r['banda']];
                $mejorBanda = $r['banda'];
            }
        }
        if ($mejorBanda !== null && $mejorOrden > ($contexto['orden_bandas'][$banda] ?? 0)) {
            $banda = $mejorBanda;
        }

        return ['residual' => $residual, 'banda' => $banda];
    }

    // Posición de cada evento: banda más alta primero y luego residual; los empates comparten posición
    private function posiciones($resultados, $ordenBandas)
    {
        $posiciones = [];
        foreach ($resultados as $codigo => $r) {
            $mejores = 0;
            foreach ($resultados as $otro) {
                $ordenOtro = $ordenBandas[$otro['banda']] ?? 0;
                $ordenPropio = $ordenBandas[$r['banda']] ?? 0;
                if ($ordenOtro > $ordenPropio || ($ordenOtro === $ordenPropio && $otro['residual'] > $r['residual'])) {
                    $mejores++;
                }
            }
            $posiciones[$codigo] = $mejores + 1;
        }

        return $posiciones;
    }

    // Quita los ceros sobrantes: 20.00 → 20, 12.50 → 12.5
    private function numero($valor)
    {
        $valor = (float) $valor;

        return $valor == floor($valor) ? (int) $valor : $valor;
    }
}
 