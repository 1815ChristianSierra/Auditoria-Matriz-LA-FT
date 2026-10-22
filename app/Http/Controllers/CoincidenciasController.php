<?php

namespace App\Http\Controllers;

use App\Models\CoincidenciaLista;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoincidenciasController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todas las coincidencias con su cliente, su lista y quién las verificó; las pendientes primero
        $filas = CoincidenciaLista::with(['cliente', 'lista', 'verificador'])
            ->orderByRaw("tipo_coincidencia = 'parcial' desc")
            ->orderByDesc('id')
            ->get();
        //dd($filas);

        // Registros con la forma que espera la vista, por id de coincidencia
        $registros = $filas->mapWithKeys(function ($k) {
            return [$k->id => $this->datosCoincidencia($k)];
        })->all();

        // Registro que se abre: el que se acaba de decidir, el del cliente indicado en la URL (?cliente=CLI-004) o el primero
        $seleccionado = session('seleccionado');
        if (!$seleccionado && $request->filled('cliente')) {
            $seleccionado = collect($registros)->firstWhere('codigo', $request->cliente)['id'] ?? null;
        }
        $seleccionado = $seleccionado ?? array_key_first($registros);

        return view('coincidencias.index', compact('registros', 'seleccionado'));
    }

    // Dictamen de una coincidencia: confirmarla, descartarla (falso positivo) o dejarla como parcial
    public function update(Request $request, $id)
    {
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'accion' => 'required|in:confirmar,descartar,parcial',
            'observacion' => 'required_unless:accion,parcial|nullable|string|min:10',
        ]);

        // Encontrar la coincidencia por su ID
        $coincidencia = CoincidenciaLista::with(['cliente', 'lista'])->findOrFail($id);

        $nuevo = ['confirmar' => 'confirmada', 'descartar' => 'descartada', 'parcial' => 'parcial'][$request->accion];
        $anterior = $coincidencia->tipo_coincidencia;

        if ($anterior === $nuevo) {
            return redirect()->route('coincidencias.index')->with('seleccionado', $coincidencia->id)
                ->with('error', 'La coincidencia de ' . $coincidencia->cliente->codigo . ' ya está como «' . $nuevo . '».');
        }

        $acciones = ['confirmada' => 'CONFIRMAR_COINCIDENCIA', 'descartada' => 'DESCARTAR_COINCIDENCIA', 'parcial' => 'REABRIR_COINCIDENCIA'];
        $observacion = trim((string) $request->observacion);

        try {
            DB::transaction(function () use ($coincidencia, $nuevo, $anterior, $acciones, $observacion) {
                // Quién decidió y cuándo (la observación anterior se conserva si no se escribe otra)
                $coincidencia->tipo_coincidencia = $nuevo;
                $coincidencia->verificada_por = auth()->id();
                $coincidencia->verificada_en = now();
                if ($observacion !== '') {
                    $coincidencia->observacion = $observacion;
                }
                $coincidencia->save();

                // Registro en la bitácora con el procedimiento sp_registrar_bitacora
                DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?, ?, ?, @bitacora)', [
                    auth()->id(),
                    $acciones[$nuevo],
                    'coincidencia_lista',
                    $coincidencia->id,
                    json_encode(['tipo_coincidencia' => $anterior]),
                    json_encode([
                        'tipo_coincidencia' => $nuevo,
                        'cliente' => $coincidencia->cliente->codigo,
                        'lista' => $coincidencia->lista->nombre_ficticio,
                    ], JSON_UNESCAPED_UNICODE),
                    $observacion !== '' ? $observacion : null,
                    null,
                ]);
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return redirect()->route('coincidencias.index')->with('seleccionado', $coincidencia->id)
                ->with('error', 'No se pudo registrar el dictamen: ' . $mensaje);
        }

        $texto = ['confirmada' => 'confirmada.', 'descartada' => 'descartada (falso positivo).', 'parcial' => 'reabierta como parcial.'][$nuevo];

        // Redirigir a la lista con un mensaje de éxito, con el mismo registro abierto
        return redirect()->route('coincidencias.index')->with('success', 'Coincidencia de ' . $coincidencia->cliente->codigo . ' ' . $texto)
            ->with('seleccionado', $coincidencia->id);
    }

    // Una coincidencia con la forma que espera la vista
    private function datosCoincidencia($k)
    {
        $cliente = $k->cliente;
        $lista = $k->lista;

        // Parecido entre el nombre del cliente y el del registro en la lista (0 a 1)
        $jaro = $this->jaroWinkler($cliente->nombre_ficticio, $lista->nombre_ficticio);
        $decidida = $k->tipo_coincidencia !== 'parcial';

        // Lado del cliente: "coincide" solo donde hay algo contra qué comparar (el nombre y, en listas PEP, si el cliente es PEP)
        $datosCliente = [
            ['label' => 'Nombre', 'valor' => $cliente->nombre_ficticio, 'coincide' => $jaro >= 0.85],
            ['label' => 'Tipo', 'valor' => $cliente->tipo_persona === 'juridica' ? 'Persona jurídica' : 'Persona individual', 'coincide' => null],
            ['label' => 'País', 'valor' => $cliente->jurisdiccion_residencia ?? '—', 'coincide' => null],
            ['label' => 'Actividad', 'valor' => $cliente->actividad ?: '—', 'coincide' => null],
            ['label' => 'PEP', 'valor' => $cliente->es_pep ? 'Sí' : 'No', 'coincide' => $lista->tipo_lista === 'PEP' ? (bool) $cliente->es_pep : null],
        ];

        // Lado de la lista: lo que guarda la BD del registro
        $datosLista = [
            ['label' => 'Nombre', 'valor' => $lista->nombre_ficticio, 'nota' => 'SIMILITUD ' . round($jaro * 100) . ' %', 'alerta' => false],
            ['label' => 'Tipo de lista', 'valor' => $lista->tipo_lista, 'nota' => $lista->sintetico ? 'LISTA SINTÉTICA' : '', 'alerta' => false],
            ['label' => 'Fuente', 'valor' => $lista->fuente_ficticia ?? '—', 'nota' => '', 'alerta' => false],
            ['label' => 'Estado de la lista', 'valor' => $lista->activo ? 'Activa' : 'Inactiva', 'nota' => '', 'alerta' => !$lista->activo],
        ];

        return [
            'id' => $k->id,
            'codigo' => $cliente->codigo,
            'nombre' => $lista->nombre_ficticio,
            'tipo_lista' => $lista->tipo_lista,
            'estado' => $k->tipo_coincidencia,
            'verificada_por' => $decidida && $k->verificador ? $k->verificador->username : null,
            'fecha' => $decidida && $k->verificada_en ? $k->verificada_en->format('Y-m-d') : null,
            'sintetica' => (bool) $lista->sintetico,
            'jaro' => round($jaro, 2),
            'ref' => $lista->fuente_ficticia ?? '—',
            'entidad' => $cliente->jurisdiccion_residencia ?? '—',
            'observacion' => $k->observacion ?? '',
            'cliente' => $datosCliente,
            'lista' => $datosLista,
        ];
    }

    // Similitud Jaro-Winkler entre dos nombres, sin distinguir mayúsculas ni acentos (1 = idénticos)
    private function jaroWinkler($a, $b)
    {
        $a = strtolower(Str::ascii(trim((string) $a)));
        $b = strtolower(Str::ascii(trim((string) $b)));

        if ($a === $b) {
            return $a === '' ? 0.0 : 1.0;
        }

        $la = strlen($a);
        $lb = strlen($b);
        if ($la === 0 || $lb === 0) {
            return 0.0;
        }

        // Letras iguales que están a una distancia razonable
        $rango = max(intdiv(max($la, $lb), 2) - 1, 0);
        $usadaA = array_fill(0, $la, false);
        $usadaB = array_fill(0, $lb, false);
        $iguales = 0;

        for ($i = 0; $i < $la; $i++) {
            $desde = max(0, $i - $rango);
            $hasta = min($i + $rango + 1, $lb);
            for ($j = $desde; $j < $hasta; $j++) {
                if ($usadaB[$j] || $a[$i] !== $b[$j]) {
                    continue;
                }
                $usadaA[$i] = true;
                $usadaB[$j] = true;
                $iguales++;
                break;
            }
        }

        if ($iguales === 0) {
            return 0.0;
        }

        // Letras iguales que aparecen en distinto orden
        $trasposiciones = 0;
        $k = 0;
        for ($i = 0; $i < $la; $i++) {
            if (!$usadaA[$i]) {
                continue;
            }
            while (!$usadaB[$k]) {
                $k++;
            }
            if ($a[$i] !== $b[$k]) {
                $trasposiciones++;
            }
            $k++;
        }
        $trasposiciones = $trasposiciones / 2;

        $jaro = ($iguales / $la + $iguales / $lb + ($iguales - $trasposiciones) / $iguales) / 3;

        // Bonificación por prefijo común (hasta 4 letras)
        $prefijo = 0;
        while ($prefijo < min(4, $la, $lb) && $a[$prefijo] === $b[$prefijo]) {
            $prefijo++;
        }

        return $jaro + $prefijo * 0.1 * (1 - $jaro);
    }
}
 