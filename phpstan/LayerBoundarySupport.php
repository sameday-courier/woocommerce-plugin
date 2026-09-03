<?php

declare(strict_types=1);

namespace SamedayCourier\PHPStan;

use PHPStan\Analyser\Scope;

final class LayerBoundarySupport
{
    /**
     * @var string
     */
    private $sourceLayerNamespacePrefix;

    /**
     * @var list<string>
     */
    private $forbiddenNamespacePrefixes;

    /**
     * @param list<string> $forbiddenNamespacePrefixes
     */
    public function __construct(string $sourceLayerNamespacePrefix, array $forbiddenNamespacePrefixes)
    {
        $this->sourceLayerNamespacePrefix = rtrim($sourceLayerNamespacePrefix, '\\') . '\\';
        $this->forbiddenNamespacePrefixes = $forbiddenNamespacePrefixes;
    }

    public function appliesToScope(Scope $scope): bool
    {
        $namespace = $this->resolveDeclaringNamespace($scope);

        return null !== $namespace && $this->startsWith($namespace, $this->sourceLayerNamespacePrefix);
    }

    public function isForbiddenClass(string $className): bool
    {
        foreach ($this->forbiddenNamespacePrefixes as $forbiddenPrefix) {
            $normalizedPrefix = rtrim($forbiddenPrefix, '\\') . '\\';
            if ($this->startsWith($className, $normalizedPrefix)) {
                return true;
            }
        }

        return false;
    }

    public function getSourceLayerNamespacePrefix(): string
    {
        return $this->sourceLayerNamespacePrefix;
    }

    /**
     * @return list<string>
     */
    public function getForbiddenNamespacePrefixes(): array
    {
        return $this->forbiddenNamespacePrefixes;
    }

    private function resolveDeclaringNamespace(Scope $scope): ?string
    {
        $namespace = $scope->getNamespace();
        if (null !== $namespace && '' !== $namespace) {
            return $namespace;
        }

        if (!$scope->isInClass()) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (null === $classReflection) {
            return null;
        }

        return $this->extractNamespaceFromClassName($classReflection->getName());
    }

    private function extractNamespaceFromClassName(string $className): ?string
    {
        $lastSeparator = strrpos($className, '\\');
        if (false === $lastSeparator) {
            return null;
        }

        $namespace = substr($className, 0, $lastSeparator);

        return '' !== $namespace ? $namespace : null;
    }

    private function startsWith(string $haystack, string $prefix): bool
    {
        if ('' === $prefix) {
            return true;
        }

        return strncmp($haystack, $prefix, strlen($prefix)) === 0;
    }
}
