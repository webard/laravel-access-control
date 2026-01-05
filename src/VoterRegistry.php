<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl;

use Closure;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use ReflectionClass;
use ReflectionMethod;
use Webard\LaravelAccessControl\Attributes\VoterForPermission;
use Webard\LaravelAccessControl\Contracts\PermissionDefinition;

/**
 * @property class-string<PermissionDefinition>[] $permissions
 */
final class VoterRegistry
{
    /**
     * @var array<string, array<int, callable>>
     */
    private array $voters = [];

    /**
     * @param  class-string<PermissionDefinition>[]  $permissions
     */
    public function __construct(
        public private(set) array $permissions = [],
    ) {}

    public function register(PermissionDefinition $permission, Closure $voter): void
    {
        $identifier = spl_object_hash($voter);

        if (isset($this->voters[self::class][$permission->name][$identifier])) {
            return;
        }

        $this->voters[self::class][$permission->value][$identifier] = $voter;
    }

    /**
     * @param  class-string|class-string[]  $voterClass
     */
    public function registerClass(string | array $voterClass): void
    {
        if (is_array($voterClass)) {
            foreach ($voterClass as $class) {
                $this->registerClass($class);
            }

            return;
        }

        $reflection = new ReflectionClass($voterClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(VoterForPermission::class);

            foreach ($attributes as $attribute) {
                /** @var VoterForPermission $instance */
                $instance = $attribute->newInstance();

                $this->register(
                    $instance->permission,
                    fn (Authenticatable $user, mixed ...$arguments): Response => $reflection
                        ->newInstance()
                        ->{$method->getName()}($user, ...$arguments),
                );
            }
        }
    }

    public function countVoters(PermissionDefinition $action): int
    {
        return count($this->voters[self::class][$action->value] ?? []);
    }

    public function vote(PermissionDefinition $action, Authenticatable $user, mixed ...$arguments): Response
    {
        $voters = $this->voters[self::class][$action->value] ?? [];

        foreach ($voters as $voter) {

            $result = $voter($user, ...$arguments);

            assert($result instanceof Response);

            if ($result->denied()) {
                return $result;
            }
        }

        return Response::allow();
    }
}
