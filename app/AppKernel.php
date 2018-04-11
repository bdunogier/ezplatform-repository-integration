<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    protected $varDir;

    public function __construct($environment, $debug, $varDir)
    {
        parent::__construct($environment, $debug);
        $this->varDir = $varDir;
    }

    public function registerBundles()
    {
        $bundles = [
            new eZ\Bundle\EzPlatformRepositoryBundle\EzPlatformRepositoryBundle(),
            new EzSystems\EzPlatformRichTextBundle\EzPlatformRichTextBundle(),
        ];

        if ($this->environment === 'legacy')
        {
            $bundles[] = new EzSystems\EzPlatformLegacyStorageEngineBundle\EzPlatformLegacyStorageEngineBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        if (!empty($_SERVER['SYMFONY_TMP_DIR'])) {
            return rtrim($_SERVER['SYMFONY_TMP_DIR'], '/') . '/var/cache/' . $this->getEnvironment();
        }

        // On platform.sh place each deployment cache in own folder to rather cleanup old cache async
        if ($this->getEnvironment() === 'prod' && ($platformTreeId = getenv('PLATFORM_TREE_ID'))) {
            return dirname(__DIR__) . '/var/cache/prod/' . $platformTreeId;
        }

        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        if (!empty($_SERVER['SYMFONY_TMP_DIR'])) {
            return rtrim($_SERVER['SYMFONY_TMP_DIR'], '/') . '/var/logs';
        }

        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    protected function getKernelParameters()
    {
        return array_merge(
            parent::getKernelParameters(),
            [
                'var_dir' => $this->varDir,
                'io_root_dir' => '%var_dir%/%storage_dir%'
            ]
        );
    }
}
