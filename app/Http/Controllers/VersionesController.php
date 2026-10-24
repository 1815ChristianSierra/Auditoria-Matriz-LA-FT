<?php

namespace App\Http\Controllers;

use App\Models\VersionParametros;
use App\Models\Factor;
use App\Models\ParametroFactor;
use App\Models\FactorNivel;
use App\Models\Banda;
use App\Models\Agravante;
use App\Models\ParametroAgravante;
use App\Models\Control;
use App\Models\ParametroControl;
use App\Models\ParametroRegla;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VersionesController extends Controller
{
    // Listado de versiones y comparación entre ellas
    public function index()
    {
        // Obtener todas las versiones con su proponente y su aprobador, la más reciente primero
        $registros = VersionParametros::with(['proponente', 'aprobador'])->orderByDesc('id')->get();

        // Pesos de cada versión: version_id → (factor_id → peso)
        $pesos = ParametroFactor::all()->groupBy('version_id')->map(function ($filas) {
            return $filas->pluck('peso', 'factor_id');
        });

        $factores = Factor::orderBy('id')->get();

        // Parámetros que se comparan: el peso de cada factor, los topes y el dato faltante
        $parametros = $factores->map(function ($f) {
            return ['clave' => 'peso_' . strtolower($f->codigo), 'label' => 'Peso ' . ucfirst($f->nombre)];
        })->all();
        $parametros[] = ['clave' => 'tope_agravantes', 'label' => 'Tope de agravantes'];
        $parametros[] = ['clave' => 'tope_controles', 'label' => 'Tope de controles'];
        $parametros[] = ['clave' => 'dato_faltante', 'label' => 'Puntaje dato faltante'];

        $versiones = $registros->map(function ($v) use ($factores, $pesos) {
            $delVersion = $pesos->get($v->id, collect());

            $valores = [];
            foreach ($factores as $f) {
                $valores['peso_' . strtolower($f->codigo)] = $this->numero($delVersion->get($f->id, 0));
            }
            $valores['tope_agravantes'] = $this->numero($v->tope_agravantes);
            $valores['tope_controles'] = $this->numero($v->tope_controles);
            $valores['dato_faltante'] = $this->numero($v->puntaje_dato_faltante);

            return [
                'id' => $v->id,
                'version' => $v->numero,
                'estado' => $v->estado,
                'proponente' => $v->proponente ? $v->proponente->username : '—',
                'aprobador' => $v->aprobador ? $v->aprobador->username : null,
                'vigencia' => $v->fecha_vigencia ? $v->fecha_vigencia->format('Y-m-d') : null,
                'motivo' => $v->motivo,
                'parametros' => $valores,
            ];
        })->all();

        // Comparación inicial: la versión aprobada contra la versión más reciente
        $aprobada = $registros->firstWhere('estado', 'aprobada');
        $primera = $registros->first();
        $baseSel = $aprobada ? $aprobada->numero : ($primera ? $primera->numero : null);
        $otra = $registros->first(function ($v) use ($baseSel) {
            return $v->numero !== $baseSel;
        });
        $compSel = $otra ? $otra->numero : $baseSel;

        // Usuario conectado, para el modal "Nueva versión"
        $proponente = optional(auth()->user())->username ?? 'admin1';

        return view('versiones.index', compact('versiones', 'parametros', 'baseSel', 'compSel', 'proponente'));
    }

    // Crear una versión nueva en borrador, copiando otra: lo hace el procedimiento sp_clonar_version
    public function store(Request $request)
    {
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'numero' => ['required', 'string', 'max:10', 'regex:/^v\d+\.\d+$/', 'unique:version_parametros,numero'],
            'clonar_desde' => VersionParametros::exists() ? 'required|string|exists:version_parametros,numero' : 'nullable',
            'motivo' => 'required|string',
        ]);

        if (! VersionParametros::exists()) {
            $request->validate([
                'umbrales' => 'required|array|size:3',
                'umbrales.0' => 'required|numeric|min:0.01|max:99.98',
                'umbrales.1' => 'required|numeric|gt:umbrales.0|max:99.99',
                'umbrales.2' => 'required|numeric|gt:umbrales.1|max:100',
            ]);
            $version = DB::transaction(function () use ($request) {
                $version = VersionParametros::create([
                    'numero'=>$request->numero, 'motivo'=>$request->motivo,
                    'proponente_id'=>auth()->id(), 'estado'=>'borrador',
                ]);
                $limites = array_merge([0], array_map(fn ($n) => round((float) $n, 2), $request->umbrales), [100.01]);
                foreach (['Bajo','Medio','Alto','Crítico'] as $i => $nombre) {
                    Banda::create(['version_id'=>$version->id, 'nombre'=>$nombre, 'orden'=>$i+1,
                        'desde'=>$limites[$i], 'hasta'=>round($limites[$i+1]-0.01, 2)]);
                }
                DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?, ?, ?, @bitacora_id)', [
                    auth()->id(), 'CREAR_VERSION', 'version_parametros', $version->id, null,
                    json_encode(['numero'=>$version->numero]), $request->motivo, null,
                ]);
                return $version;
            });
            return redirect()->route('versiones.edit', $version->id)
                ->with('success', 'Primer borrador creado. Configure pesos y escalas antes de proponerlo.');
        }
        $origen = VersionParametros::where('numero', $request->clonar_desde)->firstOrFail();

        try {
            // El procedimiento crea el borrador, copia pesos, escalas, bandas, agravantes, controles y reglas,
            // y deja el registro CREAR_VERSION en la bitácora
            DB::transaction(function () use ($request, $origen) {
                DB::statement('CALL sp_clonar_version(?, ?, ?, ?, @nueva)', [
                    $origen->id,
                    $request->numero,
                    $request->motivo,
                    auth()->id() ?? $origen->proponente_id,
                ]);
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return back()->withInput()->withErrors(['numero' => 'No se pudo crear la versión: ' . $mensaje]);
        }

        // Redirigir al listado con un mensaje de éxito
        return redirect()->route('versiones.index')->with('success', 'Versión ' . $request->numero . ' creada en borrador, copiada de ' . $origen->numero . '.');
    }

    // Pantalla "Editar versión": solo se puede editar una versión en borrador
    public function edit($id = null)
    {
        // Sin id se abre el borrador más reciente
        $registro = $id
            ? VersionParametros::findOrFail($id)
            : VersionParametros::where('estado', 'borrador')->orderByDesc('id')->first();

        if (!$registro) {
            return redirect()->route('versiones.index')->with('error', 'No hay una versión en borrador para editar.');
        }
        if (!$registro->esBorrador()) {
            return redirect()->route('versiones.index')->with('error', 'Solo se pueden editar versiones en borrador.');
        }

        return view('versiones.edit', $this->datosVersion($registro));
    }

    // Pantalla "Ver versión": la misma del editor, en solo lectura y para cualquier estado
    public function ver($id)
    {
        $registro = VersionParametros::with('aprobador')->findOrFail($id);

        $datos = $this->datosVersion($registro, true);

        // Quién decidió, cuándo y por qué (solo si la versión ya tuvo una decisión)
        $decision = null;
        if ($registro->aprobador_id) {
            $decision = [
                'quien' => $registro->aprobador ? $registro->aprobador->username : '—',
                'fecha' => $registro->decidido_en ? \Carbon\Carbon::parse($registro->decidido_en)->format('Y-m-d H:i') : '—',
                'vigencia' => $registro->fecha_vigencia ? \Carbon\Carbon::parse($registro->fecha_vigencia)->format('Y-m-d') : null,
                'motivo' => $registro->motivo_decision ?? '',
            ];
        }

        return view('versiones.edit', $datos + ['soloLectura' => true, 'decision' => $decision]);
    }

    // Guardar el borrador, o guardarlo y proponerlo para aprobación
    public function update(Request $request, $id)
    {
        $version = VersionParametros::findOrFail($id);

        if (!$version->esBorrador()) {
            return redirect()->route('versiones.index')->with('error', 'Solo se pueden editar versiones en borrador.');
        }

        // Factores y bandas que existen, para validar las reglas
        $factores = Factor::all()->keyBy(function ($f) {
            return strtolower($f->codigo);
        });
        $bandas = Banda::where('version_id', $version->id)->get()->keyBy(function ($b) {
            return Str::slug($b->nombre);
        });

        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'accion' => 'required|in:borrador,proponer',
            'motivo' => 'required|string',
            'pesos' => 'required|array',
            'pesos.*' => 'required|numeric|min:0|max:100',
            'escalas' => 'nullable|array',
            'escalas.*.*' => 'nullable|string|max:500',
            'topes.agravantes' => 'required|numeric|min:0|max:999',
            'topes.controles' => 'required|numeric|min:0|max:999',
            'topes.dato_faltante' => 'required|integer|between:0,5',
            'agravantes.*.puntos' => 'nullable|numeric|min:0|max:99',
            'controles.*.puntos' => 'nullable|numeric|min:0|max:99',
            'reglas.*.factor' => ['required', Rule::in($factores->keys()->all())],
            'reglas.*.nivel' => 'required|integer|between:0,5',
            'reglas.*.banda' => ['required', Rule::in($bandas->keys()->all())],
            'reglas.*.descripcion' => 'required|string',
        ]);

        // Para proponer, los pesos deben sumar exactamente 100
        $suma = round(array_sum($request->pesos), 2);
        if ($request->accion === 'proponer' && abs($suma - 100) > 0.001) {
            return back()->withInput()->withErrors(['pesos' => 'Los pesos deben sumar exactamente 100 % para proponer (suma actual: ' . $suma . ' %).']);
        }

        // Estado de la versión antes de los cambios, para dejar constancia en la bitácora
        $antes = $this->resumenVersion($version);

        DB::transaction(function () use ($request, $version, $factores, $bandas, $antes) {
            // Datos generales de la versión
            $version->motivo = $request->motivo;
            $version->tope_agravantes = $request->input('topes.agravantes');
            $version->tope_controles = $request->input('topes.controles');
            $version->puntaje_dato_faltante = $request->input('topes.dato_faltante');
            $version->save();

            // Pesos y escalas de cada factor
            foreach ($request->pesos as $clave => $peso) {
                if (!isset($factores[$clave])) {
                    continue;
                }
                $factor = $factores[$clave];

                ParametroFactor::updateOrCreate(
                    ['version_id' => $version->id, 'factor_id' => $factor->id],
                    ['peso' => $peso]
                );

                // Un nivel sin texto queda "por definir" (no se guarda)
                for ($n = 0; $n <= 5; $n++) {
                    $texto = trim((string) $request->input('escalas.' . $clave . '.' . $n));
                    if ($texto !== '') {
                        FactorNivel::updateOrCreate(
                            ['version_id' => $version->id, 'factor_id' => $factor->id, 'nivel' => $n],
                            ['descripcion' => $texto]
                        );
                    } else {
                        FactorNivel::where('version_id', $version->id)->where('factor_id', $factor->id)->where('nivel', $n)->delete();
                    }
                }
            }

            // Agravantes: los marcados "incluir" quedan en la versión con sus puntos
            foreach (Agravante::all() as $agravante) {
                if ($request->has('agravantes.' . $agravante->codigo . '.incluir')) {
                    ParametroAgravante::updateOrCreate(
                        ['version_id' => $version->id, 'agravante_id' => $agravante->id],
                        ['puntos' => $request->input('agravantes.' . $agravante->codigo . '.puntos') ?? 0]
                    );
                } else {
                    ParametroAgravante::where('version_id', $version->id)->where('agravante_id', $agravante->id)->delete();
                }
            }

            // Controles: igual que los agravantes, pero restan puntos
            foreach (Control::all() as $control) {
                if ($request->has('controles.' . $control->codigo . '.incluir')) {
                    ParametroControl::updateOrCreate(
                        ['version_id' => $version->id, 'control_id' => $control->id],
                        ['puntos_resta' => $request->input('controles.' . $control->codigo . '.puntos') ?? 0]
                    );
                } else {
                    ParametroControl::where('version_id', $version->id)->where('control_id', $control->id)->delete();
                }
            }

            // Reglas: se guardan las que llegan y se borran las que ya no están
            $reglas = $request->input('reglas', []);
            ParametroRegla::where('version_id', $version->id)->whereNotIn('codigo', array_keys($reglas))->delete();

            foreach ($reglas as $codigo => $regla) {
                ParametroRegla::updateOrCreate(
                    ['version_id' => $version->id, 'codigo' => $codigo],
                    [
                        'descripcion' => $regla['descripcion'],
                        'factor_id' => $factores[$regla['factor']]->id,
                        'nivel_minimo' => $regla['nivel'],
                        'banda_forzada' => $bandas[$regla['banda']]->nombre,
                    ]
                );
            }

            // Si cambió algún parámetro, se registra con el antes y el después
            $despues = $this->resumenVersion($version);
            if ($antes != $despues) {
                DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, CAST(? AS JSON), CAST(? AS JSON), ?, ?, @bitacora_id)', [
                    auth()->id(), 'GUARDAR_BORRADOR', 'version_parametros', $version->id,
                    json_encode($antes),
                    json_encode($despues),
                    $request->motivo, null,
                ]);
            }

            // Al proponer, el cambio de estado va al FINAL: la BD bloquea los parámetros de una versión
            // que ya no está en borrador y comprueba que los pesos guardados sumen 100
            if ($request->accion === 'proponer') {
                // Quien propone queda como proponente: la aprobación la hará otra persona
                $version->estado = 'propuesta';
                $version->proponente_id = auth()->id() ?? $version->proponente_id;
                $version->save();

                // Queda registrado en la bitácora (la aprobación o el rechazo los registra la BD con un trigger)
                DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, CAST(? AS JSON), CAST(? AS JSON), ?, ?, @bitacora_id)', [
                    auth()->id(), 'PROPONER_VERSION', 'version_parametros', $version->id,
                    json_encode(['estado' => 'borrador']),
                    json_encode(['estado' => 'propuesta', 'numero' => $version->numero]),
                    $request->motivo, null,
                ]);
            }
        });

        if ($request->accion === 'proponer') {
            return redirect()->route('versiones.index')->with('success', 'Versión ' . $version->numero . ' propuesta para aprobación.');
        }

        return redirect()->route('versiones.edit', $version->id)->with('success', 'Borrador guardado correctamente.');
    }

    // Pantalla "Revisar versión": solo se revisan versiones propuestas
    public function revisar($id = null)
    {
        // Sin id se abre la propuesta más reciente
        $registro = $id
            ? VersionParametros::with('proponente.rol')->findOrFail($id)
            : VersionParametros::with('proponente.rol')->where('estado', 'propuesta')->orderByDesc('id')->first();

        if (!$registro) {
            return redirect()->route('versiones.index')->with('error', 'No hay una versión propuesta para revisar.');
        }
        if ($registro->estado !== 'propuesta') {
            return redirect()->route('versiones.index')->with('error', 'Solo se pueden revisar versiones propuestas.');
        }

        // Versión contra la que se compara: la última aprobada; si no hay, la anterior; si no, ella misma
        $base = VersionParametros::where('estado', 'aprobada')->where('id', '<>', $registro->id)->orderByDesc('id')->first()
            ?? VersionParametros::where('id', '<', $registro->id)->orderByDesc('id')->first()
            ?? $registro;

        $version = [
            'id' => $registro->id,
            'codigo' => $registro->numero,
            'base' => $base->numero,
            'estado' => ucfirst($registro->estado),
            'folio' => 'REV-' . str_pad($registro->id, 4, '0', STR_PAD_LEFT),
            'proponente' => $registro->proponente ? $registro->proponente->username : '—',
            'rol_proponente' => ucfirst(optional(optional($registro->proponente)->rol)->nombre ?? ''),
            'fecha' => $registro->creado_en ? $registro->creado_en->format('Y-m-d H:i') : '—',
            'motivo' => $registro->motivo,
        ];

        // ---------- Cambios frente a la versión base: [parámetro, antes, después] ----------
        $pesosBase = ParametroFactor::where('version_id', $base->id)->pluck('peso', 'factor_id');
        $pesosNueva = ParametroFactor::where('version_id', $registro->id)->pluck('peso', 'factor_id');

        $factores = Factor::whereIn('id', $pesosBase->keys()->merge($pesosNueva->keys())->unique()->values())->orderBy('id')->get();

        $parametros = [];
        foreach ($factores as $f) {
            $parametros[] = [
                'Peso · ' . ucfirst($f->nombre),
                $this->numero($pesosBase->get($f->id, 0)),
                $this->numero($pesosNueva->get($f->id, 0)),
            ];
        }
        $parametros[] = ['Tope agravantes', $this->numero($base->tope_agravantes), $this->numero($registro->tope_agravantes)];
        $parametros[] = ['Tope controles', $this->numero($base->tope_controles), $this->numero($registro->tope_controles)];
        $parametros[] = ['Nivel por dato faltante', $this->numero($base->puntaje_dato_faltante), $this->numero($registro->puntaje_dato_faltante)];

        // La simulación sobre escenarios sintéticos todavía no está conectada
        $escenarios = [];

        // Quien propuso la versión no puede decidir sobre ella
        $puedeDecidir = auth()->check() && (int) auth()->id() !== (int) $registro->proponente_id;

        return view('versiones.revisar', compact('version', 'parametros', 'escenarios', 'puedeDecidir'));
    }

    // Aprobar o rechazar una versión propuesta
    public function decidir(Request $request, $id)
    {
        $version = VersionParametros::findOrFail($id);

        if ($version->estado !== 'propuesta') {
            return redirect()->route('versiones.index')->with('error', 'Solo se pueden decidir versiones propuestas.');
        }

        // Separación de funciones: quien propone no decide
        if ((int) auth()->id() === (int) $version->proponente_id) {
            return redirect()->route('versiones.revisar', $version->id)->with('error', 'Separación de funciones: quien propuso la versión no puede decidir sobre ella.');
        }

        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'resolucion' => 'required|in:aprobar,rechazar',
            'motivo_decision' => 'required|string|min:10',
        ]);

        $aprueba = $request->resolucion === 'aprobar';

        try {
            $version->estado = $aprueba ? 'aprobada' : 'rechazada';
            $version->aprobador_id = auth()->id();
            $version->motivo_decision = trim($request->motivo_decision);
            $version->save();
        } catch (QueryException $e) {
            // La BD también valida (pesos que suman 100, separación de funciones, motivo de la decisión)
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return back()->withInput()->with('error', 'No se pudo registrar la decisión: ' . $mensaje);
        }

        return redirect()->route('versiones.index')->with('success', 'Versión ' . $version->numero . ($aprueba ? ' aprobada.' : ' rechazada.'));
    }

    // Datos de una versión para las pantallas de editar y ver.
    // $soloDeLaVersion: en modo lectura solo se muestra lo que pertenece a esa versión
    private function datosVersion($registro, $soloDeLaVersion = false)
    {
        $version = [
            'id' => $registro->id,
            'codigo' => $registro->numero,
            'estado' => ucfirst($registro->estado),
            'creada' => $registro->creado_en ? $registro->creado_en->format('d/m/Y') : '—',
            'motivo' => $registro->motivo,
        ];

        // ---------- Pesos y escalas ----------
        $pesos = ParametroFactor::where('version_id', $registro->id)->pluck('peso', 'factor_id');

        // Factores activos del catálogo, más los que ya tienen peso en esta versión
        $catalogo = $soloDeLaVersion
            ? Factor::whereIn('id', $pesos->keys())->orderBy('id')->get()
            : Factor::where('activo', true)->orWhereIn('id', $pesos->keys())->orderBy('id')->get();

        $factores = $catalogo->map(function ($f) use ($pesos) {
            return [
                'clave' => strtolower($f->codigo),
                'nombre' => $f->nombre,
                'peso' => $this->numero($pesos[$f->id] ?? 0),
            ];
        })->all();

        // factor_id → clave, para las escalas y las reglas
        $claves = Factor::all()->mapWithKeys(function ($f) {
            return [$f->id => strtolower($f->codigo)];
        });

        $escalas = [];
        foreach (FactorNivel::where('version_id', $registro->id)->get() as $nivel) {
            $escalas[$claves[$nivel->factor_id]][$nivel->nivel] = $nivel->descripcion;
        }

        // ---------- Bandas y topes ----------
        $bandas = Banda::where('version_id', $registro->id)->orderBy('orden')->get()->map(function ($b) {
            return [
                'clave' => Str::slug($b->nombre),
                'nombre' => $b->nombre,
                'desde' => $this->numero($b->desde),
                'hasta' => $this->numero($b->hasta),
                'tratamiento' => $b->tratamiento ?? '',
            ];
        })->all();

        $topeAgravantes = $this->numero($registro->tope_agravantes);
        $topeControles = $this->numero($registro->tope_controles);
        $datoFaltante = $registro->puntaje_dato_faltante;

        // ---------- Agravantes ----------
        $puntosAgravante = ParametroAgravante::where('version_id', $registro->id)->pluck('puntos', 'agravante_id');

        $agravantes = ($soloDeLaVersion
            ? Agravante::whereIn('id', $puntosAgravante->keys())
            : Agravante::where('activo', true)->orWhereIn('id', $puntosAgravante->keys())
        )->orderBy('id')->get()->map(function ($a) use ($puntosAgravante) {
            return [
                'codigo' => $a->codigo,
                'nombre' => $a->nombre,
                'descripcion' => $a->descripcion ?? '',
                'incluir' => $puntosAgravante->has($a->id),
                'puntos' => $this->numero($puntosAgravante[$a->id] ?? 0),
            ];
        })->all();

        // ---------- Controles ----------
        $puntosControl = ParametroControl::where('version_id', $registro->id)->pluck('puntos_resta', 'control_id');

        $controlesVersion = ($soloDeLaVersion
            ? Control::whereIn('id', $puntosControl->keys())
            : Control::where('activo', true)->orWhereIn('id', $puntosControl->keys())
        )->orderBy('id')->get()->map(function ($c) use ($puntosControl) {
            return [
                'codigo' => $c->codigo,
                'nombre' => $c->nombre,
                'tipo' => ucfirst($c->tipo ?? ''),
                'diseno' => ucfirst($c->calif_diseno ?? ''),
                'operacion' => ucfirst($c->calif_operacion ?? ''),
                'incluir' => $puntosControl->has($c->id),
                'puntos' => $this->numero($puntosControl[$c->id] ?? 0),
            ];
        })->all();

        // ---------- Reglas de escalamiento ----------
        $reglas = ParametroRegla::where('version_id', $registro->id)->orderBy('codigo')->get()->map(function ($r) use ($claves) {
            return [
                'codigo' => $r->codigo,
                'factor' => $claves[$r->factor_id] ?? '',
                'nivel' => (int) $r->nivel_minimo,
                'banda' => Str::slug($r->banda_forzada),
                'descripcion' => $r->descripcion,
            ];
        })->all();

        return compact(
            'version', 'factores', 'escalas', 'bandas', 'topeAgravantes', 'topeControles',
            'datoFaltante', 'agravantes', 'controlesVersion', 'reglas'
        );
    }

    // Resumen de lo que se puede cambiar en un borrador, para compararlo antes y después en la bitácora
    private function resumenVersion($version)
    {
        $factores = Factor::pluck('codigo', 'id');
        $agravantes = Agravante::pluck('codigo', 'id');
        $controles = Control::pluck('codigo', 'id');

        $pesos = [];
        foreach (ParametroFactor::where('version_id', $version->id)->get() as $fila) {
            $pesos[strtolower($factores[$fila->factor_id])] = $this->numero($fila->peso);
        }

        $puntosAgravantes = [];
        foreach (ParametroAgravante::where('version_id', $version->id)->get() as $fila) {
            $puntosAgravantes[$agravantes[$fila->agravante_id]] = $this->numero($fila->puntos);
        }

        $puntosControles = [];
        foreach (ParametroControl::where('version_id', $version->id)->get() as $fila) {
            $puntosControles[$controles[$fila->control_id]] = $this->numero($fila->puntos_resta);
        }

        $reglas = [];
        foreach (ParametroRegla::where('version_id', $version->id)->orderBy('codigo')->get() as $fila) {
            $reglas[$fila->codigo] = ($factores[$fila->factor_id] ?? '?') . ' nivel ' . $fila->nivel_minimo . ' → ' . $fila->banda_forzada;
        }

        ksort($pesos);
        ksort($puntosAgravantes);
        ksort($puntosControles);

        return [
            'pesos' => $pesos,
            'tope_agravantes' => $this->numero($version->tope_agravantes),
            'tope_controles' => $this->numero($version->tope_controles),
            'dato_faltante' => $version->puntaje_dato_faltante === null ? null : (int) $version->puntaje_dato_faltante,
            'agravantes' => $puntosAgravantes,
            'controles' => $puntosControles,
            'reglas' => $reglas,
        ];
    }

    // Quita los ceros sobrantes: 20.00 → 20, 12.50 → 12.5
    private function numero($valor)
    {
        $valor = (float) $valor;

        return $valor == floor($valor) ? (int) $valor : $valor;
    }
}
 