<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Configuration;

/**
 * Defines the interface to write/read to/from a parameter -> value entity
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
interface ConfigurationInterface
{
    /**
     * Reads a configuration parameter
     *
     * @param  string $parameter
     * @return string
     */
    public function read($parameter);

    /**
     * Writes the new value for the given parameter
     *
     * @param  string $parameter
     * @param  string $value
     * @return int    Affected records
     */
    public function write($parameter, $value);
}
