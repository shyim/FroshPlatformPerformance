<?php

namespace Frosh\Performance\Component\Twig;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\TextNode;
use Twig\Parser;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class IconTokenParser extends AbstractTokenParser
{
    /**
     * @var Parser
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $parser;

    public function __construct(private readonly Environment $twig)
    {
    }

    public function parse(Token $token): TextNode
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        $icon = $expr->getAttribute('value');

        $stream = $this->parser->getStream();

        $variables = [];

        if ($stream->nextIf(Token::NAME_TYPE, 'style')) {
            /** @var ArrayExpression $parsedExpression */
            $parsedExpression = $this->parser->getExpressionParser()->parseExpression();
            /** @var ConstantExpression $keyValuePair */
            foreach ($parsedExpression->getKeyValuePairs() as $keyValuePair) {
                if ($keyValuePair['key']->hasAttribute('value') === false || $keyValuePair['value']->hasAttribute('value') === false) {
                    continue;
                }

                $variables[$keyValuePair['key']->getAttribute('value')] = $keyValuePair['value']->getAttribute('value');
            }
        }

        $stream->next();

        $variables['name'] = $icon;

        if (!isset($variables['pack'])) {
            $variables['pack'] = 'default';
        }

        if (!isset($variables['namespace'])) {
            $variables['namespace'] = 'Storefront';
        }

        $cssClasses = ['icon', 'icon-' . $variables['pack'], 'icon-' . $variables['pack'] . '-' . $variables['name']];

        foreach (['size', 'color', 'rotation', 'flip', 'classes'] as $style) {
            if (isset($variables[$style])) {
                $cssClasses[] = 'icon-' . $variables[$style];
            }
        }

        $mergedClasses = implode(' ', $cssClasses);

        $attributes = '';

        if (isset($variables['ariaHidden']) && $variables['ariaHidden'] === true) {
            $attributes .= ' aria-hidden="true"';
        }

        try {
            $sourceContext = $this->twig->getLoader()->getSourceContext('@' . $variables['namespace'] . '/assets/icon/' . $variables['pack'] . '/' . $variables['name'] . '.svg');
        } catch (LoaderError) {
            return new TextNode("<span class=\"$mergedClasses\" $attributes></span>", $token->getLine());
        }

        $svg = $sourceContext->getCode();


        $html = "<span class=\"$mergedClasses\" $attributes>
$svg
        </span>";

        return new TextNode($html, $token->getLine());
    }

    public function getTag(): string
    {
        return 'sw_icon';
    }
}
