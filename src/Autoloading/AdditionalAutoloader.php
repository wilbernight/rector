<?php

declare (strict_types=1);
namespace Rector\Core\Autoloading;

use Rector\Core\Configuration\Option;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use RectorPrefix20210628\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210628\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210628\Symplify\SmartFileSystem\FileSystemGuard;
/**
 * Should it pass autoload files/directories to PHPStan analyzer?
 */
final class AdditionalAutoloader
{
    /**
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    private $fileSystemGuard;
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    public function __construct(\RectorPrefix20210628\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard, \RectorPrefix20210628\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator)
    {
        $this->fileSystemGuard = $fileSystemGuard;
        $this->parameterProvider = $parameterProvider;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
    }
    public function autoloadInput(\RectorPrefix20210628\Symfony\Component\Console\Input\InputInterface $input) : void
    {
        if (!$input->hasOption(\Rector\Core\Configuration\Option::AUTOLOAD_FILE)) {
            return;
        }
        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(\Rector\Core\Configuration\Option::AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }
        $this->fileSystemGuard->ensureFileExists($autoloadFile, 'Extra autoload');
        require_once $autoloadFile;
    }
    public function autoloadPaths() : void
    {
        $autoloadPaths = $this->parameterProvider->provideArrayParameter(\Rector\Core\Configuration\Option::AUTOLOAD_PATHS);
        $this->dynamicSourceLocatorDecorator->addPaths($autoloadPaths);
    }
}
