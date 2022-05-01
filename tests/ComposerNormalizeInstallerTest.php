<?php

declare(strict_types=1);

namespace Tomchochola\ComposerNormalize\Tests;

use Tomchochola\ComposerNormalize\ComposerNormalizeInstaller;

class ComposerNormalizeInstallerTest extends TestCase
{
    public function test_local(): void
    {
        $binDir = $GLOBALS['_composer_bin_dir'];

        $localPath = \implode(\DIRECTORY_SEPARATOR, [$binDir, 'composer-normalize']);

        if (\file_exists($localPath)) {
            \unlink($localPath);
        }

        static::assertFileDoesNotExist($localPath);

        ComposerNormalizeInstaller::download($binDir, true);

        static::assertFileIsReadable($localPath);
    }
}
