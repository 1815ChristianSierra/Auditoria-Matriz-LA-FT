<?php
 
namespace App\Http\Controllers;
 
use App\Models\Agravante;
use App\Models\Calificacion;
use App\Models\Cliente;
use App\Models\Control;
use App\Models\Evaluacion;
use App\Models\Evento;
use App\Models\Factor;
use App\Models\FactorNivel;
use App\Models\ParametroAgravante;
use App\Models\ParametroControl;
use App\Models\ParametroFactor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
 
class EventosController extends Controller
{
    // Pantalla "Registrar evento": se abre desde una evaluación (?evaluacion=1)
    public function create(Request $request)
    {
        $evaluacion = $this->evaluacionAbierta($request->evaluacion);
        if (!($evaluacion instanceof Evaluacion)) {
            return $evaluacion;
        }
 
        // Clientes con lo que muestran las tarjetas de la derecha
        $registros = Cliente::with(['coincidencias.lista'])->withCount('eventos')->orderBy('codigo')->get();
 
        $clientes = $registros->mapWithKeys(function ($c) {
            // Coincidencias en listas que una persona no descartó
            $coincidencia = $c->coincidencias->filter(function ($k) {
                return $k->tipo_coincidencia !== 'descartada';
            })->map(function ($k) {
                $lista = $k->lista ? $k->lista->tipo_lista : '';
 
                return 'Coincidencia ' . strtoupper($k->tipo_coincidencia) . ' en lista ' . $lista . ' (lista sintética).';
            })->first();
 
            return [$c->codigo => [
                'nombre' => $c->nombre_ficticio,
                'tipo' => ($c->tipo_persona === 'juridica' ? 'Jurídica' : 'Individual') . ' · ' . ($c->jurisdiccion_residencia ?? '—'),
                'desde' => $c->fecha_inicio_relacion ? $c->fecha_inicio_relacion->format('Y-m-d') : '—',
                'ingresos' => (bool) $c->ingresos_verificados,
                'pep' => (bool) $c->es_pep,
                'coincidencia' => $coincidencia,
                'previos' => $c->eventos_count,
            ]];
        })->all();
 
        $evaluacionId = $evaluacion->id;
        $evaluacionCodigo = $evaluacion->codigo;
        $siguienteEvento = $this->siguienteCodigo();
        $monedas = $this->monedas();
        $productos = $this->productos();
        $canales = $this->canales();
        $jurisdicciones = $this->jurisdicciones();
        $tiposRiesgo = ['LA', 'FT', 'LA/FT'];
 
        return view('eventos.create', compact(
            'evaluacionId', 'evaluacionCodigo', 'siguienteEvento', 'clientes', 'monedas',
            'productos', 'canales', 'jurisdicciones', 'tiposRiesgo'
        ));
    }
 
