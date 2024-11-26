<?php declare(strict_types=1);

namespace Frosh\Performance\Component\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function getNodeVisitors(): array
    {
        return [
            new FeatureCallOptimizerNodeVisitor(),
//            new WhitespaceNodeVisitor(),
        ];
    }

    public function getTokenParsers()
    {
        return [
            new IconTokenParser($this->twig),
        ];
    }
}
