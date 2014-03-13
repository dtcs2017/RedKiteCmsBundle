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

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Controller;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Form\Security\AlUserType;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Form\Security\AlRoleType;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Exception\General\RuntimeException;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\AlUser;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\AlRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Implements the authentication action to grant the use of the CMS.
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class SecurityController extends Base\BaseController
{
    private $userRepository = null;
    private $roleRepository;

    public function loginAction(Request $request)
    {
        $bootstrapVersion = $this->container->get('red_kite_cms.active_theme')->getThemeBootstrapVersion();
        $this->container->get('twig')->addGlobal('bootstrap_version', $bootstrapVersion);

        $params = $this->checkRequestError($request);

        $response = null;
        $template = 'RedKiteCmsBundle:Security:Login/login-form.html.twig';
        if ($request->isXmlHttpRequest()) {
            $response = new Response();
            $response->setStatusCode('403');
            $template = sprintf('RedKiteCmsBundle:Bootstrap:%s/Security/Login/login-form-ajax.html.twig', $bootstrapVersion);
        }

        $pageRepository = $this->createRepository('Page');
        $languageRepository = $this->createRepository('Language');

        $alPage = $pageRepository->homePage();
        $alLanguage = $languageRepository->mainLanguage();
        $params['target'] = '/backend/' . $alLanguage->getLanguageName() . '/' . $alPage->getPageName();

        return $this->render($template, $params, $response);
    }

    /**
     * @codeCoverageIgnore
     */
    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @codeCoverageIgnore
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
    }

    public function listUsersAction(Request $request)
    {
        return $this->loadUsers();
    }

    public function listRolesAction(Request $request)
    {
        return $this->loadRoles($request);
    }

    public function loadUserAction(Request $request)
    {
        $values = array();
        $userId = $request->get('entityId');
        if (null !== $userId) {
            $alUser = $this->userRepository()->fromPK($userId);
            $values[] = array("name" => "#al_user_id", "value" => $alUser->getId());
            $values[] = array("name" => "#al_user_username", "value" => $alUser->getUserName());
            $values[] = array("name" => "#al_user_email", "value" => $alUser->getEmail());
            $values[] = array("name" => "#al_user_AlRole", "value" => $alUser->getRoleId());
        }

        return $this->buildJsonResponse($values);
    }

    public function loadRoleAction(Request $request)
    {
        $values = array();
        $roleId = $request->get('entityId');
        if (null !== $roleId) {
            $alRole = $this->roleRepository()->fromPK($roleId);
            $values[] = array("name" => "#al_role_id", "value" => $alRole->getId());
            $values[] = array("name" => "#al_role_role", "value" => $alRole->getRole());
        }

        return $this->buildJsonResponse($values);
    }

    public function saveUserAction(Request $request)
    {
        $message = '';
        if ('POST' === $request->getMethod()) {
            $userId = $request->get('userId');
            $isNewUser = (null !== $userId && $userId != 0) ? false : true;
            $user = ( ! $isNewUser) ? $this->userRepository()->fromPk($userId) : new AlUser();

            $userName = $request->get('username');
            if (null !== $this->userRepository()->fromUsername($userName) && $user->getUserName() != $userName ) {
                throw new RuntimeException('exception_username_exists');
            }

            $user->setRoleId($request->get('roleId'));
            $user->setUsername($userName);
            $user->setPassword($request->get('password'));
            $user->setEmail($request->get('email'));

            $validator = $this->container->get('validator');
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $message = $this->renderView('RedKiteCmsBundle:Security:Entities/_errors.html.twig', array(
                    'errors' => $errors,
                ));

                throw new RuntimeException($message);
            }

            $factory = $this->container->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $password = $encoder->encodePassword($request->get('password'), $salt);

            $user->setSalt($salt);
            $user->setPassword($password);

            $messageKey = "security_controller_user_not_saved";
            if ($user->save() > 0) {
                $messageKey = "security_controller_user_saved";
            }
            $message = $this->translate($messageKey);
        }

        $values = array(
            array(
                'key' => 'message',
                'value' => $message,
            ),
            array(
                'key' => 'refresh_list',
                'value' => $this->loadUsersList(),
            ),
        );

        return $this->buildJsonResponse($values);
    }

    public function saveRoleAction(Request $request)
    {
        $message = '';
        $errors = array();
        if ('POST' === $request->getMethod()) {
            $roleId = $request->get('roleId');
            $roleName = strtoupper($request->get('role'));
            $isNewRole = (null !== $roleId && 0 != $roleId) ? false : true;
            $role = ( ! $isNewRole) ? $this->roleRepository()->fromPK($roleId) : new AlRole();
            if (null !== $this->roleRepository()->fromRoleName($roleName) && $role->getRole() != $roleName ) {
                throw new RuntimeException('exception_role_exists');
            }

            $role->setRole($roleName);
            $validator = $this->container->get('validator');
            $errors = $validator->validate($role);
            if (count($errors) > 0) {
                $message = $this->renderView('RedKiteCmsBundle:Security:Entities/_errors.html.twig', array(
                    'errors' => $errors,
                ));

                throw new RuntimeException($message);
            }

            $messageKey = "security_controller_role_not_saved";
            if ($role->save() > 0) {
                $messageKey = "security_controller_role_saved";
            }
            $message = $this->translate($messageKey);
        }

        $values = array(
            array(
                'key' => 'message',
                'value' => $message,
            ),
            array(
                'key' => 'refresh_list',
                'value' => $this->loadRolesList(),
            ),
        );

        return $this->buildJsonResponse($values);
    }

    public function deleteUserAction(Request $request)
    {
        if (null !== $request->get('id')) {
            $user = $this->userRepository()->fromPk($request->get('id'));
            $user->delete();

            $values = array(
                array(
                    'key' => 'message',
                    'value' => $this->translate('security_controller_user_removed'),
                ),
                array(
                    'key' => 'refresh_list',
                    'value' => $this->loadUsersList(),
                ),
            );

            return $this->buildJsonResponse($values);
        }

        throw new RuntimeException('security_controller_nothing_made');
    }

    public function deleteRoleAction(Request $request)
    {
        $roleId = $request->get('id');
        if (null !== $roleId) {
            $users = $this->userRepository()->usersByRole($roleId);
            if (count($users) > 0) {
                throw new RuntimeException('security_controller_role_in_use');
            }

            $user = $this->roleRepository()->fromPK($roleId);
            $user->delete();

            $values = array(
                array(
                    'key' => 'message',
                    'value' => $this->translate('security_controller_role_removed'),
                ),
                array(
                    'key' => 'refresh_list',
                    'value' => $this->loadRolesList(),
                ),
            );

            return $this->buildJsonResponse($values);
        }

        throw new RuntimeException('security_controller_nothing_made');
    }

    /**
     * @codeCoverageIgnore
     */
    protected function checkRequestError(Request $request)
    {
        $session = $request->getSession();

        // get the error if any (works with forward and redirect -- see below)
        $error = '';
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        return array(
            "error" => $error,
            "last_username" => $lastUsername,
        );
    }

    private function loadUsers()
    {
        $form = $this->createForm(new AlUserType(), new AlUser());

        return $this->render('RedKiteCmsBundle:Security:Entities/users_panel.html.twig', array(
            'users' => $this->userRepository()->activeUsers(),
            'form' => $form->createView(),
        ));
    }

    private function loadRoles()
    {
        $form = $this->createForm(new AlRoleType(), new AlRole());

        return $this->render('RedKiteCmsBundle:Security:Entities/roles_panel.html.twig', array(
            'roles' => $this->roleRepository()->activeRoles(),
            'form' => $form->createView(),
        ));
    }

    private function loadUsersList()
    {
        return $this->renderView('RedKiteCmsBundle:Security:Entities/_users_list.html.twig', array(
            'users' => $this->userRepository()->activeUsers(),
        ));
    }

    private function loadRolesList()
    {
        return $this->renderView('RedKiteCmsBundle:Security:Entities/_roles_list.html.twig', array(
            'roles' => $this->roleRepository()->activeRoles(),
        ));
    }

    /**
     * @return \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Repository\UserRepositoryInterface
     */
    private function userRepository()
    {
        if (null === $this->userRepository) {
            $this->userRepository = $this->createRepository('User');
        }

        return $this->userRepository;
    }

    /**
     * @return \RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Repository\RoleRepositoryInterface
     */
    private function roleRepository()
    {
        if (null === $this->roleRepository) {
            $this->roleRepository = $this->createRepository('Role');
        }

        return $this->roleRepository;
    }

    private function buildJsonResponse(array $values)
    {
        $response = new Response(json_encode($values));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
