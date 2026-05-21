<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use App\Managing\Service\Admin\ManageCrudResourcePolicy;

final class ManageHostClassNameResolver
{
    private ManageClassNameFormatter $formatter;
    private ManagePhpClassNameExtractor $classNameExtractor;

    public function __construct(
        private readonly ?ManageCrudResourcePolicy $resourcePolicy = null,
        ?ManageClassNameFormatter $formatter = null,
        ?ManagePhpClassNameExtractor $classNameExtractor = null,
    ) {
        $this->formatter = $formatter ?? new ManageClassNameFormatter();
        $this->classNameExtractor = $classNameExtractor ?? new ManagePhpClassNameExtractor();
    }

    public function classNameFromFile(\SplFileInfo $file): ?string
    {
        return $this->classNameExtractor->classNameFromFile($file);
    }

    public function componentKeyFromClass(string $className): string
    {
        $parts = explode('\\', $className);

        if (isset($parts[1]) && !in_array($parts[1], ['Entity', 'Controller'], true)) {
            return $this->formatter->slug($parts[1]);
        }

        if (isset($parts[2])) {
            return $this->resourcePolicy?->componentKeyFromRootName($parts[2]) ?? $this->formatter->slug($parts[2]);
        }

        return 'app';
    }

    public function resourceKeyFromClass(string $className): string
    {
        return $this->formatter->slug($this->shortClassName($className));
    }

    public function resourcePathSegmentFromClass(string $className): string
    {
        $shortName = $this->shortClassName($className);
        $shortName = preg_replace('/Entity$/', '', $shortName) ?? $shortName;

        return $this->formatter->slug($shortName);
    }

    public function componentLabel(string $componentKey): string
    {
        if ('app' === $componentKey) {
            return 'App';
        }

        return $this->formatter->humanize($componentKey);
    }

    public function shortClassName(string $className): string
    {
        return $this->formatter->shortClassName($className);
    }

    public function studly(string $value): string
    {
        return $this->formatter->studly($value);
    }

    public function slug(string $value): string
    {
        return $this->formatter->slug($value);
    }

    public function humanize(string $value): string
    {
        return $this->formatter->humanize($value);
    }
}
