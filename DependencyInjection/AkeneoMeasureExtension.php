<?php

namespace Akeneo\Bundle\MeasureBundle\DependencyInjection;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * Load measure bundle configuration from any bundles
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AkeneoMeasureExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // retrieve each measure config from bundles
	    $configs = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/measure.yml')) { //has a measure.yml file
	            $configs[] = Yaml::parse(file_get_contents(realpath($file)));
            }
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // load service
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        // set measures config
        $container->setParameter('akeneo_measure.measures_config', $config);

        $container
            ->getDefinition(MeasureManager::class)
            ->addMethodCall('setMeasureConfig', [$config['measures_config']]);
    }
}
