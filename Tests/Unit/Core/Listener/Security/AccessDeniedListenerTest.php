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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Unit\Core\Listener\Security;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Listener\Security\AccessDeniedListener;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\TestCase;

/**
 * AccessDeniedListenerTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AccessDeniedListenerTest extends TestCase
{
    protected $testListener;
    protected $securityContext;
    protected $authenticationTrustResolver;

    protected function setUp()
    {
        parent::setUp();

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->authenticationTrustResolver = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface');

        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->testListener = new AccessDeniedListener($this->securityContext, $this->authenticationTrustResolver);
    }

    public function testTheResponseIsNotChangedWhenTheRequestIsNotAnAjaxRequest()
    {
        $this->setUpXmlHttpRequestRequest(false);
        $this->responseIsNotCalled();

        $this->testListener->onKernelException($this->event);
    }

    public function testTheResponseIsNotChangedWhenTheExceptionIsNotAccessDenienedException()
    {
        $this->setUpXmlHttpRequestRequest();
        $this->setUpException('LogicException');
        $this->responseIsNotCalled();

        $this->testListener->onKernelException($this->event);
    }

    public function testTheResponseIsNotChangedWhenCmsIsBrowsedByAnAnonymousUser()
    {
        $this->setUpXmlHttpRequestRequest();
        $this->setUpException();
        $this->setUpAuthenticationTrustResolver(true);
        $this->responseIsNotCalled();

        $this->testListener->onKernelException($this->event);
    }

    public function testTheResponseGetsTheAccessDeniendMessage()
    {
        $this->setUpXmlHttpRequestRequest();
        $this->setUpException();
        $this->setUpAuthenticationTrustResolver();
        $this->event->expects($this->once())
            ->method('setResponse');

        $this->testListener->onKernelException($this->event);
    }

    private function responseIsNotCalled()
    {
        $this->event->expects($this->never())
            ->method('setResponse');
    }

    private function setUpXmlHttpRequestRequest($value = true)
    {
        $this->request->expects($this->once())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue($value));
    }

    private function setUpException($exception = 'Symfony\Component\Security\Core\Exception\AccessDeniedException')
    {
        $this->event->expects($this->once())
            ->method('getException')
            ->will($this->returnValue($this->getMock($exception)));
    }

    private function setUpAuthenticationTrustResolver($isAnonymous = false)
    {
        $this->authenticationTrustResolver->expects($this->once())
            ->method('isAnonymous')
            ->will($this->returnValue($isAnonymous));
    }
}
