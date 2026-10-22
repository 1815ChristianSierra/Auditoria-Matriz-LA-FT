<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DecisionesController extends Controller
{
    // Pantalla "Decisión": se abre desde el resultado de un evento (?resultado=5)
    public function create(Request $request)
    {
        $resultado = $this->resultadoVigente($request->resultado);
        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        $evento = Evento::with('evaluacion.version')->findOrFail($resultado->evento_id);
        $evaluacion = $evento->evaluacion;

        // Regla de escalamiento que se cumplió (si hubo una)
        $regla = $resultado->regla_id ? DB::table('parametro_regla')->where('id', $resultado->regla_id)->value('codigo') : null;

        // Historial: cuándo se calculó el resultado y las decisiones que ya se registraron sobre él
        $calculador = $resultado->calculado_por ? DB::table('users')->where('id', $resultado->calculado_por)->value('username') : null;

        $historial = [[
            'titulo' => 'Resultado calculado',
            'detalle' => ($calculador ?? '—') . ' · ' . substr($resultado->calculado_en, 0, 16),
            'estado' => 'hecho',
        ]];

        $registradas = DB::table('decision_seguimiento as d')
            ->join('users as u', 'u.id', '=', 'd.decidido_por')
            ->where('d.resultado_id', $resultado->id)
            ->orderBy('d.id')
            ->get(['d.decision', 'd.decidido_en', 'u.username']);

        foreach ($registradas as $d) {
            $historial[] = [
                'titulo' => $this->tituloDecision($d->decision),
                'detalle' => $d->username . ' · ' . substr($d->decidido_en, 0, 16),
                'estado' => 'hecho',
            ];
        }

        // Mientras no haya una decisión final (o solo "En revisión") queda pendiente
        $ultima = $registradas->last();
        if (!$ultima || $ultima->decision === 'en_revision') {
            $historial[] = ['titulo' => 'Pendiente: decisión final', 'detalle' => 'por definir', 'estado' => 'pendiente'];
        }

        // Usuarios que pueden ser responsables del plan
        $responsables = User::where('estado', 'activo')->orderBy('username')->pluck('username', 'username')->all();

        $evaluacionId = $evaluacion->id;
        $evaluacionCodigo = $evaluacion->codigo;
        $banda = Str::slug($resultado->banda);

        $resultado = [
            'id' => $resultado->id,
            'residual' => $this->numero($resultado->riesgo_residual),
            'regla' => $regla,
            'version' => $evaluacion->version->numero,
            'fecha' => substr($resultado->calculado_en, 0, 10),
        ];
        $evento = ['id' => $evento->codigo, 'pk' => $evento->id];

        return view('decisiones.create', compact('evaluacionId', 'evaluacionCodigo', 'evento', 'banda', 'resultado', 'historial', 'responsables'));
    }

    public function store(Request $request)
    {
        //dd($request->all());//Valida que la informacion llegue desde la vista
        $request->validate([
            'resultado' => 'required|integer|exists:resultado,id',
            'decision' => ['required', Rule::in(array_keys($this->decisiones()))],
            'justificacion' => 'required|string|min:10|max:500',
            'responsable' => 'required_if:decision,plan_de_accion|nullable|string|exists:users,username',
            'fecha_compromiso' => 'required_if:decision,plan_de_accion|nullable|date|after_or_equal:today',
            'plan_accion' => 'required_if:decision,plan_de_accion|nullable|string',
            'estado_seguimiento' => 'nullable|in:pendiente,en_curso,cerrado',
            'evidencia' => 'nullable|string',
        ]);

        // La decisión se registra sobre el resultado vigente
        $resultado = $this->resultadoVigente($request->resultado);
        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        $evento = Evento::findOrFail($resultado->evento_id);
        $responsable = $request->responsable ? User::where('username', $request->responsable)->first() : null;

        try {
            $id = DB::transaction(function () use ($request, $resultado, $evento, $responsable) {
                $nueva = DB::table('decision_seguimiento')->insertGetId([
                    'resultado_id' => $resultado->id,
                    'decision' => $request->decision,
                    'justificacion' => trim($request->justificacion),
                    'decidido_por' => auth()->id(),
                    'responsable_plan' => $responsable ? $responsable->id : null,
                    'plan_accion' => $request->plan_accion ? trim($request->plan_accion) : null,
                    'fecha_compromiso' => $request->fecha_compromiso,
                    'estado_seguimiento' => $request->estado_seguimiento,
                    'evidencia' => $request->evidencia ? trim($request->evidencia) : null,
                ]);

                // Registro en la bitácora con el procedimiento sp_registrar_bitacora
                DB::statement('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?, ?, ?, @bitacora)', [
                    auth()->id(),
                    'REGISTRAR_DECISION',
                    'decision_seguimiento',
                    $nueva,
                    null,
                    json_encode([
                        'decision' => $request->decision,
                        'evento' => $evento->codigo,
                        'resultado' => $resultado->id,
                        'estado_seguimiento' => $request->estado_seguimiento,
                    ], JSON_UNESCAPED_UNICODE),
                    trim($request->justificacion),
                    null,
                ]);

                return $nueva;
            });
        } catch (QueryException $e) {
            $mensaje = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

            return back()->withInput()->with('error', 'No se pudo registrar la decisión: ' . $mensaje);
        }

        // Redirigir al resultado con un mensaje de éxito
        return redirect()->route('resultados.show', $evento->codigo)
            ->with('success', 'Decisión «' . $this->tituloDecision($request->decision) . '» registrada para ' . $evento->codigo . '.');
    }

    // Resultado vigente a partir del id recibido; si no sirve, devuelve la redirección con el motivo
    private function resultadoVigente($id)
    {
        if (!$id) {
            return redirect()->route('evaluaciones.index')->with('error', 'Abra el resultado de un evento y use «Registrar decisión» desde ahí.');
        }

        $resultado = DB::table('resultado')->where('id', $id)->first();
        if (!$resultado) {
            return redirect()->route('evaluaciones.index')->with('error', 'El resultado indicado no existe.');
        }

        if (!$resultado->vigente) {
            $codigo = Evento::where('id', $resultado->evento_id)->value('codigo');

            return redirect()->route('resultados.show', $codigo)
                ->with('error', 'Ese resultado fue reemplazado por uno más reciente: la decisión se registra sobre el vigente.');
        }

        return $resultado;
    }

    // Decisiones que acepta la BD (check de decision_seguimiento)
    private function decisiones()
    {
        return [
            'en_revision' => 'En revisión',
            'escalar_a_cumplimiento' => 'Escalar a Cumplimiento',
            'descartar' => 'Descartar',
            'plan_de_accion' => 'Plan de acción',
        ];
    }

    // Texto de la decisión para el historial y los mensajes
    private function tituloDecision($decision)
    {
        $titulos = [
            'en_revision' => 'Marcado «En revisión»',
            'escalar_a_cumplimiento' => 'Escalada a Cumplimiento',
            'descartar' => 'Descartada',
            'plan_de_accion' => 'Plan de acción registrado',
        ];

        return $titulos[$decision] ?? $decision;
    }

    // Quita los ceros sobrantes: 96.00 → 96, 50.60 → 50.6
    private function numero($valor)
    {
        $valor = (float) $valor;

        return $valor == floor($valor) ? (int) $valor : $valor;
    }
}
 