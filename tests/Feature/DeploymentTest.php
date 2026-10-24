<?php
namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use DatabaseTransactions;

    private function account(string $role, string $state = 'activo'): User
    {
        return User::factory()->create([
            'rol_id' => Rol::firstOrCreate(['nombre'=>$role])->id,
            'estado' => $state,
        ]);
    }

    public function test_health_and_login_work_and_registration_is_closed(): void
    {
        $this->get('/health')->assertOk()->assertJson(['status'=>'ok']);
        $this->get('/login')->assertOk()->assertDontSee('analista1@gmail.com');
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_roles_are_repeatable_and_demo_users_are_rejected_in_production(): void
    {
        $this->seed(RolSeeder::class);
        $before = Rol::count();
        $this->seed(RolSeeder::class);
        $this->assertSame($before, Rol::count());
        $this->app->instance('env', 'production');
        $this->expectException(\RuntimeException::class);
        (new \Database\Seeders\UserSeeder)->run();
    }

    public function test_auditor_cannot_create_users_or_modify_catalogs(): void
    {
        $this->actingAs($this->account('auditor'));
        $this->get('/usuarios')->assertForbidden();
        $this->post('/usuarios', [])->assertForbidden();
        $this->post('/catalogos/factores', [])->assertForbidden();
        $this->get('/dashboard')->assertOk();
    }

    public function test_main_screens_render_on_an_empty_database(): void
    {
        $this->actingAs($this->account('administrador'));
        foreach (['/dashboard','/evaluaciones','/clientes','/coincidencias','/versiones','/usuarios','/bitacora','/catalogos','/sensibilidad'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/eventos/crear')->assertRedirect('/evaluaciones');
    }

    public function test_blocked_accounts_cannot_login_or_reuse_sessions(): void
    {
        $user = $this->account('analista', 'bloqueado');
        $this->post('/login', ['email'=>$user->email, 'password'=>'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
        $this->assertGuest();
    }

    public function test_empty_database_can_create_first_draft_with_user_thresholds(): void
    {
        $this->assertSame(0, DB::table('version_parametros')->count());
        $this->actingAs($this->account('administrador'));
        $this->post('/versiones', ['numero'=>'v1.0', 'motivo'=>'QA synthetic draft', 'umbrales'=>[25,50,75]])
            ->assertSessionHasNoErrors()->assertRedirect();
        $version = DB::table('version_parametros')->first();
        $this->assertSame('borrador', $version->estado);
        $this->assertSame(4, DB::table('banda')->where('version_id',$version->id)->count());
        $this->get('/versiones/'.$version->id.'/ver')->assertOk();
        $this->get('/versiones/editar/'.$version->id)->assertOk();
        $this->assertSame(1, DB::table('bitacora')->count());
        $this->assertSame(0, (int) DB::table('v_bitacora_alterada')->count());
    }
}
