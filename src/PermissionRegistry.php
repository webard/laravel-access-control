<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl;

use Webard\LaravelAccessControl\Contracts\PermissionDefinition;
use Webard\LaravelAccessControl\Exceptions\PermissionAlreadyRegisteredException;

final class PermissionRegistry
{
    /**
     * @var array<string,PermissionDefinition>
     */
    public private(set) array $permissions = [];

    /**
     * @var class-string<PermissionDefinition>[]
     */
    public private(set) array $permissionDefinitions = [];

    /**
     * @param  class-string<PermissionDefinition>|array<string,class-string<PermissionDefinition>>  $definitionOrArray
     */
    public function register(string | array $definitionOrArray): void
    {
        if (is_array($definitionOrArray)) {
            foreach ($definitionOrArray as $definition) {
                $this->register($definition);
            }

            return;
        }

        if (
            in_array($definitionOrArray, $this->permissionDefinitions, true)
        ) {
            throw new PermissionAlreadyRegisteredException(
                sprintf('Permission enum %s is already registered.', $definitionOrArray),
            );
        }

        $this->permissionDefinitions[] = $definitionOrArray;
        foreach ($definitionOrArray::cases() as $permission) {
            if (isset($this->permissions[$permission->value])) {
                throw new PermissionAlreadyRegisteredException(
                    sprintf('Permission %s is already registered.', $permission->value),
                );
            }

            $this->permissions[$permission->value] = $permission;
        }
    }

    /**
     * @param  class-string<PermissionDefinition>  $definition
     */
    public function isDefined(string $definition): bool
    {
        return in_array($definition, $this->permissionDefinitions, true);
    }
}
