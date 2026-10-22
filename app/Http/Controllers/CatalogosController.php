<?php

namespace App\Http\Controllers;

use App\Models\Factor;
use App\Models\Agravante;
use App\Models\Control;
use App\Models\User;
use App\Models\VersionParametros;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogosController extends Controller
{
    public function index()
    {
        // Factores, agravantes y controles con la forma que espera la vista (codigo, nombre, activo...)
        $factores = Factor::orderBy('id')->get()->map(function ($f) {
            return [
                'codigo' => $f->codigo,
                'nombre' => $f->nombre,
                'descripcion' => $f->descripcion ?? '',
                'activo' => (bool) $f->activo,
            ];
        });

        $agravantes = Agravante::orderBy('id')->get()->map(function ($a) {
            return [
                'codigo' => $a->codigo,
                'nombre' => $a->nombre,
                'descripcion' => $a->descripcion ?? '',
                'activo' => (bool) $a->activo,
            ];
        });

        // Obtener los controles con su responsable
        $controles = Control::with('responsable')->orderBy('id')->get()->map(function ($c) {
            return [
                'codigo' => $c->codigo,
                'nombre' => $c->nombre,
                'tipo' => ucfirst($c->tipo ?? ''),
                'frecuencia' => $c->frecuencia ?? '',
                'responsable' => $c->responsable ? $c->responsable->username : '—',
                'diseno' => ucfirst($c->calif_diseno ?? ''),
                'operacion' => ucfirst($c->calif_operacion ?? ''),
                'activo' => (bool) $c->activo,
            ];
        });

        // Usuarios activos para el select "Responsable" del formulario de controles
        $responsables = User::where('estado', 'activo')->orderBy('username')->pluck('username');

        // Pestaña que se abre: la del formulario que se acaba de enviar, o "factores" por defecto
        $tabActiva = session('tab', 'factores');

        // Datos del botón "Crear versión": de cuál se parte y cuántos elementos activos pasarán
        $base = $this->versionBase();
        $resumenVersion = [
            'siguiente' => $this->siguienteNumeroVersion(),
            'base' => $base ? $base->numero . ' · ' . $base->estado : null,
            'factores' => Factor::where('activo', true)->count(),
            'agravantes' => Agravante::where('activo', true)->count(),
            'controles' => Control::where('activo', true)->count(),
        ];

        return view('catalogos.index', compact('factores', 'agravantes', 'controles', 'responsables', 'tabActiva', 'resumenVersion'));
    }

    // Guardar un control nuevo (pestaña Controles)
    public function store(Request $request)
    {
        // Para volver a la misma pestaña, tanto si guarda como si falla la validación
        session()->flash('tab', 'controles');

        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'nombre' => 'required|string|max:120',
            'tipo' => 'required|in:Preventivo,Detectivo,Correctivo',
            'frecuencia' => 'required|in:Mensual,Por evento,Trimestral,Anual',
            'responsable' => 'nullable|string|exists:users,username',
            'calificacion_diseno' => 'required|in:Adecuado,Mejorable,Deficiente',
            'calificacion_operacion' => 'required|in:Adecuado,Mejorable,Deficiente',
            'descripcion' => 'nullable|string',
        ]);

        // La vista manda el username del responsable, aquí se busca su id
        $responsable = User::where('username', $request->responsable)->first();

        // El campo "evidencia" del formulario no se guarda: la tabla control no tiene esa columna
        DB::transaction(function () use ($request, $responsable) {
            $control = Control::create([
                'codigo' => $this->siguienteCodigo(Control::class, 'CTL'),
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'tipo' => strtolower($request->tipo),
                'frecuencia' => $request->frecuencia,
                'responsable_id' => $responsable ? $responsable->id : null,
                'calif_diseno' => strtolower($request->calificacion_diseno),
                'calif_operacion' => strtolower($request->calificacion_operacion),
                'activo' => true,
            ]);

            // Queda registrado en la bitácora
            DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, CAST(? AS JSON), CAST(? AS JSON), ?, ?, @bitacora_id)', [
                auth()->id(), 'CREAR_CONTROL', 'control', $control->id,
                null,
                json_encode([
                    'codigo' => $control->codigo,
                    'nombre' => $control->nombre,
                    'tipo' => $control->tipo,
                    'frecuencia' => $control->frecuencia,
                    'responsable' => $responsable ? $responsable->username : null,
                ]),
                null, null,
            ]);
        });

        // Redirigir al catálogo con un mensaje de éxito
        return redirect()->route('catalogos.index')->with('success', 'Control Creado Correctamente');
    }

    // Guardar un factor nuevo (pestaña Factores)
    public function storeFactor(Request $request)
    {
        session()->flash('tab', 'factores');

        // El código se guarda siempre en mayúsculas
        $request->merge(['codigo' => strtoupper(trim((string) $request->codigo))]);

        $request->validate([
            'codigo' => 'required|string|max:20|regex:/^[A-Z0-9_]+$/|unique:factor,codigo',
            'nombre' => 'required|string|max:60',
            'descripcion' => 'nullable|string',
        ]);

        // El peso y los niveles del factor se definen por versión, no aquí
        DB::transaction(function () use ($request) {
            $factor = Factor::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'activo' => true,
            ]);

            DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, CAST(? AS JSON), CAST(? AS JSON), ?, ?, @bitacora_id)', [
                auth()->id(), 'CREAR_FACTOR', 'factor', $factor->id,
                null,
                json_encode(['codigo' => $factor->codigo, 'nombre' => $factor->nombre]),
                null, null,
            ]);
        });

        return redirect()->route('catalogos.index')->with('success', 'Factor Creado Correctamente');
    }

    // Guardar un agravante nuevo (pestaña Agravantes)
    public function storeAgravante(Request $request)
    {
        session()->flash('tab', 'agravantes');

        $request->validate([
            'nombre' => 'required|string|max:120',
            'descripcion' => 'nullable|string',
        ]);

        // El código del formulario es solo informativo: se calcula de nuevo aquí para que no se repita
        DB::transaction(function () use ($request) {
            $agravante = Agravante::create([
                'codigo' => $this->siguienteCodigo(Agravante::class, 'AGR'),
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'activo' => true,
            ]);

            DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, CAST(? AS JSON), CAST(? AS JSON), ?, ?, @bitacora_id)', [
                auth()->id(), 'CREAR_AGRAVANTE', 'agravante', $agravante->id,
                null,
                json_encode(['codigo' => $agravante->codigo, 'nombre' => $agravante->nombre]),
                null, null,
            ]);
        });

        return redirect()->route('catalogos.index')->with('success', 'Agravante Creado Correctamente');
    }

    // Activar o desactivar un factor, agravante o control (los interruptores de las tablas)
    public function activo(Request $request, $tipo, $codigo)
    {
        $modelos = [
            'factores' => Factor::class,
            'agravantes' => Agravante::class,
            'controles' => Control::class,
        ];
        abort_unless(isset($modelos[$tipo]), 404);

        // Tipo de entidad y sufijo de la acción en la bitácora
        $entidades = ['factores' => ['factor', 'FACTOR'], 'agravantes' => ['agravante', 'AGRAVANTE'], 'controles' => ['control', 'CONTROL']];

        $registro = $modelos[$tipo]::where('codigo', $codigo)->firstOrFail();
        $anterior = (bool) $registro->activo;
        $nuevo = $request->boolean('activo');

        DB::transaction(function () use ($registro, $anterior, $nuevo, $tipo, $entidades) {
            $registro->update(['activo' => $nuevo]);

            // Solo se registra si el estado realmente cambió
            if ($anterior !== $nuevo) {
                DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, CAST(? AS JSON), CAST(? AS JSON), ?, ?, @bitacora_id)', [
                    auth()->id(),
                    ($nuevo ? 'ACTIVAR_' : 'DESACTIVAR_') . $entidades[$tipo][1],
                    $entidades[$tipo][0],
                    $registro->id,
                    json_encode(['codigo' => $registro->codigo, 'activo' => $anterior]),
                    json_encode(['codigo' => $registro->codigo, 'activo' => $nuevo]),
                    null, null,
                ]);
            }
        });

        return response()->json(['codigo' => $registro->codigo, 'activo' => (bool) $registro->activo]);
    }

    // Botón "Crear versión": crea una versión nueva en borrador con lo que está activo en el catálogo
    public function crearVersion(Request $request)
    {
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'motivo' => 'required|string|min:10',
        ]);

        $base = $this->versionBase();
        if (!$base) {
            return back()->with('error', 'No hay ninguna versión de la cual partir: cree la primera desde el listado de versiones.');
        }

        $numero = $this->siguienteNumeroVersion();

        try {
            $cambios = DB::transaction(function () use ($request, $base, $numero) {
                // El procedimiento crea el borrador copiando la versión base y registra CREAR_VERSION en la bitácora
                DB::statement('CALL sp_clonar_version(?, ?, ?, ?, @nueva)', [
                    $base->id,
                    $numero,
                    trim($request->motivo),
                    auth()->id() ?? $base->proponente_id,
                ]);

                // Después se deja la copia igual que el catálogo: solo lo activo
                return $this->ajustarAlCatalogo(VersionParametros::where('numero', $numero)->value('id'));
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return back()->withInput()->with('error', 'No se pudo crear la versión: ' . $mensaje);
        }

        $mensaje = 'Versión ' . $numero . ' creada en borrador desde el catálogo (base ' . $base->numero . '): '
            . $cambios['factores'] . ' factores, ' . $cambios['agravantes'] . ' agravantes y ' . $cambios['controles'] . ' controles activos.';
        if ($cambios['nuevos']) {
            $mensaje .= ' ' . $cambios['nuevos'] . ' elementos nuevos entran con 0: asígneles su peso o sus puntos en «Editar versión».';
        }

        // Redirigir a la edición de la versión nueva con un mensaje de éxito
        return redirect()->route('versiones.edit', VersionParametros::where('numero', $numero)->value('id'))->with('success', $mensaje);
    }

    // Deja la versión con los factores, agravantes y controles que están activos en el catálogo:
    // lo apagado se quita y lo activo que no estaba entra con 0 (peso o puntos)
    private function ajustarAlCatalogo($versionId)
    {
        $nuevos = 0;

        // Factores: antes de quitar uno se borran sus reglas y su escala en esta versión
        $activos = Factor::where('activo', true)->pluck('id');
        $quitados = DB::table('parametro_factor')->where('version_id', $versionId)->whereNotIn('factor_id', $activos)->pluck('factor_id');
        if ($quitados->isNotEmpty()) {
            DB::table('parametro_regla')->where('version_id', $versionId)->whereIn('factor_id', $quitados)->delete();
            DB::table('factor_nivel')->where('version_id', $versionId)->whereIn('factor_id', $quitados)->delete();
            DB::table('parametro_factor')->where('version_id', $versionId)->whereIn('factor_id', $quitados)->delete();
        }
        $faltan = $activos->diff(DB::table('parametro_factor')->where('version_id', $versionId)->pluck('factor_id'));
        foreach ($faltan as $factorId) {
            DB::table('parametro_factor')->insert(['version_id' => $versionId, 'factor_id' => $factorId, 'peso' => 0]);
            $nuevos++;
        }

        // Agravantes
        $activos = Agravante::where('activo', true)->pluck('id');
        DB::table('parametro_agravante')->where('version_id', $versionId)->whereNotIn('agravante_id', $activos)->delete();
        $faltan = $activos->diff(DB::table('parametro_agravante')->where('version_id', $versionId)->pluck('agravante_id'));
        foreach ($faltan as $agravanteId) {
            DB::table('parametro_agravante')->insert(['version_id' => $versionId, 'agravante_id' => $agravanteId, 'puntos' => 0]);
            $nuevos++;
        }

        // Controles
        $activos = Control::where('activo', true)->pluck('id');
        DB::table('parametro_control')->where('version_id', $versionId)->whereNotIn('control_id', $activos)->delete();
        $faltan = $activos->diff(DB::table('parametro_control')->where('version_id', $versionId)->pluck('control_id'));
        foreach ($faltan as $controlId) {
            DB::table('parametro_control')->insert(['version_id' => $versionId, 'control_id' => $controlId, 'puntos_resta' => 0]);
            $nuevos++;
        }

        return [
            'factores' => DB::table('parametro_factor')->where('version_id', $versionId)->count(),
            'agravantes' => DB::table('parametro_agravante')->where('version_id', $versionId)->count(),
            'controles' => DB::table('parametro_control')->where('version_id', $versionId)->count(),
            'nuevos' => $nuevos,
        ];
    }

    // Versión de la que se parte: la última aprobada; si no hay ninguna, la más reciente
    private function versionBase()
    {
        return VersionParametros::where('estado', 'aprobada')->orderByDesc('id')->first()
            ?? VersionParametros::orderByDesc('id')->first();
    }

    // Siguiente número de versión: la mayor + 1 en el segundo dígito (v1.5 → v1.6)
    private function siguienteNumeroVersion()
    {
        $mayor = [0, 0];
        $numeros = VersionParametros::pluck('numero');

        foreach ($numeros as $numero) {
            if (preg_match('/^v(\d+)\.(\d+)$/', $numero, $m) && [(int) $m[1], (int) $m[2]] > $mayor) {
                $mayor = [(int) $m[1], (int) $m[2]];
            }
        }

        return $numeros->isEmpty() ? 'v1.0' : 'v' . $mayor[0] . '.' . ($mayor[1] + 1);
    }

    // Siguiente código: el número mayor + 1 (CTL-02 → CTL-03)
    private function siguienteCodigo($modelo, $prefijo)
    {
        $mayor = $modelo::pluck('codigo')->map(function ($c) {
            return (int) preg_replace('/\D/', '', $c);
        })->max() ?? 0;

        return sprintf('%s-%02d', $prefijo, $mayor + 1);
    }
}
 