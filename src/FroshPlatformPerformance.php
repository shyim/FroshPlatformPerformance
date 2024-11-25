<?php declare(strict_types=1);

namespace Frosh\Performance;

use Frosh\Performance\CompilerPass\RemoveServicesCompilerPass;
use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FroshPlatformPerformance extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RemoveServicesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 500000);

        // Buy more RAM, it's more worth
        $container->setParameter('shopware.cache.cache_compression', false);
        $container->setParameter('shopware.cache.tagging.each_config', false);
        $container->setParameter('shopware.cache.tagging.each_snippet', false);
        $container->setParameter('shopware.cache.tagging.each_theme_config', false);

        $container->setParameter('shopware.auto_update.enabled', false);
        $container->setParameter('shopware.html_sanitizer.enabled', false);
        $container->setParameter('shopware.mail.update_mail_variables_on_send', false);
        $container->setParameter('shopware.cache.invalidation.http_cache', []);
    }
}
