<?php

/**
 * @package    Grav\Common\User
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\User;

use Grav\Common\Utils;
use Grav\Framework\Flex\FlexCollection;

class UserCollection extends FlexCollection
{
    /**
     * @param string $text
     * @param bool $case_sensitive
     * @param bool $strict
     * @return UserCollection
     */
    public function search(string $text, bool $case_sensitive = false, bool $strict = false) : UserCollection
    {
        $text = trim($text);

        if (!$text) {
            return $this;
        }

        $matching = [];
        /**
         * @var string $key
         * @var User $object
         */
        foreach ($this as $key => $object) {
            if ($strict && Utils::startsWith($object->getProperty('email'), $text, $case_sensitive)) {
                $matching[$key] = $object;
            } elseif (!$strict && Utils::contains($object->getProperty('email'), $text, $case_sensitive)) {
                $matching[$key] = $object;
            }
        }

        return $this->createFrom($matching);
    }
}
