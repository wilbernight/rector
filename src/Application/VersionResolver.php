<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use DateTime;
use Rector\Core\Exception\VersionException;
use RectorPrefix20210629\Symfony\Component\Process\Process;
use RectorPrefix20210629\Symplify\PackageBuilder\Console\ShellCode;
/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php
 * See https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 */
final class VersionResolver
{
    /**
     * @var string
     */
    public const PACKAGE_VERSION = '2a394c750f518a33d56a0aa80fa2addf6ff1c140';
    /**
     * @var string
     */
    public const RELEASE_DATE = '2021-06-29 15:25:57';
    public static function resolvePackageVersion() : string
    {
        $process = new \RectorPrefix20210629\Symfony\Component\Process\Process(['git', 'log', '--pretty="%H"', '-n1', 'HEAD'], __DIR__);
        if ($process->run() !== \RectorPrefix20210629\Symplify\PackageBuilder\Console\ShellCode::SUCCESS) {
            throw new \Rector\Core\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $version = \trim($process->getOutput());
        return \trim($version, '"');
    }
    public static function resolverReleaseDateTime() : \DateTime
    {
        $process = new \RectorPrefix20210629\Symfony\Component\Process\Process(['git', 'log', '-n1', '--pretty=%ci', 'HEAD'], __DIR__);
        if ($process->run() !== \RectorPrefix20210629\Symplify\PackageBuilder\Console\ShellCode::SUCCESS) {
            throw new \Rector\Core\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        return new \DateTime(\trim($process->getOutput()));
    }
}
