<?php

namespace Tests\Feature;

use App\Events\StudentAppliedToJob;
use App\Events\ApplicationStatusChanged;
use App\Events\CompanySubmittedForApproval;
use App\Events\JobVacancyPublished;
use App\Models\Carrera;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use App\Notifications\PlatformDatabaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_application_notifies_only_the_vacancy_owner(): void
    {
        config(['queue.default' => 'sync']);
        Storage::fake('local');
        [$studentUser, , $vacancy, $companyUser] = $this->createContext();
        $vacancy->update(['estado' => 'publicada']);

        $this->actingAs($studentUser)
            ->post(route('estudiante.vacantes.postular', $vacancy), [
                'cv' => UploadedFile::fake()->create('curriculum.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('estudiante.vacantes.show', $vacancy));

        $application = $vacancy->postulaciones()->sole();
        event(new StudentAppliedToJob((int) $application->id));
        $notification = $companyUser->notifications()->sole();
        $this->assertSame('application.received', $notification->data['event']);
        $this->assertSame('Nueva postulación recibida', $notification->data['title']);
        $this->assertSame('Juan Pérez se ha postulado a tu vacante de Desarrollador Web.', $notification->data['message']);
        $this->assertSame(0, $studentUser->notifications()->count());

        $this->actingAs($studentUser)
            ->post(route('estudiante.vacantes.postular', $vacancy), [
                'cv' => UploadedFile::fake()->create('curriculum.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHas('error', 'Ya te has postulado a esta vacante.');
        $this->assertSame(1, $companyUser->notifications()->count());
    }

    public function test_publishing_vacancy_notifies_each_related_student_once(): void
    {
        config(['queue.default' => 'sync']);
        [$studentUser, $student, $vacancy] = $this->createContext();
        $secondCareer = Carrera::create(['nombre' => 'Administración']);
        $otherStudentUser = User::factory()->create(['rol' => 'estudiante']);
        $otherStudent = Estudiante::create([
            'user_id' => $otherStudentUser->id,
            'carrera_id' => $secondCareer->id,
            'matricula' => 'MATRICULA-'.Str::upper(Str::random(8)),
            'tipo' => 'estudiante',
        ]);
        $unrelatedCareer = Carrera::create(['nombre' => 'Contabilidad']);
        $unrelatedUser = User::factory()->create(['rol' => 'estudiante']);
        Estudiante::create([
            'user_id' => $unrelatedUser->id,
            'carrera_id' => $unrelatedCareer->id,
            'matricula' => 'MATRICULA-'.Str::upper(Str::random(8)),
            'tipo' => 'estudiante',
        ]);
        $vacancy->carreras()->attach($secondCareer->id);

        $vacancy->publicar();

        $this->assertSame(1, $studentUser->notifications()->count());
        $this->assertSame(1, $otherStudentUser->notifications()->count());
        $this->assertSame(0, $unrelatedUser->notifications()->count());
        $this->assertSame('vacancy.published', $studentUser->notifications()->sole()->data['event']);

        event(new \App\Events\JobVacancyPublished((int) $vacancy->id));

        $this->assertSame(1, $studentUser->notifications()->count());
        $this->assertSame(1, $otherStudentUser->notifications()->count());
        $this->assertSame(0, $unrelatedUser->notifications()->count());
        $this->assertSame($student->id, $studentUser->estudiante->id);
        $this->assertSame($otherStudent->id, $otherStudentUser->estudiante->id);
    }

    public function test_publishing_vacancy_queues_one_notification_listener(): void
    {
        Queue::fake();
        [, , $vacancy] = $this->createContext();

        $vacancy->publicar();

        Queue::assertPushed(\Illuminate\Events\CallQueuedListener::class, 2);
    }

    public function test_publishing_vacancy_notifies_all_admins_once_independently_of_targeted_careers(): void
    {
        config(['queue.default' => 'sync']);
        [$studentUser, , $vacancy] = $this->createContext();
        $vacancy->carreras()->detach();
        $firstAdmin = User::factory()->create(['rol' => 'admin']);
        $secondAdmin = User::factory()->create(['rol' => 'admin']);

        $vacancy->publicar();

        foreach ([$firstAdmin, $secondAdmin] as $admin) {
            $notification = $admin->notifications()->sole();
            $this->assertSame('admin.vacancy.published', $notification->data['event']);
            $this->assertSame($vacancy->id, $notification->data['vacancy_id']);
        }
        $this->assertSame(0, $studentUser->notifications()->count());

        JobVacancyPublished::dispatch((int) $vacancy->id);

        $this->assertSame(1, $firstAdmin->notifications()->count());
        $this->assertSame(1, $secondAdmin->notifications()->count());
    }

    public function test_company_registration_notifies_all_admins_and_is_idempotent(): void
    {
        config(['queue.default' => 'sync']);
        $firstAdmin = User::factory()->create(['rol' => 'admin']);
        $secondAdmin = User::factory()->create(['rol' => 'admin']);
        $student = User::factory()->create(['rol' => 'estudiante']);

        $this->post('/register/empresa', [
            'name' => 'Empresa User',
            'email' => 'nueva-empresa@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'nombre_empresa' => 'Empresa Pendiente',
            'rnc' => 'RNC-PENDIENTE-001',
        ])->assertRedirect(route('login'));

        $company = Empresa::query()->where('rnc', 'RNC-PENDIENTE-001')->sole();
        foreach ([$firstAdmin, $secondAdmin] as $admin) {
            $notification = $admin->notifications()->sole();
            $this->assertSame('admin.company.pending', $notification->data['event']);
            $this->assertSame($company->id, $notification->data['company_id']);
        }
        $this->assertSame(0, $student->notifications()->count());

        CompanySubmittedForApproval::dispatch((int) $company->id);

        $this->assertSame(1, $firstAdmin->notifications()->count());
        $this->assertSame(1, $secondAdmin->notifications()->count());
    }

    public function test_admin_notification_links_lead_to_relevant_admin_lists(): void
    {
        [$studentUser, , $vacancy] = $this->createContext();
        $admin = User::factory()->create(['rol' => 'admin']);
        $vacancy->publicar();

        $vacancyNotification = $admin->notifications()
            ->where('data->event', 'admin.vacancy.published')
            ->sole();
        $this->actingAs($admin)
            ->get(route('notifications.open', $vacancyNotification))
            ->assertRedirect(route('admin.vacantes.index', ['buscar' => $vacancy->titulo]));

        $company = Empresa::create([
            'user_id' => User::factory()->create(['rol' => 'empresa'])->id,
            'nombre_empresa' => 'Por aprobar',
            'rnc' => 'RNC-POR-APROBAR',
            'estado' => 'pendiente',
        ]);
        CompanySubmittedForApproval::dispatch((int) $company->id);
        $companyNotification = $admin->notifications()
            ->where('data->event', 'admin.company.pending')
            ->sole();

        $this->get(route('notifications.open', $companyNotification))
            ->assertRedirect(route('admin.empresas.index', [
                'estado' => 'pendiente',
                'buscar' => $company->nombre_empresa,
            ]));

        $studentNotification = $studentUser->notifications()->sole();
        $this->assertSame('vacancy.published', $studentNotification->data['event']);
    }

    public function test_approval_and_rejection_notify_only_the_applicant_after_status_change(): void
    {
        config(['queue.default' => 'sync']);
        [$studentUser, $student, $vacancy, $companyUser] = $this->createContext();
        $accepted = $vacancy->postulaciones()->create([
            'estudiante_id' => $student->id,
            'estado' => 'en_revision',
        ]);

        $this->actingAs($companyUser)
            ->patch(route('empresa.postulaciones.aceptar', $accepted))
            ->assertRedirect();

        $this->assertSame('application.aceptada', $studentUser->notifications()->sole()->data['event']);
        $this->assertSame(0, $companyUser->notifications()->count());

        $rejectedUser = User::factory()->create(['rol' => 'estudiante']);
        $rejectedStudent = Estudiante::create([
            'user_id' => $rejectedUser->id,
            'carrera_id' => $student->carrera_id,
            'matricula' => 'MATRICULA-'.Str::upper(Str::random(8)),
            'tipo' => 'estudiante',
        ]);
        $rejected = $vacancy->postulaciones()->create([
            'estudiante_id' => $rejectedStudent->id,
            'estado' => 'recibida',
        ]);

        $this->patch(route('empresa.postulaciones.rechazar', $rejected))
            ->assertRedirect();

        $this->assertSame('application.rechazada', $rejectedUser->notifications()->sole()->data['event']);

        event(new ApplicationStatusChanged((int) $accepted->id, 'en_revision', 'aceptada'));
        $this->assertSame(1, $studentUser->notifications()->count());
    }

    public function test_user_can_mark_only_their_own_notifications_read(): void
    {
        $owner = User::factory()->create(['rol' => 'estudiante']);
        $other = User::factory()->create(['rol' => 'empresa']);
        $owner->notify(new PlatformDatabaseNotification([
            'event' => 'test',
            'title' => 'Notificación propia',
            'message' => 'Solo debe verla su destinatario.',
        ]));
        $notification = $owner->unreadNotifications()->sole();

        $this->actingAs($other)
            ->patchJson(route('notifications.read', $notification->id))
            ->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);

        $this->actingAs($owner)
            ->patchJson(route('notifications.read', $notification->id))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_view_shared_notification_ui_and_mark_all_as_read(): void
    {
        [$studentUser, , , $companyUser] = $this->createContext();
        $adminUser = User::factory()->create(['rol' => 'admin']);
        $studentUser->notify(new PlatformDatabaseNotification([
            'event' => 'test',
            'title' => 'Notificación de ejemplo',
            'message' => 'Contenido de prueba.',
        ]));

        foreach ([$studentUser, $companyUser, $adminUser] as $user) {
            $this->actingAs($user)
                ->get(route('notifications.index'))
                ->assertOk()
                ->assertSee('aria-label="Notificaciones"', false)
                ->assertSee(route('notifications.feed'), false)
                ->assertSee(route('notifications.index'), false);
        }

        $this->actingAs($studentUser)
            ->patchJson(route('notifications.read-all'))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);
        $this->assertSame(0, $studentUser->fresh()->unreadNotifications()->count());
    }

    public function test_notification_feed_and_open_routes_are_scoped_and_check_resource_ownership(): void
    {
        [$studentUser, , $vacancy, $companyUser] = $this->createContext();
        $vacancy->publicar();
        $notification = $studentUser->notifications()->sole();

        $this->actingAs($companyUser)
            ->getJson(route('notifications.feed'))
            ->assertOk()
            ->assertJsonPath('unread_count', 0)
            ->assertJsonCount(0, 'notifications');

        $this->actingAs($studentUser)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('estudiante.vacantes.show', $vacancy));
        $this->assertNotNull($notification->fresh()->read_at);

        $notification->forceFill(['read_at' => null])->save();
        $vacancy->cerrar();

        $this->get(route('notifications.open', $notification))
            ->assertRedirect(route('notifications.index'));
    }

    public function test_event_dispatched_inside_rolled_back_transaction_does_not_enqueue_notification(): void
    {
        Queue::fake();
        [$studentUser] = $this->createContext();

        try {
            DB::transaction(function (): void {
                StudentAppliedToJob::dispatch(987654);
                throw new \RuntimeException('Rollback test');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Rollback test', $exception->getMessage());
        }

        Queue::assertNothingPushed();
        $this->assertSame(0, $studentUser->notifications()->count());
    }

    public function test_pending_company_event_inside_rolled_back_transaction_does_not_enqueue_notification(): void
    {
        Queue::fake();

        try {
            DB::transaction(function (): void {
                $user = User::factory()->create(['rol' => 'empresa']);
                $company = Empresa::create([
                    'user_id' => $user->id,
                    'nombre_empresa' => 'Registro revertido',
                    'rnc' => 'RNC-ROLLBACK-001',
                    'estado' => 'pendiente',
                ]);
                CompanySubmittedForApproval::dispatch((int) $company->id);
                throw new \RuntimeException('Rollback test');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Rollback test', $exception->getMessage());
        }

        Queue::assertNothingPushed();
    }

    private function createContext(): array
    {
        $career = Carrera::create(['nombre' => 'Informática']);
        $studentUser = User::factory()->create([
            'name' => 'Juan Pérez',
            'rol' => 'estudiante',
        ]);
        $student = Estudiante::create([
            'user_id' => $studentUser->id,
            'carrera_id' => $career->id,
            'matricula' => 'MATRICULA-'.Str::upper(Str::random(8)),
            'tipo' => 'estudiante',
        ]);
        $companyUser = User::factory()->create(['rol' => 'empresa']);
        $company = Empresa::create([
            'user_id' => $companyUser->id,
            'nombre_empresa' => 'Tech Solutions',
            'rnc' => 'RNC-'.Str::upper(Str::random(8)),
            'estado' => 'aprobada',
        ]);
        $vacancy = Vacante::create([
            'empresa_id' => $company->id,
            'titulo' => 'Desarrollador Web',
            'descripcion' => 'Desarrollar aplicaciones.',
            'tipo_empleo' => 'tiempo_completo',
            'modalidad' => 'remoto',
            'estado' => 'borrador',
        ]);
        $vacancy->carreras()->attach($career->id);

        return [$studentUser, $student, $vacancy, $companyUser];
    }
}
