<?php

use Composer\InstalledVersions;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$autoloadPaths = [
    dirname(__DIR__, 3) . '/autoload.php',
    dirname(__DIR__, 2) . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php'
];

foreach ($autoloadPaths as $autoloadPath) {
    if (file_exists($autoloadPath)) {
        require $autoloadPath;
        break;
    }
}

/**
 * @return Container
 * @throws Exception
 */
function getContainer(): Container
{
    $package = InstalledVersions::getRootPackage();
    $cachePath = dirname(__DIR__) . '/var/cache/container_' . $package['reference'] . '.php';
    if (!file_exists($cachePath)) {
        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(
                dirname(__DIR__) . '/config'
            )
        );
        $loader->load('services.yml');
        $containerBuilder->setParameter('root-dir', dirname(__DIR__));
        $containerBuilder->compile();
        file_put_contents($cachePath, (new PhpDumper($containerBuilder))->dump());
    }
    require_once $cachePath;
    return new ProjectServiceContainer();
}
