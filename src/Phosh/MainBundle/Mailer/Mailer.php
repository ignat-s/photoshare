<?php

namespace Phosh\MainBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;

use Phosh\MainBundle\Entity\Order;

class Mailer
{
    protected $mailer;
    protected $router;
    protected $templating;
    protected $parameters;

    public function __construct($mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    public function sendOrderCreatedEmailMessage(Order $order)
    {
        $template = $this->parameters['order_created.template'];
        $rendered = $this->templating->render($template, array(
            'order' => $order,
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['order_created'], $this->parameters['to_email']['order_created']);
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
}