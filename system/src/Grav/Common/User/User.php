<?php

/**
 * @package    Grav.Common.User
 *
 * @copyright  Copyright (C) 2015 - 2018 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\User;

use Grav\Common\Grav;
use Grav\Common\Utils;
use Grav\Framework\File\Formatter\JsonFormatter;
use Grav\Framework\File\Formatter\YamlFormatter;
use Grav\Framework\Flex\FlexObject;
use RocketTheme\Toolbox\File\FileInterface;

/**
 * Flex User
 *
 * Flex User is mostly compatible with the older User class, except on few key areas:
 *
 * - Constructor parameters have been changed. Old code creating a new user does not work.
 * - Serializer has been changed -- existing sessions will be killed.
 *
 * @package Grav\Common\User
 *
 * @property string $username
 * @property string $email
 * @property string $fullname
 * @property string $state
 * @property array $groups
 * @property array $access
 * @property bool $authenticated
 * @property bool $authorized
 */
class User extends FlexObject implements UserInterface
{
    /**
     * Load user account.
     *
     * Always creates user object. To check if user exists, use $this->exists().
     *
     * @param string $username
     *
     * @return User|FlexObject
     */
    public static function load($username)
    {
        $collection = static::getCollection();

        if ($username !== '') {
            $user = $collection[mb_strtolower($username)];
            if ($user) {
                return $user;
            }
        }

        $directory = $collection->getFlexDirectory();

        return $directory->createObject([
            'username' => $username,
            'state' => 'enabled'
        ]);
    }

    /**
     * Find a user by username, email, etc
     *
     * @param string $query the query to search for
     * @param array $fields the fields to search
     * @return User|FlexObject
     */
    public static function find($query, $fields = ['username', 'email'])
    {
        $collection = static::getCollection();

        foreach ($fields as $field) {
            if ($field === 'username') {
                $user = $collection[mb_strtolower($query)];
            } else {
                $user = $collection->find($query, $field);
            }
            if ($user) {
                return $user;
            }
        }

        return static::load('');
    }

    /**
     * Remove user account.
     *
     * @param string $username
     *
     * @return bool True if the action was performed
     */
    public static function remove($username)
    {
        $user = static::load($username);

        $exists = $user->exists();
        if ($exists) {
            $user->delete();
        }

        return $exists;
    }

    /*
    public function __construct(array $items = [], $blueprints = null)
    {
        $this->items = $items;
        $this->blueprints = $blueprints;
    }
    /*

    /**
     * Get value by using dot notation for nested arrays/objects.
     *
     * @example $value = $this->get('this.is.my.nested.variable');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $default    Default value (or null).
     * @param string  $separator  Separator, defaults to '.'
     * @return mixed  Value.
     */
    public function get($name, $default = null, $separator = null)
    {
        return $this->getNestedProperty($name, $default, $separator);
    }

    /**
     * Set value by using dot notation for nested arrays/objects.
     *
     * @example $data->set('this.is.my.nested.variable', $value);
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      New value.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function set($name, $value, $separator = null)
    {
        $this->setNestedProperty($name, $value, $separator);

        return $this;
    }

    /**
     * Unset value by using dot notation for nested arrays/objects.
     *
     * @example $data->undef('this.is.my.nested.variable');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function undef($name, $separator = null)
    {
        $this->unsetNestedProperty($name, $separator);

        return $this;
    }

    /**
     * Set default value by using dot notation for nested arrays/objects.
     *
     * @example $data->def('this.is.my.nested.variable', 'default');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $default    Default value (or null).
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function def($name, $default = null, $separator = null)
    {
        $this->defNestedProperty($name, $default, $separator);

        return $this;
    }

    /**
     * Implements Countable interface.
     *
     * @return int
     * @todo remove?
     */
    public function count()
    {
        return \count($this->jsonSerialize());
    }

    /**
     * Convert object into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->jsonSerialize();
    }

    /**
     * Convert object into YAML string.
     *
     * @param  int $inline  The level where you switch to inline YAML.
     * @param  int $indent  The amount of spaces to use for indentation of nested nodes.
     *
     * @return string A YAML string representing the object.
     */
    public function toYaml($inline = 5, $indent = 2)
    {
        $yaml = new YamlFormatter(['inline' => $inline, 'indent' => $indent]);

        return $yaml->encode($this->toArray());
    }

