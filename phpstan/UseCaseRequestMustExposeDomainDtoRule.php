<?php

declare(strict_types=1);

namespace SamedayCourier\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * Request objects consumed by use cases must expose domain DTOs, not raw input
 * arrays/mixed values for address or locker payloads.
 *
 * Configure per request class so legacy requests can be migrated incrementally.
 *
 * @implements Rule<MethodCall>
 */
final class UseCaseRequestMustExposeDomainDtoRule implements Rule
{
    private const USE_CASE_NAMESPACE_PREFIX = 'SamedayCourier\\Shipping\\Application\\UseCases\\';

    /**
     * @var array<string, array<string, list<string>>>
     */
    private $forbiddenRawGetterReturnTypesByRequestClass;

    /**
     * @var string
     */
    private $requestInterface;

    /**
     * @var string
     */
    private $ruleIdentifier;

    /**
     * @param array<string, array<string, list<string>>> $forbiddenRawGetterReturnTypesByRequestClass
     */
    public function __construct(
        array $forbiddenRawGetterReturnTypesByRequestClass,
        string $requestInterface = 'SamedayCourier\\Shipping\\Application\\Common\\Interfaces\\RequestInterface',
        string $ruleIdentifier = 'samedaycourier.useCaseRequestRawGetter'
    ) {
        $this->forbiddenRawGetterReturnTypesByRequestClass = $forbiddenRawGetterReturnTypesByRequestClass;
        $this->requestInterface = $requestInterface;
        $this->ruleIdentifier = $ruleIdentifier;
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
        if (!$this->isUseCaseScope($scope)) {
            return [];
        }

        if (!$node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->toString();
        $callerType = $scope->getType($node->var);
        if (!(new ObjectType($this->requestInterface))->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        $requestClass = $this->resolveSingleClassName($callerType->getObjectClassNames());
        if (null === $requestClass || !isset($this->forbiddenRawGetterReturnTypesByRequestClass[$requestClass])) {
            return [];
        }

        $forbiddenReturnTypes = $this->forbiddenRawGetterReturnTypesByRequestClass[$requestClass];
        if (!isset($forbiddenReturnTypes[$methodName])) {
            return [];
        }

        $methodReflection = $callerType->getMethod($methodName, $scope);
        if (null === $methodReflection) {
            return [];
        }

        $returnType = $methodReflection->getVariants()[0]->getReturnType();
        foreach ($forbiddenReturnTypes[$methodName] as $forbiddenType) {
            if (!$this->returnTypeMatchesForbiddenRawType($returnType, $forbiddenType)) {
                continue;
            }

            return [
                RuleErrorBuilder::message(sprintf(
                    'Use case boundary violation: request getter "%s::%s()" returns raw "%s". Map input in Infrastructure and expose domain DTO types from the request object.',
                    $requestClass,
                    $methodName,
                    $forbiddenType
                ))
                    ->identifier($this->ruleIdentifier)
                    ->build(),
            ];
        }

        return [];
    }

    private function isUseCaseScope(Scope $scope): bool
    {
        if (!$scope->isInClass()) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (null === $classReflection) {
            return false;
        }

        $className = $classReflection->getName();

        return strncmp($className, self::USE_CASE_NAMESPACE_PREFIX, strlen(self::USE_CASE_NAMESPACE_PREFIX)) === 0;
    }

    private function returnTypeMatchesForbiddenRawType(Type $returnType, string $forbiddenType): bool
    {
        if ('array' === $forbiddenType) {
            return $this->typeContains($returnType, static function (Type $type): bool {
                return $type instanceof ArrayType;
            });
        }

        if ('mixed' === $forbiddenType) {
            return $this->typeContains($returnType, static function (Type $type): bool {
                return $type instanceof MixedType;
            });
        }

        $normalizedForbiddenClass = ltrim($forbiddenType, '\\');

        return $this->typeContains($returnType, static function (Type $type) use ($normalizedForbiddenClass): bool {
            return $type instanceof ObjectType && $type->getClassName() === $normalizedForbiddenClass;
        });
    }

    /**
     * @param callable(Type): bool $matcher
     */
    private function typeContains(Type $type, callable $matcher): bool
    {
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $innerType) {
                if ($this->typeContains($innerType, $matcher)) {
                    return true;
                }
            }

            return false;
        }

        return $matcher($type);
    }

    /**
     * @param list<string> $classNames
     */
    private function resolveSingleClassName(array $classNames): ?string
    {
        if ([] === $classNames) {
            return null;
        }

        return $classNames[0];
    }
}
