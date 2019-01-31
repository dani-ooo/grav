<?php

/**
 * @package    Grav.Common.User
 *
 * @copyright  Copyright (C) 2015 - 2018 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\User;

use Grav\Common\Debugger;
use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;
use Grav\Framework\Flex\FlexIndex;
use Grav\Framework\Flex\Interfaces\FlexStorageInterface;
use Monolog\Logger;

class UserIndex extends FlexIndex
{
    /**
     * @param FlexStorageInterface $storage
     * @return array
     */
    public static function loadEntriesFromStorage(FlexStorageInterface $storage) : array
    {
        $index = parent::loadEntriesFromStorage($storage);

        $grav = Grav::instance();
        $locator = $grav['locator'];
        $filename = $locator->findResource('user-data://accounts/index.yaml', true, true);
        $indexFile = CompiledYamlFile::instance($filename);

        try {
            $data = (array)$indexFile->content();
        } catch (\Exception $e) {
            /** @var Logger $logger */
            $logger = $grav['log'];
            $logger->addAlert(sprintf('Reading FlexUser index failed: %s', $e->getMessage()));

            /** @var Debugger $debugger */
            $debugger = $grav['debugger'];
            $debugger->addException($e);

            $data = [];
        }

        $entries = $data['index'] ?? [];
        foreach ($entries as $key => $row) {
            $storage_key = $row['storage_key'];
            if (!isset($index[$storage_key])) {
                // Entry has been removed from storage.
                unset($entries[$storage_key]);
            } elseif ($index[$storage_key]['storage_timestamp'] === $row['storage_timestamp']) {
                // Entry is up to date, no update needed.
                unset($index[$storage_key]);
            }
        }

        if ($index) {
            $indexFile->lock();

            $keys = array_fill_keys(array_keys($index), null);
            $rows = $storage->readRows($keys);

            foreach ($rows as $key => $row) {
                $entries[$key] = array_merge(
                    $index[$key],
                    [
                        'key' => $row['username'] ?? $key,
                        'email' => $row['email'] ?? '',
                    ]
                );
            }

            ksort($entries, SORT_NATURAL);

            $indexFile->save(['index' => $entries]);
        }

        return $entries;
    }
}
