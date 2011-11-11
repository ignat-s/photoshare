<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Phosh\MainBundle\Entity\ConfigAttr;

class DefaultController extends BaseController
{
    /**
     * @Route("/", name="index")
     * @Template()
     */
    public function indexAction()
    {
        $contentContigAttr = $this->getRepository(ConfigAttr::CLASS_NAME)->findOneByName('indexPageContent');

        return array(
            'content' => $contentContigAttr ? $contentContigAttr->getValue() : '',
        );
    }
}
