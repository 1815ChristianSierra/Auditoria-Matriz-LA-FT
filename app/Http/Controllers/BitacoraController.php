<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\VersionParametros;
use Illuminate\Support\Facades\DB;

class BitacoraController extends Controller
{
    public function index()
    {
        // Obtener los 500 registros más recientes con su usuario y su aprobador
        $filas = Bitacora::with(['usuario', 'aprobador'])->orderByDesc('id')->limit(500)->get();
        //dd($filas);

        // Número de cada versión, para mostrar "versión v1.2" en vez de un id
        $versiones = VersionParametros::pluck('numero', 'id');

        // Registros con la forma que espera la vista
        $registros = $filas->map(function ($f) use ($versiones) {
            switch ($f->entidad_tipo) {
                case 'version_parametros':
                    $entidad = 'versión ' . $versiones->get($f->entidad_id, '#' . $f->entidad_id);
                    $modulo = 'GESTIÓN_PARAMÉTRICA';
                    break;
                case 'resultado':
                case 'evento':
                    $entidad = $f->entidad_tipo . ' #' . $f->entidad_id;
                    $modulo = 'CALIFICACIÓN_RIESGO';
                    break;
                default:
                    $entidad = $f->entidad_tipo . ($f->entidad_id ? ' #' . $f->entidad_id : '');
                    $modulo = strtoupper($f->entidad_tipo);
            }

            return [
                'id' => (int) $f->id,
                'fecha' => $f->fecha_hora->format('Y-m-d H:i:s'),
                'usuario' => $f->usuario ? $f->usuario->username : '—',
                'accion' => $f->accion,
                'entidad' => $entidad,
                'entidad_tipo' => $f->entidad_tipo,
                'motivo' => $f->motivo ?? '',
                'aprobador' => $f->aprobador ? $f->aprobador->username : null,
                'hash' => substr($f->hash_registro, 0, 8),
                'hash_anterior' => $f->hash_anterior ? substr($f->hash_anterior, 0, 8) : null,
                'modulo' => $modulo,
                'anterior' => $f->valor_anterior,
                'nuevo' => $f->valor_nuevo,
            ];
        })->all();

        // Registro que se abre al entrar: el más reciente
        $seleccionado = $registros ? $registros[0]['id'] : null;

        // Fechas iniciales del filtro: desde el registro más antiguo hasta hoy, para que se vea todo
        $hoy = date('Y-m-d');
        $desde = $registros ? substr($registros[count($registros) - 1]['fecha'], 0, 10) : $hoy;
        $hasta = $registros ? max($hoy, substr($registros[0]['fecha'], 0, 10)) : $hoy;

        $integridad = $this->verificarCadena();

        return view('bitacora.index', compact('registros', 'seleccionado', 'desde', 'hasta', 'integridad'));
    }

    // Botón "Verificar integridad": vuelve a comprobar la cadena y devuelve el resultado
    public function verificar()
    {
        return response()->json($this->verificarCadena());
    }

    // Comprueba la cadena de hashes con la vista v_bitacora_alterada de la BD:
    // vacía = íntegra; un id = ese registro fue modificado; id 0 = se borraron registros del final
    private function verificarCadena()
    {
        $total = Bitacora::count();
        $ultimo = Bitacora::orderByDesc('id')->value('hash_registro');

        $resultado = [
            'estado' => 'ok',
            'registros' => $total,
            'ultimo_hash' => $ultimo ? substr($ultimo, 0, 8) : '',
            'ids' => [],
            'mensaje' => '',
            'hora' => date('H:i:s'),
        ];

        if (!$total) {
            $resultado['estado'] = 'vacia';
            $resultado['mensaje'] = 'La bitácora aún no tiene registros.';

            return $resultado;
        }

        try {
            $alteradas = DB::table('v_bitacora_alterada')->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all();
        } catch (\Throwable $e) {
            $resultado['estado'] = 'error';
            $resultado['mensaje'] = 'No se pudo verificar la cadena: ' . $e->getMessage();

            return $resultado;
        }

        if (count($alteradas)) {
            $modificados = array_filter($alteradas);
            $partes = [];
            if ($modificados) {
                $partes[] = 'registros modificados: #' . implode(', #', $modificados);
            }
            if (in_array(0, $alteradas, true)) {
                $partes[] = 'se borraron registros del final';
            }

            $resultado['estado'] = 'alterada';
            $resultado['ids'] = array_values($modificados);
            $resultado['mensaje'] = 'Cadena de hashes ALTERADA · ' . implode(' · ', $partes);

            return $resultado;
        }

        $resultado['mensaje'] = 'Cadena de hashes íntegra · ' . ($total === 1 ? '1 registro verificado' : $total . ' registros verificados') . ' · último hash';

        return $resultado;
    }
}
 