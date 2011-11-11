<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\ConfigAttr;
use Phosh\MainBundle\Form\Model\ConfigModel;
use Phosh\MainBundle\Form\Type\ConfigType;

/**
 * @Route("/admin/config")
 * @Secure(roles="ROLE_ADMIN")
 */
class ConfigController extends BaseController
{
    /**
     * @Route("/", name="config_edit")
     * @Template()
     */
    public function editAction()
    {
        $config = $this->getConfigModel();
        $form = $this->createForm(new ConfigType(), $config);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $this->saveConfigModel($config);
                $this->getEntityManager()->flush();

                $this->get('session')->setFlash('success', 'Config saved');
                return $this->redirect($this->generateUrl('config_edit'));
            } else {
                $this->get('session')->setFlash('error', 'Config saving error');
            }
        }

        return array(
            'config' => $config,
            'form' => $form->createView(),
        );
    }

    /**
     * @return \Phosh\MainBundle\Form\Model\ConfigModel
     */
    private function getConfigModel()
    {
        $config = new ConfigModel();
        $config->indexPageContent = $this->getConfigAttrValue('indexPageContent');
        $config->orderCreatedToEmail = $this->getConfigAttrValue('orderCreatedToEmail');

        return $config;
    }

    private function saveConfigModel(ConfigModel $config)
    {
        $this->getOrCreateConfigAttr('indexPageContent')->setValue($config->indexPageContent);
        $this->getOrCreateConfigAttr('orderCreatedToEmail')->setValue($config->orderCreatedToEmail);

        return $config;
    }

    /**
     * @return string
     */
    private function getConfigAttrValue($name)
    {
        $configAttr = $this->findConfigAttr($name);
        if ($configAttr) {
            return $configAttr->getValue();
        }

        return null;
    }

    /**
     * @return \Phosh\MainBundle\Entity\ConfigAttr
     */
    private function getOrCreateConfigAttr($name)
    {
        $result = $this->getRepository(ConfigAttr::CLASS_NAME)->findOneByName($name);
        if (!$result) {
            $result = new ConfigAttr();
            $result->setName($name);
            $this->getEntityManager()->persist($result);
        }

        return $result;
    }

    /**
     * @return \Phosh\MainBundle\Entity\ConfigAttr|null
     */
    private function findConfigAttr($name)
    {
        return $this->getRepository(ConfigAttr::CLASS_NAME)->findOneByName($name);
    }
}