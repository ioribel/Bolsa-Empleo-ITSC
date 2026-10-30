<?php

namespace App\Listeners;

use App\Events\CompanySubmittedForApproval;
use App\Models\Empresa;
use App\Models\User;
use App\Services\NotificationDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminsOfPendingCompany implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(private readonly NotificationDeliveryService $deliveries) {}

    public function handle(CompanySubmittedForApproval $event): void
    {
        $company = Empresa::query()
            ->where('estado', 'pendiente')
            ->find($event->companyId);

        if (! $company) {
            return;
        }

        User::query()
            ->where('rol', 'admin')
            ->chunkById(100, function ($admins) use ($company): void {
                foreach ($admins as $admin) {
                    $this->deliveries->sendOnce(
                        $admin,
                        "admin.company.pending:{$company->id}:user:{$admin->id}",
                        [
                            'event' => 'admin.company.pending',
                            'title' => 'Empresa pendiente de aprobación',
                            'message' => "{$company->nombre_empresa} ha solicitado registrarse y está pendiente de revisión.",
                            'company_id' => $company->id,
                        ],
                    );
                }
            });
    }
}
