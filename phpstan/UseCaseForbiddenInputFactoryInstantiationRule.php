<?php

declare(strict_types=1);

namespace SamedayCourier\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Use cases must receive already-mapped domain DTOs. Input factories that convert
 * raw HTTP/order payloads belong in Infrastructure, not inside a use case.
 *
 * @implements Rule<New_>
 */
final class UseCaseForbiddenInputFactoryInstantiationRule implements Rule
{
    private const USE_CASE_NAMESPACE_PREFIX = 'SamedayCourier\\Shipping\\Application\\UseCases\\';

    private const INPUT_FACTORY_NAMESPACE_PREFIX = 'SamedayCourier\\Shipping\\Application\\Common\\Factories\\';

    /**
     * @var string
     */
    private $ruleIdentifier;

    public function __construct(string $ruleIdentifier = 'samedaycourier.useCaseInputFactoryInstantiation')
    {
        $this->ruleIdentifier = $ruleIdentifier;
    }

    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $node
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isUseCaseScope($scope)) {
            return [];
        }

        if (!$node->class instanceof Node\Name) {
            return [];
        }

        $instantiatedClass = $scope->resolveName($node->class);
        if (!$this->isInputFactoryClass($instantiatedClass)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Use case boundary violation: "%s" must not instantiate input factory "%s". Map raw input to domain DTOs in Infrastructure (controller/request factory) and pass typed request objects into the use case.',
                $this->describeDeclaringScope($scope),
                $instantiatedClass
            ))
                ->identifier($this->ruleIdentifier)
                ->build(),
        ];
    }

    private function isUseCaseScope(Scope $scope): bool
    {
        if (!$scope->isInClass()) {
            return false;
        }

        $className = $scope->getClassReflection()->getName();

        return strncmp($className, self::USE_CASE_NAMESPACE_PREFIX, strlen(self::USE_CASE_NAMESPACE_PREFIX)) === 0;
    }

    private function isInputFactoryClass(string $className): bool
    {
        if (strncmp($className, self::INPUT_FACTORY_NAMESPACE_PREFIX, strlen(self::INPUT_FACTORY_NAMESPACE_PREFIX)) !== 0) {
            return false;
        }

        return substr($className, -7) === 'Factory';
    }

    private function describeDeclaringScope(Scope $scope): string
    {
        return $scope->getClassReflection()->getDisplayName();
    }
}
