<?php

declare(strict_types=1);

namespace Tomchochola\ComposerNormalize;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use RuntimeException;

class ComposerNormalizeInstaller implements PluginInterface, EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'post-install-cmd' => 'install',
            'post-update-cmd' => 'update',
        ];
    }

    /**
     * Install normalizer.
     */
    public static function install(Event $event): void
    {
        $binDir = static::binDir($event);

        static::download($binDir, false);
    }

    /**
     * Update normalizer.
     */
    public static function update(Event $event): void
    {
        $binDir = static::binDir($event);

        static::download($binDir, true);
    }

    /**
     * Download to given bin dir.
     */
    public static function download(string $binDir, bool $force): void
    {
        static::ensureVendorBinDirectoryExists($binDir);

        $localPath = \implode(\DIRECTORY_SEPARATOR, [$binDir, 'composer-normalize']);

        if (\file_exists($localPath) && $force === false) {
            return;
        }

        $binary = \file_get_contents('https://github.com/ergebnis/composer-normalize/releases/latest/download/composer-normalize.phar');

        if ($binary === false) {
            throw new RuntimeException('Could not download binary.');
        }

        $ok = \file_put_contents($localPath, $binary);

        if ($ok === false) {
            throw new RuntimeException('Could not write binary.');
        }

        $ok = \chmod($localPath, 0o755);

        if ($ok === false) {
            throw new RuntimeException('Could not make binary executable.');
        }
    }

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * @inheritDoc
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Resolve vendor/bin dir.
     */
    protected static function binDir(Event $event): string
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        if (\is_string($binDir)) {
            return $binDir;
        }

        return '';
    }

    /**
     * Make vendor/bin directory.
     */
    protected static function ensureVendorBinDirectoryExists(string $binDir): void
    {
        if (! \file_exists($binDir)) {
            $ok = \mkdir($binDir, 0o755);

            if ($ok === false) {
                throw new RuntimeException('Could not create vendor/bin directory.');
            }
        }

        if (! \is_dir($binDir)) {
            throw new RuntimeException('Could not find vendor/bin directory.');
        }

        if (! \is_writable($binDir)) {
            throw new RuntimeException('Could not write to vendor/bin directory.');
        }
    }
}
