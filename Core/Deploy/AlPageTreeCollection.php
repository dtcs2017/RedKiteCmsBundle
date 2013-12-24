<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteLabs\RedKiteCmsBundle\Core\Deploy;

use Symfony\Component\DependencyInjection\ContainerInterface;

use RedKiteLabs\RedKiteCmsBundle\Core\PageTree\AlPageTreeDeploy;
use RedKiteLabs\RedKiteCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface;

/**
 * A collection of PageTree objects
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
class AlPageTreeCollection implements \Iterator, \Countable
{
    private $container = null;
    private $pages = array();
    private $factoryRepository = null;
    private $languageRepository = null;
    private $pageRepository = null;

    /**
     * Constructor
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface                            $container
     * @param \RedKiteLabs\RedKiteCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface   $factoryRepository
     *
     * @api
     */
    public function __construct(ContainerInterface $container,
            AlFactoryRepositoryInterface $factoryRepository)
    {
        $this->container = $container;
        $this->factoryRepository = $factoryRepository;
        $this->languageRepository = $this->factoryRepository->createRepository('Language');
        $this->pageRepository = $this->factoryRepository->createRepository('Page');

        $this->setUp();
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->pages);
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return scalar scalar on success, or <b>NULL</b> on failure.
     */
    public function key()
    {
        return key($this->pages);
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        return next($this->pages);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        return reset($this->pages);
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     */
    public function valid()
    {
        return current($this->pages) !== false;
    }

    /**
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->pages);
    }

    /**
     * Returns the AlPageTree object stored at the requird key
     *
     * @param  string                                                      $key
     * @return null|\RedKiteLabs\RedKiteCmsBundle\Core\PageTree\AlPageTree
     *
     * @api
     */
    public function at($key)
    {
        if (!array_key_exists($key, $this->pages)) {
            return null;
        }

        return $this->pages[$key];
    }

    /**
     * Fills up the PageTree collection traversing the saved languages and pages
     */
    protected function setUp()
    {
        $languages = $this->languageRepository->activeLanguages();
        $pages = $this->pageRepository->activePages();

        // Cycles all the website's languages
        foreach ($languages as $language) {
            // Cycles all the website's pages
            foreach ($pages as $page) {
                if ( ! $page->getIsPublished()) {
                    continue;
                }

                $pageTree = new AlPageTreeDeploy(
                    $this->container,
                    $this->factoryRepository
                );

                $pageTree
                    ->setExtraAssetsSuffixes()
                    ->refresh(
                        $language->getId(),
                        $page->getId()
                    );

                $this->pages[] = $pageTree;
            }
        }
    }
}
