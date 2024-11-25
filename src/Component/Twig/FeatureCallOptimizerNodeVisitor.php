<?php declare(strict_types=1);

namespace Frosh\Performance\Component\Twig;

use Shopware\Core\Framework\Feature;
use Twig\Environment;
use Twig\Node\BlockNode;
use Twig\Node\Expression\Binary\AndBinary;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\Unary\NotUnary;
use Twig\Node\IfNode;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\NodeVisitor\NodeVisitorInterface;

class FeatureCallOptimizerNodeVisitor implements NodeVisitorInterface
{
    public function enterNode(Node $node, Environment $env): Node
    {
        if ($node instanceof IfNode) {
            $test = $node->getNode('tests');
            $firstParameter = $test->getNode('0');

            $negated = false;

            if ($firstParameter instanceof NotUnary) {
                $firstParameter = $firstParameter->getNode('node');
                $negated = true;
            }

            if ($firstParameter instanceof FunctionExpression && $firstParameter->getAttribute('name') === 'feature') {
                $featureName = $firstParameter->getNode('arguments')->getNode('0')->getAttribute('value');

                $then = $test->getNode('1');
                $else = $node->hasNode('else') ? $node->getNode('else') : new TextNode('', $node->getTemplateLine());

                if (Feature::isActive($featureName)) {
                    return $negated ? $else : $then;
                }

                return $negated ? $then : $else;
            }

            if ($firstParameter instanceof AndBinary) {
                $left = $this->resolveNode($firstParameter->getNode('left'));
                $right = $this->resolveNode($firstParameter->getNode('right'));

                if ($left !== null) {
                    // when left condition is true, remove and and just use right
                    if ($left) {
                        $test->setNode('0', $firstParameter->getNode('right'));
                    } else {
                        if ($node->hasNode('else')) {
                            return $node->getNode('else');
                        }

                        return new TextNode('', $node->getTemplateLine());
                    }
                }

                if ($right !== null) {
                    if ($right) {
                        $test->setNode('0', $firstParameter->getNode('left'));
                    } else {
                        if ($node->hasNode('else')) {
                            return $node->getNode('else');
                        }

                        return new TextNode('', $node->getTemplateLine());
                    }
                }
            }
        }

        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function resolveNode(Node $node): ?bool
    {
        // We cannot transform it
        if (!$node instanceof FunctionExpression) {
            return null;
        }

        if ($node->getAttribute('name') !== 'feature') {
            return null;
        }

        $featureName = $node->getNode('arguments')->getNode('0')->getAttribute('value');

        return Feature::isActive($featureName);
    }
}
