<?php

declare(strict_types=1);

namespace SamedayCourier\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids `new ForbiddenClass()` inside a layer that must not depend on it.
 *
 * @implements Rule<New_>
 */
final class ForbiddenLayerInstantiationRule implements Rule
{
    /**
     * @var LayerBoundarySupport
     */
    private $layerBoundarySupport;

    /**
     * @var string
     */
    private $ruleIdentifier;

    public function __construct(
        LayerBoundarySupport $layerBoundarySupport,
        string $ruleIdentifier = 'samedaycourier.layerBoundary.instantiation'
    ) {
        $this->layerBoundarySupport = $layerBoundarySupport;
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
        if (!$this->layerBoundarySupport->appliesToScope($scope)) {
            return [];
        }

        if (!$node->class instanceof Node\Name) {
            return [];
        }

        $instantiatedClass = $scope->resolveName($node->class);
        if (!$this->layerBoundarySupport->isForbiddenClass($instantiatedClass)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Layer boundary violation: "%s" must not instantiate "%s". Classes under %s are forbidden in this layer.',
                $this->describeDeclaringScope($scope),
                $instantiatedClass,
                implode(', ', $this->layerBoundarySupport->getForbiddenNamespacePrefixes())
            ))
                ->identifier($this->ruleIdentifier)
                ->build(),
        ];
    }

    private function describeDeclaringScope(Scope $scope): string
    {
        if ($scope->isInClass()) {
            return $scope->getClassReflection()->getDisplayName();
        }

        $namespace = $scope->getNamespace();

        return null !== $namespace && '' !== $namespace
            ? $namespace
            : 'file scope';
    }
}
