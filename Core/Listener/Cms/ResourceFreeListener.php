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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Listener\Cms;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\ResourcesLocker\AlResourcesLocker;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\ResourcesLocker\Exception\ResourceNotFreeException;

/**
 * Checks that the requested resource is not used by any other user. When it is not free,
 * it stops the request propagation and returns a response warning the user that the
 * resouce is locked, when it is available, it locks the resource for the current user.
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
class ResourceFreeListener
{
    private $securityContext;
    private $resourcesLocker;

    /**
     * Contructor
     *
     * @param \Symfony\Component\Security\Core\SecurityContext                     $securityContext
     * @param \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\ResourcesLocker\AlResourcesLocker $resourcesLocker
     *
     * @api
     */
    public function __construct(SecurityContext $securityContext, AlResourcesLocker $resourcesLocker)
    {
        $this->securityContext = $securityContext;
        $this->resourcesLocker = $resourcesLocker;
    }

    /**
     * Listen to onKernelRequest event to lock a resource
     *
     * @param GetResponseEvent $event
     *
     * @api
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // checks if the backend is secured
        $token = $this->securityContext->getToken();
        if (null !== $token) {

            // Check if the user has already been logged in
            $user = $token->getUser();
            if (null !== $user) {
                $errorMessage = '';
                $userId = $user->getId();

                // Frees the expired locked resources and the resource previously locked
                // by the user
                try {
                    $this->resourcesLocker->unlockExpiredResources();
                    $this->resourcesLocker->unlockUserResource($userId);
                } catch (\PropelException $ex) {
                    $errorMessage = $ex->getMessage();
                }

                if ($errorMessage == '') {
                    $request = $event->getRequest();

                    // LOcks the resource
                    $locked = $request->get('locked');
                    if (null !== $locked) {
                        try {
                            // Process composite locking rules
                            $rules = explode(',', $locked);
                            if (isset($rules[1])) {
                                $locked = $rules[0];
                                $param = $request->get($rules[1]);
                            } else {
                                $param = $request->get($locked);
                            }

                            $key = ('locked' !== $locked) ? $locked . "=" . $param : $request->getUri() . '/locked';
                            $this->resourcesLocker->lockResource($userId, md5($key));
                        } catch (\PropelException $ex) {
                            $errorMessage = 'exception_resource_was_free_but_someone_locked_it';
                        }
                    }
                }

                // The resource is not free, stops the request
                if ($errorMessage != '') {
                    throw new ResourceNotFreeException($errorMessage);
                }
            }
        }
    }
}
