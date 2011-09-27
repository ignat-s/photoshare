<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends BaseController
{
    /**
     * @Template()
     */
    public function loginAction()
    {
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->get('request');
        
        $error = null;
        $errorMessage = null;

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
            $request->getSession()->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error) {
            if ($error instanceof BadCredentialsException) {
                $errorMessage = 'Invalid login or user password.';
            } else {
                $errorMessage = $error->getMessage();
            }
        }

        $response = new \Symfony\Component\HttpFoundation\Response();
        if ($request->getSession()->getFlash('access_denied_for_anonymous_token')) {
            $response->setStatusCode(401);
        }

        return array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error_message' => $errorMessage,
            'error' => $error,
        );
    }
}
