<?php

declare(strict_types=1);

namespace SamedayCourier\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids static calls on classes that belong to a forbidden layer.
 *
 * @implements Rule<StaticCall>
 */
final class ForbiddenLayerStaticCallRule implements Rule
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
        string $ruleIdentifier = 'samedaycourier.layerBoundary.staticCall'
    ) {
        $this->layerBoundarySupport = $layerBoundarySupport;
        $this->ruleIdentifier = $ruleIdentifier;
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
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

        $calledClass = $scope->resolveName($node->class);
        if (!$this->layerBoundarySupport->isForbiddenClass($calledClass)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Layer boundary violation: "%s" must not call static methods on "%s". Classes under %s are forbidden in this layer.',
                $this->describeDeclaringScope($scope),
                $calledClass,
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
