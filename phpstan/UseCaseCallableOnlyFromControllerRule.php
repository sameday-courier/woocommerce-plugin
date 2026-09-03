<?php

declare(strict_types=1);

namespace SamedayCourier\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Enforces the architectural boundary that a use case may only be invoked
 * (through its entry-point method) from a controller.
 *
 * A "use case" is any object whose type is a subtype of the configured
 * use-case interface. A "controller" is any class that implements the
 * configured controller interface. Any other caller (services, factories,
 * domain code, plain functions, global scope, closures defined outside a
 * controller, ...) is reported.
 *
 * The rule is intentionally scoped to the use case's public entry-point method
 * (e.g. `execute`) so that a use case calling its own internal template methods
 * (such as `processAction` / `validateRequest` on `$this`) is not flagged.
 *
 * @implements Rule<MethodCall>
 */
final class UseCaseCallableOnlyFromControllerRule implements Rule
{
    /**
     * @var string
     */
    private $useCaseInterface;

    /**
     * @var string
     */
    private $controllerInterface;

    /**
     * @var string
     */
    private $entryPointMethod;

    /**
     * @var list<string>
     */
    private $controllerNamespaces;

    /**
     * @param list<string> $controllerNamespaces Fully-qualified namespace prefixes whose classes are
     *                                            also treated as controllers, in addition to any class
     *                                            implementing $controllerInterface.
     */
    public function __construct(
        string $useCaseInterface,
        string $controllerInterface,
        string $entryPointMethod = 'execute',
        array $controllerNamespaces = []
    ) {
        $this->useCaseInterface = $useCaseInterface;
        $this->controllerInterface = $controllerInterface;
        $this->entryPointMethod = $entryPointMethod;
        $this->controllerNamespaces = $controllerNamespaces;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            // Dynamic method name (`$useCase->$method()`); cannot reason statically.
            return [];
        }

        if (strtolower($node->name->toString()) !== strtolower($this->entryPointMethod)) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        if (!(new ObjectType($this->useCaseInterface))->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        if ($this->isCalledFromController($scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Use case "%s::%s()" may only be invoked from a controller (a class implementing %s), but it is called from %s.',
                $this->useCaseInterface,
                $this->entryPointMethod,
                $this->controllerInterface,
                $this->describeCallSite($scope)
            ))
                ->identifier('samedaycourier.useCaseCalledOutsideController')
                ->build(),
        ];
    }

    private function isCalledFromController(Scope $scope): bool
    {
        if (!$scope->isInClass()) {
            return false;
        }

        $classReflection = $scope->getClassReflection();

        if ($classReflection->getName() === $this->controllerInterface
            || $classReflection->implementsInterface($this->controllerInterface)
        ) {
            return true;
        }

        $className = $classReflection->getName();
        foreach ($this->controllerNamespaces as $namespace) {
            $prefix = rtrim($namespace, '\\') . '\\';
            if (strncmp($className, $prefix, strlen($prefix)) === 0) {
                return true;
            }
        }

        return false;
    }

    private function describeCallSite(Scope $scope): string
    {
        if ($scope->isInClass()) {
            return sprintf('"%s"', $scope->getClassReflection()->getDisplayName());
        }

        if ($scope->getFunctionName() !== null) {
            return sprintf('function "%s()"', $scope->getFunctionName());
        }

        return 'outside of any class';
    }
}
