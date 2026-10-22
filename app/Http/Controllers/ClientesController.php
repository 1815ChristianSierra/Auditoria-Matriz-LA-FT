<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CoincidenciaLista;
use App\Models\ListaRestrictiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        // Obtener los clientes con sus coincidencias en listas y sus eventos (con evaluación y resultado vigente)
        $registros = Cliente::with(['coincidencias.lista', 'eventos.evaluacion', 'eventos.resultadoVigente'])->orderBy('codigo')->get();
        //dd($registros);

        // Clientes con la forma que espera la vista, por código
        $clientes = $registros->mapWithKeys(function ($c) {
            return [$c->codigo => $this->datosCliente($c)];
        })->all();

        // Cliente que se abre en la ficha: el que se acaba de crear o editar, el indicado en la URL (?cliente=CLI-004) o el primero
        $seleccionado = session('seleccionado');
        if (!$seleccionado && $request->filled('cliente') && isset($clientes[$request->cliente])) {
            $seleccionado = $request->cliente;
        }
        $seleccionado = $seleccionado ?? array_key_first($clientes);

        return view('clientes.index', compact('clientes', 'seleccionado'));
    }

    public function create()
    {
        $siguienteCodigo = $this->siguienteCodigo();
        $jurisdicciones = $this->jurisdicciones();

        // Datos del panel "Verificación en listas": las listas sintéticas activas contra las que se cruza el nombre
        $activas = ListaRestrictiva::where('activo', true)->orderBy('id')->get();

        $motor = [
            'nombre' => 'Motor OFAC / ONU',
            'version' => 'Listas sintéticas (' . $activas->count() . ')',
            'revisadas' => 0,
            'total' => $activas->count(),
        ];
        $listas = $activas->map(function ($l) {
            return ['nombre' => $l->nombre_ficticio . ' · ' . $l->tipo_lista, 'estado' => 'Pendiente'];
        })->all();

        // Referencia del alta y quién la registra
        $referencia = 'REG-' . date('Y') . '-' . substr($siguienteCodigo, 4);
        $auditoria = 'OP_' . strtoupper(optional(auth()->user())->username ?? 'SISTEMA');

        return view('clientes.create', compact('siguienteCodigo', 'jurisdicciones', 'motor', 'listas', 'referencia', 'auditoria'));
    }

    public function store(Request $request)
    {
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate($this->reglas());

        $esPep = $request->boolean('es_pep');

        $cliente = DB::transaction(function () use ($request, $esPep) {
            // La columna "sintetico" no se envía: la BD la deja en 1 y rechaza cualquier otro valor
            $nuevo = Cliente::create([
                'codigo' => $this->siguienteCodigo(),
                'nombre_ficticio' => trim($request->nombre),
                'tipo_persona' => $request->tipo_persona,
                'actividad' => $request->actividad,
                'fecha_inicio_relacion' => $request->inicio_relacion,
                'ingresos_verificados' => $request->boolean('ingresos_verificados'),
                'jurisdiccion_residencia' => $this->jurisdicciones()[$request->jurisdiccion],
                'es_pep' => $esPep,
                'vinculo_pep' => $esPep ? $request->vinculo_pep : null,
            ]);

            // Cruce del nombre contra las listas sintéticas activas: si coincide queda como "parcial"
            // hasta que una persona la confirme o la descarte (la BD pide un usuario en verificada_por)
            $listas = ListaRestrictiva::where('activo', true)->where('nombre_ficticio', $nuevo->nombre_ficticio)->get();
            foreach ($listas as $lista) {
                CoincidenciaLista::create([
                    'cliente_id' => $nuevo->id,
                    'lista_id' => $lista->id,
                    'tipo_coincidencia' => 'parcial',
                    'verificada_por' => auth()->id(),
                    'observacion' => 'Coincidencia detectada al registrar el cliente (pendiente de verificación)',
                ]);
            }

            return $nuevo;
        });

        $coincidencias = $cliente->coincidencias()->count();
        $mensaje = 'Cliente ' . $cliente->codigo . ' creado correctamente.';
        if ($coincidencias) {
            $mensaje .= ' Se detectó ' . $coincidencias . ($coincidencias === 1 ? ' coincidencia' : ' coincidencias') . ' en listas (quedan como «Parcial» hasta que una persona las confirme).';
        }

        // Redirigir a la lista de clientes con un mensaje de éxito, con la ficha del cliente nuevo abierta
        return redirect()->route('clientes.index')->with('success', $mensaje)->with('seleccionado', $cliente->codigo);
    }

    public function edit($id)
    {
        $registro = Cliente::with(['coincidencias.lista', 'eventos.evaluacion', 'eventos.resultadoVigente'])->findOrFail($id);

        // Datos del formulario: la fecha en formato de campo date, la jurisdicción por su clave y la actividad sin texto de relleno
        $cliente = $this->datosCliente($registro);
        $cliente['desde'] = $registro->fecha_inicio_relacion ? $registro->fecha_inicio_relacion->format('Y-m-d') : '';
        $cliente['jurisdiccion'] = $this->claveJurisdiccion($registro->jurisdiccion_residencia);
        $cliente['actividad'] = $registro->actividad ?? '';

        $jurisdicciones = $this->jurisdicciones();

        return view('clientes.edit', compact('cliente', 'jurisdicciones'));
    }

    // Método para actualizar los datos de un cliente en la base de datos
    public function update(Request $request, $id)
    {
        // Validación de los datos recibidos (el código no se modifica)
        $request->validate($this->reglas());

        // Encontrar el cliente por su ID
        $cliente = Cliente::findOrFail($id);

        $esPep = $request->boolean('es_pep');

        // Actualización de los datos del cliente
        $cliente->update([
            'nombre_ficticio' => trim($request->nombre),
            'tipo_persona' => $request->tipo_persona,
            'actividad' => $request->actividad,
            'fecha_inicio_relacion' => $request->inicio_relacion,
            'ingresos_verificados' => $request->boolean('ingresos_verificados'),
            'jurisdiccion_residencia' => $this->jurisdicciones()[$request->jurisdiccion],
            'es_pep' => $esPep,
            'vinculo_pep' => $esPep ? $request->vinculo_pep : null,
        ]);

        // Redirigir a la lista de clientes con un mensaje de éxito, con la ficha de este cliente abierta
        return redirect()->route('clientes.index')->with('success', 'Cliente ' . $cliente->codigo . ' actualizado correctamente.')->with('seleccionado', $cliente->codigo);
    }

    // Reglas de validación del formulario (las mismas al crear y al editar)
    private function reglas()
    {
        return [
            'nombre' => 'required|string|max:120',
            'tipo_persona' => 'required|in:individual,juridica',
            'inicio_relacion' => 'nullable|date|before_or_equal:today',
            'jurisdiccion' => ['required', Rule::in(array_keys($this->jurisdicciones()))],
            'actividad' => 'nullable|string',
            'vinculo_pep' => 'nullable|string|max:500',
        ];
    }

    // Jurisdicciones: clave que envía el formulario → texto que se guarda en la BD
    private function jurisdicciones()
    {
        return [
            'local' => 'Local',
            'extranjero' => 'Extranjero',
            'frontera' => 'Zona fronteriza / alto riesgo',
            'offshore' => 'Zona offshore / régimen especial',
            'alto_riesgo' => 'Jurisdicción no cooperante (GAFI)',
            'internacional' => 'Internacional estándar',
        ];
    }

    // Clave del formulario para el texto guardado en la BD ('' si no coincide con ninguna)
    private function claveJurisdiccion($texto)
    {
        foreach ($this->jurisdicciones() as $clave => $nombre) {
            if (strcasecmp($nombre, (string) $texto) === 0) {
                return $clave;
            }
        }

        return '';
    }

    // Siguiente código: el número mayor + 1 (CLI-006 → CLI-007)
    private function siguienteCodigo()
    {
        $mayor = Cliente::pluck('codigo')->map(function ($c) {
            return (int) preg_replace('/\D/', '', $c);
        })->max() ?? 0;

        return sprintf('CLI-%03d', $mayor + 1);
    }

    // Un cliente con la forma que esperan las vistas de clientes
    private function datosCliente($c)
    {
        // Coincidencias: las que una persona descartó ya no cuentan
        $coincidencias = $c->coincidencias->filter(function ($k) {
            return $k->tipo_coincidencia !== 'descartada';
        })->map(function ($k) {
            return [
                'lista' => $k->lista ? $k->lista->nombre_ficticio : '—',
                'tipo' => $k->lista ? $k->lista->tipo_lista : '',
                'estado' => $k->tipo_coincidencia === 'confirmada' ? 'confirmada' : 'por_verificar',
            ];
        })->values()->all();

        // Eventos con su evaluación y la banda del resultado vigente ('' si todavía no se calcula)
        $eventos = $c->eventos->sortBy('codigo')->map(function ($e) {
            $resultado = $e->resultadoVigente;

            return [
                'evento' => $e->codigo,
                'evaluacion' => $e->evaluacion ? $e->evaluacion->codigo : '—',
                'banda' => $resultado ? Str::slug($resultado->banda) : '',
                'residual' => $resultado ? (float) $resultado->riesgo_residual : null,
            ];
        })->values()->all();

        // Score del cliente: promedio del riesgo residual de sus eventos ya calculados
        $residuales = collect($eventos)->pluck('residual')->filter(function ($valor) {
            return $valor !== null;
        });

        $confirmadas = collect($coincidencias)->where('estado', 'confirmada')->count();

        return [
            'id' => $c->id,
            'codigo' => $c->codigo,
            'nombre' => $c->nombre_ficticio,
            'tipo' => $c->tipo_persona,
            'jurisdiccion' => $c->jurisdiccion_residencia ?? '—',
            'actividad' => $c->actividad ?: 'Sin actividad registrada',
            'riesgo' => $confirmadas > 0,
            'desde' => $c->fecha_inicio_relacion ? $c->fecha_inicio_relacion->format('Y-m-d') : '—',
            'pep' => (bool) $c->es_pep,
            'vinculo' => $c->vinculo_pep ?? '',
            'ingresos' => (bool) $c->ingresos_verificados,
            'coincidencias' => $coincidencias,
            'eventos' => $eventos,
            'score' => $residuales->isNotEmpty() ? round($residuales->avg(), 1) : 0,
        ];
    }
}
 