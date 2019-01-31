<?php

/**
 * @package    Grav\Common\User
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\User;

use Grav\Common\Data\Blueprint;
use Grav\Common\Data\DataInterface;
use RocketTheme\Toolbox\ArrayTraits\ExportInterface;

interface UserInterface extends DataInterface, \ArrayAccess, \Countable, \JsonSerializable, ExportInterface
{
    /**
     * @param array $items
     * @param Blueprint|callable $blueprints
     */
    //public function __construct(array $items = [], $blueprints = null);

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
    public function get($name, $default = null, $separator = null);

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
    public function set($name, $value, $separator = null);

    /**
     * Unset value by using dot notation for nested arrays/objects.
     *
     * @example $data->undef('this.is.my.nested.variable');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function undef($name, $separator = null);

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
    public function def($name, $default = null, $separator = null);

    /**
     * Join nested values together by using blueprints.
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      Value to be joined.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     * @throws \RuntimeException
     */
    public function join($name, $value, $separator = '.');

    /**
     * Get nested structure containing default values defined in the blueprints.
     *
     * Fields without default value are ignored in the list.

     * @return array
     */
    public function getDefaults();

    /**
     * Set default values by using blueprints.
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      Value to be joined.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function joinDefaults($name, $value, $separator = '.');

    /**
     * Get value from the configuration and join it with given data.
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param array|object $value      Value to be joined.
     * @param string  $separator  Separator, defaults to '.'
     * @return array
     * @throws \RuntimeException
     */
    public function getJoined($name, $value, $separator = '.');

    /**
     * Set default values to the configuration if variables were not set.
     *
     * @param array $data
     * @return $this
     */
    public function setDefaults(array $data);

    /**
     * Returns whether the data already exists in the storage.
     *
     * NOTE: This method does not check if the data is current.
     *
     * @return bool
     */
    public function exists();

    /**
     * Return unmodified data as raw string.
     *
     * NOTE: This function only returns data which has been saved to the storage.
     *
     * @return string
     */
    public function raw();

    /**
     * Load user account.
     *
     * Always creates user object. To check if user exists, use $this->exists().
     *
     * @param string $username
     * @param bool $setConfig
     *
     * @return User
     */
    public static function load($username);

    /**
     * Find a user by username, email, etc
     *
     * @param string $query the query to search for
     * @param array $fields the fields to search
     * @return User
     */
    public static function find($query, $fields = ['username', 'email']);

    /**
     * Remove user account.
     *
     * @param string $username
     *
     * @return bool True if the action was performed
     */
    public static function remove($username);

    /**
     * Authenticate user.
     *
     * If user password needs to be updated, new information will be saved.
     *
     * @param string $password Plaintext password.
     *
     * @return bool
     */
    public function authenticate($password);

    /**
     * Checks user authorization to the action.
     * Ensures backwards compatibility
     *
     * @param  string $action
     *
     * @deprecated use authorize()
     * @return bool
     */
    public function authorise($action);

    /**
     * Return the User's avatar URL
     *
     * @return string
     */
    public function avatarUrl();
}
