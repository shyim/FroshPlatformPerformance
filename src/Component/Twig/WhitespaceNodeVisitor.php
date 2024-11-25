<?php declare(strict_types=1);

namespace Frosh\Performance\Component\Twig;

use Twig\Environment;
use Twig\Node\BlockNode;
use Twig\Node\IfNode;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\NodeVisitor\NodeVisitorInterface;

class WhitespaceNodeVisitor implements NodeVisitorInterface
{
    public function enterNode(Node $node, Environment $env): Node
    {
        if ($node instanceof BlockNode) {
            $inner = $node->getNode('body');

            if ($inner instanceof TextNode) {
                $text = \trim($inner->getAttribute('data'));
                if ($text === '') {
                    return new BlockNode($node->getAttribute('name'), new Node(), $node->getTemplateLine());
                }
                $this->filterTextNode($inner);
            } elseif ($inner::class === Node::class) {
                return new BlockNode($node->getAttribute('name'), $this->filterNode($inner), $node->getTemplateLine());
            }
        }

        if ($node instanceof IfNode) {
            $test = $node->getNode('tests');

            if ($test->hasNode('1')) {
                $then = $test->getNode('1');

                if ($then::class === Node::class) {
                    $test->setNode('1', $this->filterNode($then));
                }
            }

            if ($test->hasNode('else')) {
                $else = $test->getNode('else');

                if ($else::class === Node::class) {
                    $test->setNode('else', $this->filterNode($else));
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

    private function filterTextNode(TextNode $node): TextNode
    {
        $text = $node->getAttribute('data');

        if (
            str_contains($text, '<div')
            || str_contains($text, '<span')
            || str_contains($text, '<p')
            || str_contains($text, '<br')
            || str_contains($text, '<hr')
            || str_contains($text, '<h1')
            || str_contains($text, '<h2')
            || str_contains($text, '<h3')
            || str_contains($text, '<h4')
            || str_contains($text, '<li')
            || str_contains($text, 'div>')
            || str_contains($text, 'span>')
            || str_contains($text, 'p>')
            || str_contains($text, 'h1>')
            || str_contains($text, 'h2>')
            || str_contains($text, 'h3>')
            || str_contains($text, 'h4>')
            || str_contains($text, 'li>')
        ) {
            $node->setAttribute('data', \trim($text));
        }

        return $node;
    }

    private function filterNode(Node $node): Node
    {
        $newNodes = [];
        foreach ($node as $child) {
            if ($child instanceof TextNode) {
                $text = \trim($child->getAttribute('data'));

                if ($text !== '') {
                    $newNodes[] = $this->filterTextNode($child);
                }
            } else {
                $newNodes[] = $child;
            }
        }

        return new Node($newNodes);
    }
}
