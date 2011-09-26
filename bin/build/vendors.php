#!/usr/bin/env php
<?php

/*
 * This file is part of the Symfony Standard Edition.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$rootDir = dirname(dirname(__DIR__));

array_shift($argv);
if (!isset($argv[0])) {
    exit(<<<EOF
Symfony2 vendors script management.

Specify a command to run:

 install: install vendors as specified in deps or deps.lock (recommended)
 update:  update vendors to their latest versions (as specified in deps)


EOF
    );
}

if (!in_array($command = array_shift($argv), array('install', 'update'))) {
    exit(sprintf("Command \"%s\" does not exist.\n", $command));
}

// versions
$versions = array();
if ('install' === $command && file_exists($rootDir . '/deps.lock')) {
    foreach (file($rootDir . '/deps.lock', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $parts = array_values(array_filter(explode(' ', $line)));
        if (2 !== count($parts)) {
            exit(sprintf('The deps version file is not valid (near "%s")', $line));
        }
        $versions[$parts[0]] = $parts[1];
    }
}

$newversions = array();
$deps = parse_ini_file($rootDir . '/deps', true, INI_SCANNER_RAW);
foreach ($deps as $name => $dep) {
    // install dir
    if (0 !== strpos($dep['target'], '/')) {
        $dep['target'] = '/' . $dep['target'];
    }
    $installDir = $rootDir . $dep['target'];
    if (in_array('--reinstall', $argv)) {
        system(sprintf('rm -rf %s', escapeshellarg($installDir)));
    }

    echo "> Installing/Updating $name\n";

    // url
    if (isset($dep['git'])) {
        $url = $dep['git'];

        // revision
        if (isset($versions[$name])) {
            $rev = $versions[$name];
        } else {
            $rev = isset($dep['version']) ? $dep['version'] : 'origin/HEAD';
        }

        if (!isset($dep['target'])) {
            exit(sprintf('The "target" value for the "%s" dependency must be set.', $name));
        }

        if (!is_dir($installDir)) {
            system(sprintf('git clone %s %s', escapeshellarg($url), escapeshellarg($installDir)));
        }

        system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));

        if ('update' === $command) {
            ob_start();
            system(sprintf('cd %s && git log -n 1 --format=%%H', escapeshellarg($installDir)));
            $newversions[] = trim($name . ' ' . ob_get_clean());
        }
    } else if (isset($dep['svn'])) {
        $rev = isset($dep['revision']) ? $dep['revision'] : 'HEAD';
        $url = $dep['svn'];
        system(sprintf('rm -rf %s', escapeshellarg($installDir)));
        system(sprintf('svn export -q -r %s %s %s', $rev, escapeshellarg($url), escapeshellarg($installDir)));
    } else {
        exit(sprintf('The "git" or "svn" value for the "%s" dependency must be set.', $name));
    }
}

// update?
if ('update' === $command) {
    file_put_contents($rootDir . '/deps.lock', implode("\n", $newversions));
}