    /**
     * Convert object into JSON string.
     *
     * @return string
     */
    public function toJson()
    {
        $json = new JsonFormatter();

        return $json->encode($this->toArray());
    }

    /**
     * Join nested values together by using blueprints.
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      Value to be joined.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     * @throws \RuntimeException
     */
    public function join($name, $value, $separator = null)
    {
        $old = $this->get($name, null, $separator);
        if ($old !== null) {
            if (!\is_array($old)) {
                throw new \RuntimeException('Value ' . $old);
            }

            if (\is_object($value)) {
                $value = (array) $value;
            } elseif (!\is_array($value)) {
                throw new \RuntimeException('Value ' . $value);
            }

            $value = $this->getBlueprint()->mergeData($old, $value, $name, $separator);
        }

        $this->set($name, $value, $separator);

        return $this;
    }

    /**
     * Get nested structure containing default values defined in the blueprints.
     *
     * Fields without default value are ignored in the list.

     * @return array
     */
    public function getDefaults()
    {
        return $this->getBlueprint()->getDefaults();
    }

    /**
     * Set default values by using blueprints.
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      Value to be joined.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function joinDefaults($name, $value, $separator = null)
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        $old = $this->get($name, null, $separator);
        if ($old !== null) {
            $value = $this->getBlueprint()->mergeData($value, $old, $name, $separator);
        }

        $this->setNestedProperty($name, $value, $separator);

        return $this;
    }

    /**
     * Get value from the configuration and join it with given data.
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param array|object $value      Value to be joined.
     * @param string  $separator  Separator, defaults to '.'
     * @return array
     * @throws \RuntimeException
     */
    public function getJoined($name, $value, $separator = null)
    {
        if (\is_object($value)) {
            $value = (array) $value;
        } elseif (!\is_array($value)) {
            throw new \RuntimeException('Value ' . $value);
        }

        $old = $this->get($name, null, $separator);

        if ($old === null) {
            // No value set; no need to join data.
            return $value;
        }

        if (!\is_array($old)) {
            throw new \RuntimeException('Value ' . $old);
        }

        // Return joined data.
        return $this->getBlueprint()->mergeData($old, $value, $name, $separator);
    }


    /**
     * Merge two configurations together.
     *
     * @param array $data
     * @return $this
     */
    public function merge(array $data)
    {
        $this->setElements($this->getBlueprint()->mergeData($this->toArray(), $data));

        return $this;
    }

    /**
     * Set default values to the configuration if variables were not set.
     *
     * @param array $data
     * @return $this
     */
    public function setDefaults(array $data)
    {
        $this->setElements($this->getBlueprint()->mergeData($data, $this->toArray()));

        return $this;
    }

    /**
     * Validate by blueprints.
     *
     * @return $this
     * @throws \Exception
     */
    public function validate()
    {
        $this->getBlueprint()->validate($this->toArray());

        return $this;
    }

    /**
     * @return $this
     * Filter all items by using blueprints.
     */
    public function filter()
    {
        $this->setElements($this->getBlueprint()->filter($this->toArray()));

        return $this;
    }

    /**
     * Get extra items which haven't been defined in blueprints.
     *
     * @return array
     */
    public function extra()
    {
        return $this->getBlueprint()->extra($this->toArray());
    }

    /**
     * Return unmodified data as raw string.
     *
     * NOTE: This function only returns data which has been saved to the storage.
     *
     * @return string
     */
    public function raw()
    {
        $file = $this->file();

        return $file ? $file->raw() : '';
    }

    /**
     * Set or get the data storage.
     *
     * @param FileInterface $storage Optionally enter a new storage.
     * @return FileInterface
     */
    public function file(FileInterface $storage = null)
    {
        if ($storage) {
            $this->storage = $storage;
        }

        return $this->storage;
    }

