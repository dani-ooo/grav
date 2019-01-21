<?php

/**
 * @package    Grav.Common.Service
 *
 * @copyright  Copyright (C) 2015 - 2018 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\Service;

use Grav\Common\User\User;
use Grav\Common\User\UserCollection;
use Grav\Common\User\UserIndex;
use Grav\Common\User\UserStorage;
use Grav\Framework\File\Formatter\YamlFormatter;
use Grav\Framework\Flex\FlexDirectory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['users'] = function () {
            $config = [
                'data' => [
                    'object' => User::class,
                    'collection' => UserCollection::class,
                    'index' => UserIndex::class,
                    'storage' => [
                        'class' => UserStorage::class,
                        'options' => [
                            'formatter' => ['class' => YamlFormatter::class],
                            'folder' => 'account://',
                            'pattern' => '{FOLDER}/{KEY:2}/{KEY}/user.yaml',
                            'indexed' => true
                        ]
                    ]
                ]
            ];
            $directory = new FlexDirectory('users', 'blueprints://user/account.yaml', $config);

            return $directory->getIndex();
        };
    }
}
