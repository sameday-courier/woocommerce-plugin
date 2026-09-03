<?php

declare(strict_types=1);

namespace SamedayCourier\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids `use` imports of classes that belong to an outer/forbidden layer.
 *
 * @implements Rule<Use_>
 */
final class ForbiddenLayerUseRule implements Rule
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
        string $ruleIdentifier = 'samedaycourier.layerBoundary.use'
    ) {
        $this->layerBoundarySupport = $layerBoundarySupport;
        $this->ruleIdentifier = $ruleIdentifier;
    }

    public function getNodeType(): string
    {
        return Use_::class;
    }

    /**
     * @param Use_ $node
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->layerBoundarySupport->appliesToScope($scope)) {
            return [];
        }

        $errors = [];

        foreach ($node->uses as $use) {
            $importedClass = $scope->resolveName($use->name);
            if (!$this->layerBoundarySupport->isForbiddenClass($importedClass)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Layer boundary violation: "%s" must not import "%s". Classes under %s are forbidden in this layer.',
                $this->describeDeclaringScope($scope),
                $importedClass,
                implode(', ', $this->layerBoundarySupport->getForbiddenNamespacePrefixes())
            ))
                ->identifier($this->ruleIdentifier)
                ->build();
        }

        return $errors;
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
