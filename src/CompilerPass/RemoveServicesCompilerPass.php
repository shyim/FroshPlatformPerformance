<?php declare(strict_types=1);

namespace Frosh\Performance\CompilerPass;

use Shopware\Core\Content\ImportExport\Command\ImportEntityCommand;
use Shopware\Core\DevOps\Docs\App\DocsAppEventCommand;
use Shopware\Core\DevOps\Docs\Script\ScriptReferenceGeneratorCommand;
use Shopware\Core\DevOps\System\Command\OpenApiValidationCommand;
use Shopware\Core\DevOps\System\Command\SyncComposerVersionCommand;
use Shopware\Core\Framework\Adapter\Command\S3FilesystemVisibilityCommand;
use Shopware\Core\Framework\Adapter\Twig\EntityTemplateLoader;
use Shopware\Core\Framework\App\Command\ActivateAppCommand;
use Shopware\Core\Framework\App\Command\CreateAppCommand;
use Shopware\Core\Framework\App\Command\DeactivateAppCommand;
use Shopware\Core\Framework\App\Command\InstallAppCommand;
use Shopware\Core\Framework\App\Command\RefreshAppCommand;
use Shopware\Core\Framework\App\Command\ValidateAppCommand;
use Shopware\Core\Framework\App\ScheduledTask\UpdateAppsHandler;
use Shopware\Core\Framework\App\ScheduledTask\UpdateAppsTask;
use Shopware\Core\Framework\Update\Api\UpdateController;
use Shopware\Core\Framework\Webhook\WebhookCacheClearer;
use Shopware\Core\Framework\Webhook\WebhookDispatcher;
use Shopware\Core\Service\AllServiceInstaller;
use Shopware\Core\Service\Api\ServiceController;
use Shopware\Core\Service\Command\Install;
use Shopware\Core\Service\MessageHandler\UpdateServiceHandler;
use Shopware\Core\Service\ScheduledTask\InstallServicesTask;
use Shopware\Core\Service\ScheduledTask\InstallServicesTaskHandler;
use Shopware\Core\Service\ServiceClientFactory;
use Shopware\Core\Service\ServiceLifecycle;
use Shopware\Core\Service\ServiceSourceResolver;
use Shopware\Core\Service\Subscriber\ExtensionCompatibilitiesResolvedSubscriber;
use Shopware\Core\Service\Subscriber\InstalledExtensionsListingLoadedSubscriber;
use Shopware\Core\Service\Subscriber\LicenseSyncSubscriber;
use Shopware\Core\Service\Subscriber\ServiceOutdatedSubscriber;
use Shopware\Core\Service\TemporaryDirectoryFactory;
use Shopware\Core\System\StateMachine\Command\WorkflowDumpCommand;
use Shopware\Storefront\Framework\Twig\TokenParser\IconTokenParser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveServicesCompilerPass implements CompilerPassInterface
{
    private const REMOVE_SERVICES = [
        // App
        EntityTemplateLoader::class,
        WebhookDispatcher::class,
        WebhookCacheClearer::class,
        UpdateController::class,
        InstallAppCommand::class,
        ActivateAppCommand::class,
        DeactivateAppCommand::class,
        RefreshAppCommand::class,
        ValidateAppCommand::class,
        CreateAppCommand::class,

        UpdateAppsTask::class,
        UpdateAppsHandler::class,

        // Useless commands
        ImportEntityCommand::class,
        OpenApiValidationCommand::class,
        S3FilesystemVisibilityCommand::class,
        SyncComposerVersionCommand::class,
        WorkflowDumpCommand::class,
        DocsAppEventCommand::class,
        ScriptReferenceGeneratorCommand::class,

        // Symfony Translation commands
        'console.command.translation_debug',
        'console.command.translation_extract',
        'console.command.translation_pull',
        'console.command.translation_push',

        // Symfony Secrets commands
        'console.command.secrets_decrypt_to_local',
        'console.command.secrets_encrypt_from_local',
        'console.command.secrets_reveal',
        'console.command.secrets_list',
        'console.command.secrets_generate_key',
        'console.command.secrets_remove',
        'console.command.secrets_set',

        // Services
        Install::class,
        ServiceController::class,
        UpdateServiceHandler::class,
        InstallServicesTask::class,
        InstallServicesTaskHandler::class,
        ExtensionCompatibilitiesResolvedSubscriber::class,
        InstalledExtensionsListingLoadedSubscriber::class,
        LicenseSyncSubscriber::class,
        ServiceOutdatedSubscriber::class,
        AllServiceInstaller::class,
        ServiceClientFactory::class,
        ServiceLifecycle::class,
        ServiceSourceResolver::class,
        TemporaryDirectoryFactory::class,
    ];

    public function process(ContainerBuilder $container): void
    {
        foreach (self::REMOVE_SERVICES as $serviceId) {
            if ($container->has($serviceId)) {
                $container->removeDefinition($serviceId);
            }
        }

        $container->removeDefinition(IconTokenParser::class);
    }
}