    /**
     * Authenticate user.
     *
     * If user password needs to be updated, new information will be saved.
     *
     * @param string $password Plaintext password.
     *
     * @return bool
     */
    public function authenticate($password)
    {
        // Always execute verify to protect us from timing attacks
        $hash = $this->getProperty('hashed_password') ?? Grav::instance()['config']->get('system.security.default_hash');
        $result = Authentication::verify($password, $hash);

        $plaintext_password = $this->getProperty('password');
        if (null !== $plaintext_password) {
            // Plain-text password is still stored, check if it matches
            if ($password !== $plaintext_password) {
                return false;
            }

            // Force hash update to get rid of plaintext password
            $result = 2;
        }

        if ($result === 2) {
            // Password needs to be updated, save the user
            $this->setProperty('password', $password);
            $this->save();
        }

        return (bool)$result;
    }

    /**
     * Save user without the username
     */
    public function save()
    {
        $password = $this->getProperty('password');
        if (null !== $password) {
            $this->unsetProperty('password');
            $this->unsetProperty('password1');
            $this->unsetProperty('password2');
            $this->setProperty('hashed_password', Authentication::create($password));
        }

        return parent::save();
    }

    /**
     * Checks user authorization to the action.
     *
     * @param  string $action
     * @param  string $scope
     * @return bool
     */
    public function authorize(string $action, ?string $scope = null) : bool
    {
        if (!$this->getProperty('authenticated')) {
            return false;
        }

        if ($this->getProperty('state') !== 'enabled') {
            return false;
        }

        if (null !== $scope) {
            $action = $scope . '.' . $action;
        }

        $authorized = false;

        //Check group access level
        $groups = (array)$this->getProperty('groups');
        foreach ($groups as $group) {
            $permission = Grav::instance()['config']->get("groups.{$group}.access.{$action}");
            $authorized = Utils::isPositive($permission);
            if ($authorized === true) {
                break;
            }
        }

        //Check user access level
        $check = "access.{$action}";
        $permission = $this->getProperty($check) ?? $this->getNestedProperty($check);
        if (null !== $permission) {
            $authorized = Utils::isPositive($permission);
        }

        return $authorized;
    }

    /**
     * Checks user authorization to the action.
     * Ensures backwards compatibility
     *
     * @param  string $action
     *
     * @deprecated use authorize()
     * @return bool
     */
    public function authorise($action)
    {
        user_error(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated since Grav 1.5, use authorize() method instead', E_USER_DEPRECATED);

        return $this->authorize($action);
    }

    /**
     * Return the User's avatar URL
     *
     * @return string
     */
    public function avatarUrl()
    {
        $avatar = $this->getProperty('avatar');
        if (\is_array($avatar)) {
            $avatar = array_shift($avatar);
            return Grav::instance()['base_url'] . '/' . $avatar['path'];
        }

        $provider = $this->getProperty('provider');
        if (\is_array($provider)) {
            if (isset($provider['avatar_url'])) {
                return $provider['avatar_url'];
            }
            if (isset($provider['avatar'])) {
                return $provider['avatar'];
            }
        }

        return 'https://www.gravatar.com/avatar/' . md5($this->getProperty('email'));
    }

    /**
     * Unserialize user.
     *
     * Implements backwards compatibility with the old User class.
     */
    public function __wakeup()
    {
        $key = mb_strtolower($this->username);

        $serialized = [
            'type' => 'users',
            'key' => $key,
            'elements' => $this->items,
            'storage' => [
                'key' => $key,
                'storage_key' => $key,
                'timestamp' => 0
            ]
        ];

        $this->doUnserialize($serialized);
    }

    /**
     * @return UserCollection
     */
    protected static function getCollection()
    {
        return Grav::instance()['users'];
    }

    /**
     * @return array
     */
    protected function doSerialize()
    {
        return [
            'type' => 'users',
            'key' => $this->getKey(),
            'elements' => $this->jsonSerialize(),
            'storage' => $this->getStorage()
        ];
    }

    /**
     * @param array $serialized
     */
    protected function doUnserialize(array $serialized)
    {
        $grav = Grav::instance();

        /** @var UserCollection $users */
        $users = $grav['users'];

        $directory = $users->getFlexDirectory();
        if (!$directory) {
            throw new \InvalidArgumentException('Internal error');
        }

        $this->setFlexDirectory($directory);
        $this->setStorage($serialized['storage']);
        $this->setKey($serialized['key']);
        $this->setElements($serialized['elements']);
    }
}
