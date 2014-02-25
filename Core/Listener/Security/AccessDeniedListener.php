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

namespace RedKiteLabs\RedKiteCmsBundle\Core\Listener\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;

/**
 * Listens for kernel exceptions to intercept the AccessDeniedException during an Ajax
 * request
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
class AccessDeniedListener
{
    /** @var SecurityContextInterface */
    private $context;
    /** @var AuthenticationTrustResolverInterface */
    private $authenticationTrustResolver;

    /**
     * Construct
     *
     * @param SecurityContextInterface             $context
     * @param AuthenticationTrustResolverInterface $trustResolver
     *
     * @api
     */
    public function __construct(SecurityContextInterface $context, AuthenticationTrustResolverInterface $trustResolver)
    {
        $this->context = $context;
        $this->authenticationTrustResolver = $trustResolver;
    }

    /**
     * Returns a response when is an ajax request and an AccessDeniedException has been thrown
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @api
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getException();

        if ($request->isXmlHttpRequest()) {
            if ($exception instanceof AccessDeniedException) {
                $token = $this->context->getToken();
                if (!$this->authenticationTrustResolver->isAnonymous($token)) {
                    $event->setResponse(new Response('You haven\'t enough privileges to perform the required action', 403));
                }
            }
        }
    }
}
