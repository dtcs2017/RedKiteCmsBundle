<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Listener\Page;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Listener\Page\EditSeoListener;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Listener\Base\BaseListenerTest;

/**
 * EditSeoListenerTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class EditSeoListenerTest extends BaseListenerTest
{
    private $event;
    private $testListener;
    private $pageManager;
    private $seoManager;
    private $pageRepository;
    private $seoRepository;
    private $templateManager;
    private $pageContents;

    protected function setUp()
    {
        parent::setUp();

        $this->seoManager = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Seo\SeoManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->pageRepository = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\PageRepositoryPropel')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->seoRepository = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\SeoRepositoryPropel')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->event = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Event\Content\Page\BeforeEditPageCommitEvent')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->pageManager = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Page\PageManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->templateManager = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\Template\TemplateManager')
                           ->disableOriginalConstructor()
                            ->getMock();

        $this->pageContents = $this->getMockBuilder('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Content\PageBlocks\PageBlocks')
                           ->disableOriginalConstructor()
                            ->getMock();

        $this->testListener = new EditSeoListener($this->seoManager);
    }

    public function testAnythingIsExecutedWhenTheEventHadBeenAborted()
    {
        $this->event->expects($this->once())
            ->method('isAborted')
            ->will($this->returnValue(true));

        $this->testListener->onBeforeEditPageCommit($this->event);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValuesParamIsNotAnArray()
    {
        $this->event->expects($this->once())
            ->method('getContentManager')
            ->will($this->returnValue($this->pageManager));

        $this->event->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue('fake'));

        $this->testListener->onBeforeEditPageCommit($this->event);
    }

    public function testAnythingIsMadeWhenTheSeoObjectIsNotFound()
    {
        $this->setUpPageRepository();

        $this->pageRepository->expects($this->never())
            ->method('startTransaction');

        $this->event->expects($this->once())
            ->method('getContentManager')
            ->will($this->returnValue($this->pageManager));

        $this->event->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(array()));

        $this->event->expects($this->never())
            ->method('abort');

        $this->setUpCommonObjects();

        $this->seoManager->expects($this->never())
            ->method('save');

        $this->seoRepository->expects($this->once())
            ->method('fromPageAndLanguage')
            ->will($this->returnValue(null));

        $this->testListener->onBeforeEditPageCommit($this->event);
    }

    public function testANewSeoIsAddedWhenItDoesNotExist()
    {
        $this->setUpPageRepository();

        $this->pageRepository->expects($this->once())
            ->method('startTransaction');

        $this->pageRepository->expects($this->once())
            ->method('rollBack');

        $this->event->expects($this->once())
            ->method('getContentManager')
            ->will($this->returnValue($this->pageManager));

        $this->event->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(array('permalink' => 'fake')));

        $this->event->expects($this->once())
            ->method('abort');

        $this->setUpCommonObjects();

        $this->seoManager->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));

        $seo= $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Seo');
        $this->seoRepository->expects($this->once())
            ->method('fromPageAndLanguage')
            ->will($this->returnValue(null));

        $this->seoManager->expects($this->once())
            ->method('getSeoRepository')
            ->will($this->returnValue($this->seoRepository));

        $this->testListener->onBeforeEditPageCommit($this->event);
    }

    public function testSaveFailsWhenAttributesAreNotSaved()
    {
        $this->setUpPageRepository();

        $this->pageRepository->expects($this->once())
            ->method('startTransaction');

        $this->pageRepository->expects($this->once())
            ->method('rollBack');

        $this->event->expects($this->once())
            ->method('getContentManager')
            ->will($this->returnValue($this->pageManager));

        $this->event->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(array('permalink' => 'fake')));

        $this->event->expects($this->once())
            ->method('abort');

        $this->setUpCommonObjects();

        $this->seoManager->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));

        $seo= $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Seo');
        $this->seoRepository->expects($this->once())
            ->method('fromPageAndLanguage')
            ->will($this->returnValue($seo));

        $this->seoManager->expects($this->once())
            ->method('getSeoRepository')
            ->will($this->returnValue($this->seoRepository));

        $this->testListener->onBeforeEditPageCommit($this->event);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSaveFailsBecauseAndUnespectedExceptionIsThrown()
    {
        $this->setUpPageRepository();

        $this->pageRepository->expects($this->once())
            ->method('startTransaction');

        $this->pageRepository->expects($this->once())
            ->method('rollBack');

        $this->event->expects($this->once())
            ->method('getContentManager')
            ->will($this->returnValue($this->pageManager));

        $this->event->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(array('permalink' => 'fake')));

        $this->event->expects($this->once())
            ->method('abort');

        $this->setUpCommonObjects();

        $this->seoManager->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \RuntimeException()));

        $seo= $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Seo');
        $this->seoRepository->expects($this->once())
            ->method('fromPageAndLanguage')
            ->will($this->returnValue($seo));

        $this->seoManager->expects($this->once())
            ->method('getSeoRepository')
            ->will($this->returnValue($this->seoRepository));

        $this->testListener->onBeforeEditPageCommit($this->event);
    }

    public function testSave()
    {
        $this->setUpPageRepository();

        $this->pageRepository->expects($this->once())
            ->method('startTransaction');

        $this->pageRepository->expects($this->once())
            ->method('commit');

        $this->pageRepository->expects($this->never())
            ->method('rollback');

        $this->event->expects($this->once())
            ->method('getContentManager')
            ->will($this->returnValue($this->pageManager));

        $this->event->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(array('permalink' => 'fake')));

        $this->event->expects($this->never())
            ->method('abort');

        $this->setUpCommonObjects();

        $this->seoManager->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $seo= $this->getMock('RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Seo');
        $this->seoRepository->expects($this->once())
            ->method('fromPageAndLanguage')
            ->will($this->returnValue($seo));

        $this->seoManager->expects($this->once())
            ->method('getSeoRepository')
            ->will($this->returnValue($this->seoRepository));

        $this->testListener->onBeforeEditPageCommit($this->event);
    }

    private function setUpCommonObjects()
    {
        $this->setUpPageRepository();

        $this->templateManager->expects($this->once())
            ->method('getPageBlocks')
            ->will($this->returnValue($this->pageContents));

        $this->pageManager->expects($this->once())
            ->method('getPageRepository')
            ->will($this->returnValue($this->pageRepository));

        $this->pageManager->expects($this->once())
            ->method('getTemplateManager')
            ->will($this->returnValue($this->templateManager));


        $this->seoManager->expects($this->once())
            ->method('getSeoRepository')
            ->will($this->returnValue($this->seoRepository));
    }

    private function setUpPageRepository()
    {
        $this->pageManager->expects($this->once())
            ->method('getPageRepository')
            ->will($this->returnValue($this->pageRepository));
    }
}
