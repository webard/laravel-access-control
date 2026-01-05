<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate as FacadesGate;

final readonly class GateConfigurator
{
    public function __construct(
        private PermissionRegistry $permissionRegistry,
        private VoterRegistry $voterRegistry,
    ) {}

    public function configure(): void
    {
        foreach ($this->permissionRegistry->permissions as $permission) {
            FacadesGate::define(
                $permission,
                function (
                    ?Authenticatable $user = null,
                    ...$arguments
                ) use (
                    $permission
                ): Response {
                    if (! $user instanceof Authenticatable) {
                        return Response::deny('Unauthenicated.');
                    }

                    if (! $user->hasPermissionTo($permission)) {
                        return Response::deny('Unauthorized.');
                    }

                    $vote = $this->voterRegistry->vote(
                        $permission,
                        $user,
                        ...$arguments
                    );

                    return $vote;
                }
            );
        }
    }
}
