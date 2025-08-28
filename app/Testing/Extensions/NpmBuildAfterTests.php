<?php

namespace App\Testing\Extensions;

use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * PHPUnit 11 extension that triggers `npm run build` after the test suite finishes.
 *
 * This is intended for local developer convenience. It will automatically skip
 * when running in common CI environments (CI env var present) and will silently
 * ignore errors so it never fails the test suite.
 */
final class NpmBuildAfterTests implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements FinishedSubscriber
        {
            public function notify(Finished $event): void
            {
                // Skip in CI environments to avoid breaking pipelines
                if (self::isCi()) {
                    return;
                }

                // Only attempt if npm is available
                if (! self::hasNpm()) {
                    return;
                }

                // Run the build and swallow any errors.
                try {
                    $cmd = self::npmCommand('run build');

                    // Redirect stderr to stdout so developers can see output in one stream
                    $cmdWithRedirect = $cmd.' 2>&1';

                    // Use passthru to stream output live; capture exit status
                    $exitCode = 0;
                    passthru($cmdWithRedirect, $exitCode);
                    // Do not throw on non-zero; tests should not fail because the build failed.
                } catch (\Throwable $e) {
                    // Silently ignore any issues to ensure tests are not affected
                }
            }

            private static function isCi(): bool
            {
                // Many CI providers set CI=1/true. Also check GITHUB_ACTIONS, GITLAB_CI, etc.
                $ciVars = ['CI', 'GITHUB_ACTIONS', 'GITLAB_CI', 'AZURE_HTTP_USER_AGENT', 'BITBUCKET_BUILD_NUMBER'];
                foreach ($ciVars as $var) {
                    $val = getenv($var);
                    if ($val !== false && $val !== '') {
                        return true;
                    }
                }

                return false;
            }

            private static function hasNpm(): bool
            {
                // Basic availability check
                $which = stripos(PHP_OS_FAMILY, 'Windows') === false ? 'command -v npm' : 'where npm';
                $output = null;
                $exitCode = 0;
                @exec($which, $output, $exitCode);

                return $exitCode === 0;
            }

            private static function npmCommand(string $args): string
            {
                // On Windows, npm is typically available as npm.cmd
                if (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
                    return 'npm.cmd '.$args;
                }

                // On Unix-like systems
                return 'npm '.$args;
            }
        });
    }
}
