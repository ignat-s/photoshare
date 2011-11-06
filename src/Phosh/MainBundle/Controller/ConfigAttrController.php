<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\ConfigAttr;
use Phosh\MainBundle\Form\Type\ConfigAttrType;

/**
 * @Route("/admin/config")
 * @Secure(roles="ROLE_ADMIN")
 */
class ConfigAttrController extends BaseController
{
    /**
     * @Route("", name="config_attr_index", requirements={"_method"="GET"})
     * @Template()
     */
    public function indexAction()
    {
        $configAttrs = $this->getEntityManager()->createQueryBuilder()
                ->select('configAttr')
                ->from('PhoshMainBundle:ConfigAttr', 'configAttr')
                ->orderBy('configAttr.name', 'ASC')
                ->getQuery()->execute();

        return array(
            'config_attrs' => $configAttrs,
            'missed_config_attr_names' => $this->getMissedConfigAttrNames($configAttrs),
        );
    }

    /**
     * @Route("/{id}", name="config_attr_show", requirements={"_method"="GET", "id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:ConfigAttr")
     * @Template()
     */
    public function showAction(ConfigAttr $configAttr)
    {
        return array(
            'config_attr' => $configAttr,
        );
    }

    /**
     * @Route("/create", name="config_attr_create")
     * @Template()
     */
    public function createAction()
    {
        $configAttr = new ConfigAttr();
        $form = $this->createForm(new ConfigAttrType(), $configAttr);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                if ($this->isConfigAttrUnique($configAttr)) {
                    $this->getEntityManager()->persist($configAttr);
                    $this->getEntityManager()->flush();
                    $this->get('session')->setFlash('success', 'Config attribute added');
                    return $this->redirect($this->generateUrl('config_attr_show', array('id' => $configAttr->getId())));
                } else {
                    $this->get('session')->setFlash('error', 'Config attribute with that name is already exists');
                }
            } else {
                $this->get('session')->setFlash('error', 'Config attribute saving error');
            }
        }

        return array(
            'config_attr' => $configAttr,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="config_attr_edit", requirements={"id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:ConfigAttr")
     * @Template()
     */
    public function editAction(ConfigAttr $configAttr)
    {
        $form = $this->createForm(new ConfigAttrType(), $configAttr);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                if ($this->isConfigAttrUnique($configAttr)) {
                    $this->getEntityManager()->flush();
                    $this->get('session')->setFlash('success', 'Config attribute updated');
                    return $this->redirect($this->generateUrl('config_attr_show', array('id' => $configAttr->getId())));
                } else {
                    $this->get('session')->setFlash('error', 'Config attribute with that name is already exists');
                }
            } else {
                $this->get('session')->setFlash('error', 'Config attribute saving error');
            }
        }

        return array(
            'config_attr' => $configAttr,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/delete", name="config_attr_delete", requirements={"id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:ConfigAttr")
     */
    public function deleteAction(ConfigAttr $configAttr)
    {
        try {
            $this->getEntityManager()->remove($configAttr);
            $this->getEntityManager()->flush();

            $this->get('session')->setFlash('success', 'Config attribute deleted');
            return $this->redirect($this->generateUrl('config_attr_index'));
        } catch (\PDOException $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
            return $this->redirect($this->generateUrl('config_attr_show', array('id' => $configAttr->getId())));
        }
    }

    /**
     * @param \Phosh\MainBundle\Entity\ConfigAttr[] $configAttrs
     * @return array
     */
    public function getMissedConfigAttrNames(array $configAttrs)
    {
        $result = array();
        $requiredConfigAttrNames = $this->container->getParameter('phosh_main.required_config_attrs');
        foreach ($requiredConfigAttrNames as $attrName) {
            $found = false;
            foreach ($configAttrs as $configAttr) {
                if ($configAttr->getName() == $attrName) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[] = $attrName;
            }
        }

        return $result;
    }

    public function isConfigAttrUnique(ConfigAttr $configAttr)
    {
        return $this->getEntityManager()->createQueryBuilder()
                ->select('configAttr')
                ->from(ConfigAttr::CLASS_NAME, 'configAttr')
                ->where('configAttr.name = :name')
                ->andWhere('configAttr.id != :id')
                ->setParameters(array(
                                    'id' => $configAttr->getId(),
                                    'name' => $configAttr->getName(),
                                ))
                ->getQuery()->getOneOrNullResult() == null;
    }
}