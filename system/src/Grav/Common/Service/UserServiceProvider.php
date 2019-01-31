<?php

/**
 * @package    Grav\Common\Service
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\Service;

use Grav\Common\Config\Config;
use Grav\Common\User\Storage\UserFileStorage;
use Grav\Common\User\Storage\UserFolderStorage;
use Grav\Common\User\User;
use Grav\Common\User\UserCollection;
use Grav\Common\User\UserIndex;
use Grav\Framework\File\Formatter\YamlFormatter;
use Grav\Framework\Flex\FlexDirectory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['users'] = function (Container $container) {
            /** @var Config $config */
            $config = $container['config'];

            $options = [
                'data' => [
                    'object' => User::class,
                    'collection' => UserCollection::class,
                    'index' => UserIndex::class,
                    'storage' => $this->getStorage($config->get('system.accounts.storage', 'file'))
                ]
            ];

            $directory = new FlexDirectory('users', 'blueprints://user/account.yaml', $options);

            return $directory->getIndex();
        };
    }

    protected function getStorage($config)
    {
        if ($config === 'folder') {
            return [
                'class' => UserFolderStorage::class,
                'options' => [
                    'formatter' => ['class' => YamlFormatter::class],
                    'folder' => 'account://',
                    'pattern' => '{FOLDER}/{KEY:2}/{KEY}/user.yaml',
                    'indexed' => true
                ]
            ];
        }

        return [
            'class' => UserFileStorage::class,
            'options' => [
                'formatter' => ['class' => YamlFormatter::class],
                'folder' => 'account://',
                'pattern' => '{FOLDER}/{KEY}.yaml',
                'indexed' => true
            ]
        ];
    }
}
