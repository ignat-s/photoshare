<?php

namespace Phosh\MainBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Phosh\MainBundle\Pager\Pager;

class BaseController extends Controller
{
    /**
     * @return \Phosh\MainBundle\Pager\Pager
     */
    public function createPager()
    {
        return new Pager($this->getRequest()->get('p', 0), $this->getRequest()->get('pp', 100));
    }

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
     * Shortcut to return the session service.
     *
     * @return \Symfony\Component\HttpFoundation\Session
     */
    public function getSession()
    {
        return $this->get('session');
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

    protected function assertTrue($subject, $errorMessage = null, $errorCode = 404)
    {
        if (true != $subject) {
            $this->fail($errorMessage, $errorCode);
        }
    }

    protected function assertFalse($subject, $errorMessage = null, $errorCode = 404)
    {
        if (false != $subject) {
            $this->fail($errorMessage, $errorCode);
        }
    }

    protected function fail($errorMessage = null, $errorCode = 404)
    {
        throw new HttpException($errorCode, $errorMessage);
    }

    protected function getSessionArrayValue($attrName)
    {
        if (!$this->getSession()->has($attrName) || !is_array($this->getSession()->get($attrName))) {
            $this->getSession()->set($attrName, array());
        }
        return $this->getSession()->get($attrName);
    }

    protected function setSessionValue($attrName, $attrValue)
    {
        return $this->getSession()->set($attrName, $attrValue);
    }

    protected function removeSessionValue($attrName)
    {
        return $this->getSession()->remove($attrName);
    }
}
