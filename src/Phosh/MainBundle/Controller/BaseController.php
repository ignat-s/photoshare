<?php

namespace Phosh\MainBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * Shortcut to return the request service.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    /**
     * @param  string $className
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($className)
    {
        return $this->getEntityManager()->getRepository($className);
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getCurrentUser()
    {
        $token = $this->get('security.context')->getToken();
        return $token ? $token->getUser() : null;
    }

    protected function assertGranted($attribute, $subject = null, $message = 'Access denied')
    {
        $this->assertGrantedIfTrue($this->isGranted($attribute, $subject), $message);
    }

    protected function assertGrantedIfTrue($test, $errorMessage = 'Access denied')
    {
        if (!$test) {
            $this->fail($errorMessage, 403);
        }
    }

    protected function isGranted($attribute, $subject = null)
    {
        return $this->get('security.context')->isGranted($attribute, $subject);
    }

    protected function assertNotNull($subject, $errorMessage = null, $errorCode = 404)
    {
        if ($subject === null) {
            $this->fail($errorMessage, $errorCode);
        }
    }

    protected function fail($errorMessage = null, $errorCode = 404)
    {
        throw new HttpException($errorCode, $errorMessage);
    }
}
