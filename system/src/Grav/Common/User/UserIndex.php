<?php

/**
 * @package    Grav.Common.User
 *
 * @copyright  Copyright (C) 2015 - 2018 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\User;

use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;
use Grav\Framework\Flex\FlexIndex;
use Grav\Framework\Flex\Interfaces\FlexStorageInterface;

class UserIndex extends FlexIndex
{
    /**
     * @param FlexStorageInterface $storage
     * @return array
     */
    public static function loadEntriesFromStorage(FlexStorageInterface $storage) : array
    {
        $index = parent::loadEntriesFromStorage($storage);

        $locator = Grav::instance()['locator'];
        $filename = $locator->findResource('user-data://accounts/index.yaml', true, true);
        $indexFile = CompiledYamlFile::instance($filename);

        $data = (array)$indexFile->content();

        $entries = $data['index'] ?? [];
        foreach ($entries as $key => $row) {
            if (!isset($row['key'])) {
                // Index format updated: the whole index needs an update.
                $entries = [];
                break;
            }

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
