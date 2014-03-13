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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Block;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Exception\Content\General\ArgumentExpectedException;

/**
 * AlBlockManagerFactoryItem saves the block manager, the id used to identify the block
 * manager itself and a description. Optionally accepts the group attribute, to group
 * togheter the blocks that belongs the same group
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
class AlBlockManagerFactoryItem
{
    private $id;
    private $type;
    private $blockManager;
    private $description;
    private $group;
    private $filter;
    private $requiredAttributes = array('id' => '', 'description' => '');

    /**
     * Constructor
     *
     * @param  AlBlockManagerInterface   $blockManager
     * @param  array                     $attributes
     * @throws ArgumentExpectedException
     *
     * @api
     */
    public function __construct(AlBlockManagerInterface $blockManager, array $attributes)
    {
        $missingAttributes = array_diff_key($this->requiredAttributes, $attributes);
        if (count($missingAttributes) > 0) {
            $exception = array(
                'message' => 'exception_missing_expected_attributes',
                'parameters' => array(
                    '%attributes%' => implode(',', array_keys($missingAttributes)),
                    '%class%' => get_class($blockManager),
                ),
            );
            throw new ArgumentExpectedException(json_encode($exception));
        }

        $this->blockManager = $blockManager;
        $this->id = $attributes['id'];
        $this->type = $attributes['type'];
        $this->description = $attributes['description'];
        $this->filter = array_key_exists('filter', $attributes) ? $attributes['filter'] : 'none';
        $this->group = (array_key_exists('group', $attributes)) ? $attributes['group'] : 'none';
    }

    /**
     * Returns the handled block manager
     *
     * @return AlBlockManagerInterface
     *
     * @api
     */
    public function getBlockManager()
    {
        return $this->blockManager;
    }

    /**
     * Returns the item id
     *
     * @return string
     *
     * @api
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the item id
     *
     * @return string
     *
     * @api
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the item description
     *
     * @return string
     *
     * @api
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the item group
     *
     * @return string
     *
     * @api
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Returns the item filter
     *
     * @return string
     *
     * @api
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
