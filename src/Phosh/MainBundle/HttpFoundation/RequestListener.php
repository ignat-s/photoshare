<?php

namespace Phosh\MainBundle\HttpFoundation;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }


        if (!$request->attributes->has('_format')) {
            if (in_array('application/json', $request->getAcceptableContentTypes())) {
                $request->attributes->set('_format', 'json');
            }
        }

        $params = array();
        if ('application/json' == $request->headers->get('Content-Type')) {
            $content = $request->getContent();
            $json = json_decode($content ? $content : '{}', true);
            if (null === $json) {
                throw new HttpException(400, "Request entity is malformed JSON.");
            }
            $params = $json;
        }
        $request->query->add($params);
    }
}
