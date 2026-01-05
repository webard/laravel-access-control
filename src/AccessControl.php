<?php

namespace Webard\LaravelAccessControl;

class AccessControl
{
    public function __construct(
        private readonly VoterRegistry $voterRegistry,
        private readonly PermissionRegistry $permissionRegistry
    ) {}

    public function registerVoter(string $voterClassOrPermission, ?\Closure $voter = null): void
    {
        $this->voterRegistry->register($voterClassOrPermission, $voter);
    }

    public function registerPermission(string $definitionOrArray): void
    {
        $this->permissionRegistry->register($definitionOrArray);
    }

    public function reflectPermission(string $permissionClass): void
    {
        // return $this->permissionRegistry->reflect($permissionClass);
    }
}
