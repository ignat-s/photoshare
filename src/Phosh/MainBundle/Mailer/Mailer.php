<?php

namespace Phosh\MainBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;

use Phosh\MainBundle\Entity\Order;
use Phosh\MainBundle\Entity\ConfigAttr;

class Mailer
{
    protected $mailer;
    protected $entityManager;
    protected $templating;
    protected $parameters;

    public function __construct($mailer, EntityManager $entityManager, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    public function sendOrderCreatedEmailMessage(Order $order)
    {
        $template = $this->parameters['order_created.template'];
        $rendered = $this->templating->render($template, array(
            'order' => $order,
        ));

        $orderCreatedFromEmail = $this->findConfigAttrValueByName('orderCreatedFromEmail');
        $orderCreatedToEmail = $this->findConfigAttrValueByName('orderCreatedToEmail');

        if ($orderCreatedFromEmail && $orderCreatedToEmail) {
            $this->sendEmailMessage($rendered, $orderCreatedFromEmail, $orderCreatedToEmail);
        }
    }

    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body);

        $this->mailer->send($message);
    }

    /**
     * @param $name
     */
    private function findConfigAttrValueByName($name)
    {
        $configAttr = $this->entityManager->getRepository(ConfigAttr::CLASS_NAME)->findOneByName($name);
        if ($configAttr) {
            return $configAttr->getValue();
        }
        return null;
    }
}