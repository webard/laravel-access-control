<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl;

use Webard\LaravelAccessControl\Attributes\PermissionDescription;
use Webard\LaravelAccessControl\Attributes\PermissionGroup;
use Webard\LaravelAccessControl\Attributes\PermissionName;
use Webard\LaravelAccessControl\Contracts\PermissionDefinition;
use Webard\LaravelAccessControl\Contracts\PermissionGroupDefinition;
use Webard\LaravelAccessControl\Dto\PermissionDto;
use Webard\LaravelAccessControl\Exceptions\PermissionGroupRequiredException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionClassConstant;

final readonly class PermissionReflector
{
    public function __construct(
        private string $permission,
    ) {}

    public function getGroup(): PermissionGroupDefinition
    {
        $reflection = new ReflectionClass($this->permission);
        $group = $reflection->getAttributes(PermissionGroup::class);

        if ($group === []) {
            throw new PermissionGroupRequiredException('PermissionGroup attribute is missing for ' . $this->permission . '.');
        }

        $attributeInstance = $group[0]->newInstance();

        return new $attributeInstance->group;
    }

    /**
     * @return Collection<int,PermissionDto>
     */
    public function getValues(): Collection
    {
        return new Collection($this->permission::cases())
            ->map(fn (PermissionDefinition $case): PermissionDto => new PermissionDto(
                name: $this->getName($case->name),
                enum: $case,
                slug: $case->value,
                description: $this->getDescription($case->name),
            ));
    }

    private function getDescription(string $enumCase): ?string
    {
        $ref = new ReflectionClassConstant($this->permission, $enumCase);
        $classAttributes = $ref->getAttributes(PermissionDescription::class);

        if ($classAttributes === []) {
            return null;
        }

        $attribute = $classAttributes[0]->newInstance();

        return __($attribute->value);
    }

    private function getName(string $enumCase): string
    {
        $ref = new ReflectionClassConstant($this->permission, $enumCase);
        $classAttributes = $ref->getAttributes(PermissionName::class);

        if ($classAttributes === []) {
            return $this->getDefaultName($enumCase);
        }

        $attribute = $classAttributes[0]->newInstance();

        return __($attribute->value);
    }

    private function getDefaultName(string $enumCase): string
    {
        return Str::of($enumCase)
            ->headline()
            ->append(' ')
            ->append($this->getGroup()->getName())
            ->toString();
    }
}
