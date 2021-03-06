<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\CLI;

use Doctrine\DBAL\Connection;
use GeoIp2\Database\Reader;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Shlinkio\Shlink\CLI\Util\GeolocationDbUpdater;
use Shlinkio\Shlink\Common\Doctrine\NoDbNameConnectionFactory;
use Shlinkio\Shlink\Core\Service;
use Shlinkio\Shlink\Core\Visit;
use Shlinkio\Shlink\Installer\Factory\ProcessHelperFactory;
use Shlinkio\Shlink\IpGeolocation\GeoLite2\DbUpdater;
use Shlinkio\Shlink\IpGeolocation\Resolver\IpLocationResolverInterface;
use Shlinkio\Shlink\Rest\Service\ApiKeyService;
use Symfony\Component\Console as SymfonyCli;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Process\PhpExecutableFinder;

use const Shlinkio\Shlink\Core\LOCAL_LOCK_FACTORY;

return [

    'dependencies' => [
        'factories' => [
            SymfonyCli\Application::class => Factory\ApplicationFactory::class,
            SymfonyCli\Helper\ProcessHelper::class => ProcessHelperFactory::class,
            PhpExecutableFinder::class => InvokableFactory::class,

            GeolocationDbUpdater::class => ConfigAbstractFactory::class,

            Command\ShortUrl\GenerateShortUrlCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\ResolveUrlCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\ListShortUrlsCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\GetVisitsCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\DeleteShortUrlCommand::class => ConfigAbstractFactory::class,

            Command\Visit\LocateVisitsCommand::class => ConfigAbstractFactory::class,

            Command\Api\GenerateKeyCommand::class => ConfigAbstractFactory::class,
            Command\Api\DisableKeyCommand::class => ConfigAbstractFactory::class,
            Command\Api\ListKeysCommand::class => ConfigAbstractFactory::class,

            Command\Tag\ListTagsCommand::class => ConfigAbstractFactory::class,
            Command\Tag\CreateTagCommand::class => ConfigAbstractFactory::class,
            Command\Tag\RenameTagCommand::class => ConfigAbstractFactory::class,
            Command\Tag\DeleteTagsCommand::class => ConfigAbstractFactory::class,

            Command\Db\CreateDatabaseCommand::class => ConfigAbstractFactory::class,
            Command\Db\MigrateDatabaseCommand::class => ConfigAbstractFactory::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        GeolocationDbUpdater::class => [DbUpdater::class, Reader::class, LOCAL_LOCK_FACTORY],

        Command\ShortUrl\GenerateShortUrlCommand::class => [
            Service\UrlShortener::class,
            'config.url_shortener.domain',
            'config.url_shortener.default_short_codes_length',
        ],
        Command\ShortUrl\ResolveUrlCommand::class => [Service\ShortUrl\ShortUrlResolver::class],
        Command\ShortUrl\ListShortUrlsCommand::class => [Service\ShortUrlService::class, 'config.url_shortener.domain'],
        Command\ShortUrl\GetVisitsCommand::class => [Service\VisitsTracker::class],
        Command\ShortUrl\DeleteShortUrlCommand::class => [Service\ShortUrl\DeleteShortUrlService::class],

        Command\Visit\LocateVisitsCommand::class => [
            Visit\VisitLocator::class,
            IpLocationResolverInterface::class,
            LockFactory::class,
            GeolocationDbUpdater::class,
        ],

        Command\Api\GenerateKeyCommand::class => [ApiKeyService::class],
        Command\Api\DisableKeyCommand::class => [ApiKeyService::class],
        Command\Api\ListKeysCommand::class => [ApiKeyService::class],

        Command\Tag\ListTagsCommand::class => [Service\Tag\TagService::class],
        Command\Tag\CreateTagCommand::class => [Service\Tag\TagService::class],
        Command\Tag\RenameTagCommand::class => [Service\Tag\TagService::class],
        Command\Tag\DeleteTagsCommand::class => [Service\Tag\TagService::class],

        Command\Db\CreateDatabaseCommand::class => [
            LockFactory::class,
            SymfonyCli\Helper\ProcessHelper::class,
            PhpExecutableFinder::class,
            Connection::class,
            NoDbNameConnectionFactory::SERVICE_NAME,
        ],
        Command\Db\MigrateDatabaseCommand::class => [
            LockFactory::class,
            SymfonyCli\Helper\ProcessHelper::class,
            PhpExecutableFinder::class,
        ],
    ],

];
