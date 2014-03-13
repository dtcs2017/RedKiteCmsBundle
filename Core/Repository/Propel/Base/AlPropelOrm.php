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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\Base;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Orm\OrmInterface;

/**
 *  Implements the OrmInterface for Propel Orm
 *
 *  @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlPropelOrm implements OrmInterface
{
    protected static $connection = null;
    protected $affectedRecords = null;

    /**
     * Constructor
     *
     * @param \PropelPDO $connection
     */
    public function __construct(\PropelPDO $connection = null)
    {
        self::$connection = $connection;
        if (null === $connection) {
            try {
                self::$connection = \Propel::getConnection();
            } catch (\Exception $ex) {
                // We are in test environment, so propel connection does not matter
            }
        }
    }

    /**
     * {@ inheritdoc}
     */
    public function setConnection($connection)
    {
        self::$connection = $connection;

        return $this;
    }

    /**
     * {@ inheritdoc}
     */
    public function getConnection()
    {
        return self::$connection;
    }

    /**
     * {@ inheritdoc}
     */
    public function startTransaction()
    {
        self::$connection->beginTransaction();

        return $this;
    }

    /**
     * {@ inheritdoc}
     */
    public function commit()
    {
        self::$connection->commit();

        return $this;
    }

    /**
     * {@ inheritdoc}
     */
    public function rollBack()
    {
        self::$connection->rollBack();

        return $this;
    }

    /**
     * {@ inheritdoc}
     */
    public function getAffectedRecords()
    {
        return $this->affectedRecords;
    }

    /**
     * {@ inheritdoc}
     */
    public function save(array $values, $modelObject = null)
    {
        try {
            if (null !== $modelObject) {
                $this->setRepositoryObject($modelObject);
            }

            $this->startTransaction();
            $this->modelObject->fromArray($values);
            $this->affectedRecords = $this->modelObject->save();

            if ($this->affectedRecords == 0) {
                $success = ($this->modelObject->isModified()) ? false : null;
            } else {
                $success = true;
            }

            if (false !== $success) {
                $this->commit();
            } else {
                $this->rollBack();
            }

            return $success;
        } catch (\Exception $ex) {
            $this->rollBack();

            throw $ex;
        }
    }

    /**
     * {@ inheritdoc}
     */
    public function delete($modelObject = null)
    {
        try {
            $values = array('ToDelete' => 1);

            return $this->save($values, $modelObject);
        } catch (\Exception $ex) {

            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function executeQuery($query)
    {
        $statement = self::$connection->prepare($query);

        return $statement->execute();
    }
}