    public function store(Request $request)
    {
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'evaluacion' => 'required|integer|exists:evaluacion,id',
            'cliente' => 'required|string|exists:cliente,codigo',
            'tipo_riesgo' => 'required|in:LA,FT,LA/FT',
            'fecha' => 'nullable|date',
            'monto' => 'required|numeric|min:0|max:999999999999.99',
            'moneda' => ['required', Rule::in(array_keys($this->monedas()))],
            'producto' => ['nullable', Rule::in(array_keys($this->productos()))],
            'canal' => ['nullable', Rule::in(array_keys($this->canales()))],
            'jurisdiccion' => ['nullable', Rule::in(array_keys($this->jurisdicciones()))],
            'descripcion' => 'required|string',
            'frecuencia' => 'nullable|string',
        ]);
 
        $evaluacion = $this->evaluacionAbierta($request->evaluacion);
        if (!($evaluacion instanceof Evaluacion)) {
            return $evaluacion;
        }
 
        $cliente = Cliente::where('codigo', $request->cliente)->firstOrFail();
 
        try {
            $evento = DB::transaction(function () use ($request, $evaluacion, $cliente) {
                $nuevo = Evento::create([
                    'codigo' => $this->siguienteCodigo(),
                    'evaluacion_id' => $evaluacion->id,
                    'cliente_id' => $cliente->id,
                    'descripcion' => trim($request->descripcion),
                    'fecha_evento' => $request->fecha,
                    'monto' => $request->monto,
                    'moneda' => $request->moneda,
                    'frecuencia_descripcion' => $request->frecuencia,
                    'producto' => $request->producto ? $this->productos()[$request->producto] : null,
                    'canal' => $request->canal ? $this->canales()[$request->canal] : null,
                    'jurisdiccion' => $request->jurisdiccion ? $this->jurisdicciones()[$request->jurisdiccion] : null,
                    'tipo_riesgo' => $request->tipo_riesgo,
                    'creado_por' => auth()->id(),
                ]);
 
                $this->registrarBitacora('CREAR_EVENTO', $nuevo->id, null, [
                    'codigo' => $nuevo->codigo,
                    'evaluacion' => $evaluacion->codigo,
                    'cliente' => $cliente->codigo,
                    'tipo_riesgo' => $nuevo->tipo_riesgo,
                ], trim($request->descripcion));
 
                return $nuevo;
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();
 
            return back()->withInput()->with('error', 'No se pudo registrar el evento: ' . $mensaje);
        }
 
        // "Guardar y calificar": después de guardar se pasa a calificar el evento
        return redirect()->route('eventos.calificar', $evento->id)->with('success', 'Evento ' . $evento->codigo . ' registrado. Ahora califique sus factores.');
    }
 
    // Pantalla "Calificar evento": factores, agravantes y controles de la versión de la evaluación
    public function calificar($id)
    {
        $evento = Evento::with(['evaluacion', 'resultadoVigente'])->findOrFail($id);
        $versionId = $evento->evaluacion->version_id;
 
        $parametros = $this->parametrosDeLaVersion($versionId);
 
        // ---------- Factores ----------
        $calificaciones = Calificacion::where('evento_id', $evento->id)->get()->keyBy('factor_id');
 
        $factores = $parametros['factores']->map(function ($f) use ($parametros, $calificaciones) {
            $cal = $calificaciones->get($f->id);
 
            return [
                'clave' => strtolower($f->codigo),
                'nombre' => $f->nombre,
                'peso' => $this->numero($parametros['pesos'][$f->id]),
                'nivel' => $cal ? (int) $cal->nivel : null,
                'justificacion' => $cal && !$cal->dato_faltante ? $cal->justificacion : '',
                'falta' => $cal ? (bool) $cal->dato_faltante : false,
            ];
        })->values()->all();
 
        // ---------- Agravantes y controles ----------
        $marcadosAgravantes = DB::table('evento_agravante')->where('evento_id', $evento->id)->pluck('evidencia', 'agravante_id');
        $marcadosControles = DB::table('evento_control')->where('evento_id', $evento->id)->pluck('evidencia', 'control_id');
 
        $agravantes = $parametros['agravantes']->map(function ($a) use ($parametros, $marcadosAgravantes) {
            return [
                'clave' => $a->codigo,
                'texto' => $a->nombre,
                'valor' => $this->numero($parametros['puntosAgravante'][$a->id]),
                'marcado' => $marcadosAgravantes->has($a->id),
                'evidencia' => $marcadosAgravantes->get($a->id, ''),
            ];
        })->values()->all();
 
        $controles = $parametros['controles']->map(function ($c) use ($parametros, $marcadosControles) {
            return [
                'clave' => $c->codigo,
                'texto' => $c->nombre,
                'valor' => -1 * $this->numero($parametros['puntosControl'][$c->id]),
                'marcado' => $marcadosControles->has($c->id),
                'evidencia' => $marcadosControles->get($c->id, ''),
            ];
        })->values()->all();
 
        // Criterio del nivel más alto ya calificado (descripción de la escala de la versión)
        $criterio = ['nivel' => 0, 'factor' => '—', 'texto' => 'Elija un nivel para ver su criterio.'];
        $mayor = $calificaciones->filter(function ($cal) {
            return !$cal->dato_faltante;
        })->sortByDesc('nivel')->first();
        if ($mayor) {
            $factor = $parametros['factores']->firstWhere('id', $mayor->factor_id);
            $escala = FactorNivel::where('version_id', $versionId)->where('factor_id', $mayor->factor_id)->where('nivel', $mayor->nivel)->first();
            $criterio = [
                'nivel' => (int) $mayor->nivel,
                'factor' => $factor ? $factor->nombre : '—',
                'texto' => $escala ? $escala->descripcion : 'Sin criterio definido para este nivel.',
            ];
        }
 
        $evaluacionId = $evento->evaluacion->id;
        $evaluacionCodigo = $evento->evaluacion->codigo;
        $cerrada = $evento->evaluacion->estado === 'cerrada';
 
        // Banda del resultado vigente (null mientras el evento no se calcule)
        $evento = [
            'id' => $evento->codigo,
            'pk' => $evento->id,
            'banda' => $evento->resultadoVigente ? Str::slug($evento->resultadoVigente->banda) : null,
        ];
 
        return view('eventos.calificar', compact('evaluacionId', 'evaluacionCodigo', 'evento', 'factores', 'agravantes', 'controles', 'criterio', 'cerrada'));
    }
 
    // Guardar las calificaciones, los agravantes y los controles del evento (y calcular si se pide)
    public function guardarCalificaciones(Request $request, $id)
    {
        $evento = Evento::with('evaluacion')->findOrFail($id);
 
        if ($evento->evaluacion->estado === 'cerrada') {
            return redirect()->route('evaluaciones.show', $evento->evaluacion->id)->with('error', 'La evaluación está cerrada: solo lectura.');
        }
 
        $parametros = $this->parametrosDeLaVersion($evento->evaluacion->version_id);
 
        $agravantesPermitidos = $parametros['agravantes']->pluck('id', 'codigo');
        $controlesPermitidos = $parametros['controles']->pluck('id', 'codigo');
 
        $marcadosAgravantes = array_values(array_intersect((array) $request->input('agravantes', []), $agravantesPermitidos->keys()->all()));
        $marcadosControles = array_values(array_intersect((array) $request->input('controles', []), $controlesPermitidos->keys()->all()));
 
        // Reglas: cada factor necesita nivel y justificación (salvo los marcados "Falta dato"); cada agravante o control marcado, su evidencia
        $reglas = ['accion' => 'nullable|in:guardar,calcular'];
        $atributos = [];
 
        foreach ($parametros['factores'] as $f) {
            $k = strtolower($f->codigo);
            $atributos['factor_' . $k] = 'nivel de ' . $f->nombre;
            $atributos['justificacion_' . $k] = 'justificación de ' . $f->nombre;
 
            if (!$request->boolean('falta_' . $k)) {
                $reglas['factor_' . $k] = 'required|integer|between:0,5';
                $reglas['justificacion_' . $k] = 'required|string|min:3';
            }
        }
        foreach (array_merge($marcadosAgravantes, $marcadosControles) as $codigo) {
            $reglas['evidencia_' . $codigo] = 'required|string|min:3';
            $atributos['evidencia_' . $codigo] = 'evidencia de ' . $codigo;
        }
 
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate($reglas, [], $atributos);
 
        try {
            DB::transaction(function () use ($request, $evento, $parametros, $agravantesPermitidos, $controlesPermitidos, $marcadosAgravantes, $marcadosControles) {
                $usuario = auth()->id();
                $faltantes = [];
 
                // Factores
                foreach ($parametros['factores'] as $f) {
                    $k = strtolower($f->codigo);
 
                    if ($request->boolean('falta_' . $k)) {
                        // Dato faltante: no se guarda calificación; al calcular, el procedimiento asigna el puntaje de dato faltante
                        DB::table('calificacion')->where('evento_id', $evento->id)->where('factor_id', $f->id)->where('dato_faltante', false)->delete();
                        $faltantes[] = $f->codigo;
                        continue;
                    }
 
                    DB::table('calificacion')->updateOrInsert(
                        ['evento_id' => $evento->id, 'factor_id' => $f->id],
                        [
                            'nivel' => (int) $request->input('factor_' . $k),
                            'justificacion' => trim($request->input('justificacion_' . $k)),
                            'dato_faltante' => false,
                            'calificado_por' => $usuario,
                            'calificado_en' => now(),
                        ]
                    );
                }
 
                // Agravantes: los marcados quedan con su evidencia; los demás se quitan
                $idsAgravantes = $agravantesPermitidos->only($marcadosAgravantes)->values()->all();
                DB::table('evento_agravante')->where('evento_id', $evento->id)->whereNotIn('agravante_id', $idsAgravantes)->delete();
                foreach ($marcadosAgravantes as $codigo) {
                    DB::table('evento_agravante')->updateOrInsert(
                        ['evento_id' => $evento->id, 'agravante_id' => $agravantesPermitidos[$codigo]],
                        ['evidencia' => trim($request->input('evidencia_' . $codigo)), 'registrado_por' => $usuario, 'registrado_en' => now()]
                    );
                }
 
                // Controles: igual que los agravantes
                $idsControles = $controlesPermitidos->only($marcadosControles)->values()->all();
                DB::table('evento_control')->where('evento_id', $evento->id)->whereNotIn('control_id', $idsControles)->delete();
                foreach ($marcadosControles as $codigo) {
                    DB::table('evento_control')->updateOrInsert(
                        ['evento_id' => $evento->id, 'control_id' => $controlesPermitidos[$codigo]],
                        ['evidencia' => trim($request->input('evidencia_' . $codigo)), 'registrado_por' => $usuario, 'registrado_en' => now()]
                    );
                }
 
                $this->registrarBitacora('CALIFICAR_EVENTO', $evento->id, null, [
                    'evento' => $evento->codigo,
                    'factores_calificados' => $parametros['factores']->count() - count($faltantes),
                    'dato_faltante' => $faltantes,
                    'agravantes' => $marcadosAgravantes,
                    'controles' => $marcadosControles,
                ], 'Calificación registrada');
 
                // "Calcular resultado": el procedimiento calcula (y registra en la bitácora) con lo recién guardado
                if ($request->accion === 'calcular') {
                    DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [$evento->id, $usuario]);
                }
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();
 
            return back()->withInput()->with('error', 'No se pudo guardar: ' . $mensaje);
        }
 
        if ($request->accion === 'calcular') {
            $mensaje = 'Calificaciones guardadas y resultado de ' . $evento->codigo . ' calculado.';
 
            return Route::has('resultados.show')
                ? redirect()->route('resultados.show', $evento->codigo)->with('success', $mensaje)
                : redirect()->route('evaluaciones.show', $evento->evaluacion->id)->with('success', $mensaje);
        }
 
        // Redirigir a la evaluación con un mensaje de éxito (los resultados ya calculados no se recalculan solos)
        return redirect()->route('evaluaciones.show', $evento->evaluacion->id)
            ->with('success', 'Calificaciones de ' . $evento->codigo . ' guardadas. Use «Calcular pendientes» o «Calcular resultado» para actualizar el resultado.');
    }
 
    // Evaluación abierta a partir del id recibido; si no sirve, devuelve la redirección con el motivo
    private function evaluacionAbierta($id)
    {
        if (!$id) {
            return redirect()->route('evaluaciones.index')->with('error', 'Abra una evaluación y use «Registrar evento» desde ahí.');
        }
 
        $evaluacion = Evaluacion::findOrFail($id);
 
        if ($evaluacion->estado === 'cerrada') {
            return redirect()->route('evaluaciones.show', $evaluacion->id)->with('error', 'La evaluación está cerrada: no se pueden registrar eventos.');
        }
 
        return $evaluacion;
    }
 
    // Factores, agravantes y controles de una versión de parámetros, con sus pesos y puntos
    private function parametrosDeLaVersion($versionId)
    {
        $pesos = ParametroFactor::where('version_id', $versionId)->pluck('peso', 'factor_id');
        $puntosAgravante = ParametroAgravante::where('version_id', $versionId)->pluck('puntos', 'agravante_id');
        $puntosControl = ParametroControl::where('version_id', $versionId)->pluck('puntos_resta', 'control_id');
 
        return [
            'pesos' => $pesos,
            'puntosAgravante' => $puntosAgravante,
            'puntosControl' => $puntosControl,
            'factores' => Factor::whereIn('id', $pesos->keys())->orderBy('id')->get(),
            'agravantes' => Agravante::whereIn('id', $puntosAgravante->keys())->orderBy('id')->get(),
            'controles' => Control::whereIn('id', $puntosControl->keys())->orderBy('id')->get(),
        ];
    }
 
    // Siguiente código de evento: EV-005 → EV-006 (único en toda la BD)
    private function siguienteCodigo()
    {
        $mayor = Evento::pluck('codigo')->map(function ($c) {
            return (int) preg_replace('/\D/', '', $c);
        })->max() ?? 0;
 
        return sprintf('EV-%03d', $mayor + 1);
    }
 
    // Opciones de los formularios: clave que envía la vista → texto que se guarda en la BD
    private function monedas()
    {
        return ['USD' => 'USD · Dólar', 'EUR' => 'EUR · Euro', 'CRC' => 'CRC · Colones'];
    }
 
    private function productos()
    {
        return [
            'intl-transfers' => 'Transferencias internacionales',
            'cash-deposits' => 'Depósitos en efectivo concentrados',
            'trade-financing' => 'Cartas de crédito / Comercio exterior',
            'crypto-settlement' => 'Liquidación de criptoactivos',
        ];
    }
 
    private function canales()
    {
        return [
            'digital' => 'Vinculación completamente digital',
            'branch' => 'Atención presencial en agencia',
            'broker' => 'Intermediario externo / Broker',
        ];
    }
 
    private function jurisdicciones()
    {
        return [
            'high-risk' => 'Jurisdicción de mayor riesgo',
            'standard' => 'Jurisdicción estándar / Cooperante',
            'low-risk' => 'Jurisdicción local baja exposición',
        ];
    }
 
    // Quita los ceros sobrantes: 20.00 → 20, 2.50 → 2.5
    private function numero($valor)
    {
        $valor = (float) $valor;
 
        return $valor == floor($valor) ? (int) $valor : $valor;
    }
 
    // Registro en la bitácora con el procedimiento sp_registrar_bitacora
    private function registrarBitacora($accion, $eventoId, $anterior, $nuevo, $motivo)
    {
        DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?, ?, ?, @bitacora)', [
            auth()->id(),
            $accion,
            'evento',
            $eventoId,
            $anterior ? json_encode($anterior, JSON_UNESCAPED_UNICODE) : null,
            $nuevo ? json_encode($nuevo, JSON_UNESCAPED_UNICODE) : null,
            $motivo,
            null,
        ]);
    }
}
 