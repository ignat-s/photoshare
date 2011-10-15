<?php

namespace Phosh\MainBundle\HttpFoundation;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class ImageResponseFactory
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function createImageResponse($imagePath, $format)
    {
        $fileModTime = filemtime($imagePath);

        $headers = array(
            'Content-Type' => 'image/' . $format,
        );

        // Checking if the client is validating his cache and if it is current.
        /*if (($this->request->headers->has('If-Modified-Since')) && (strtotime($this->request->headers->get('If-Modified-Since')) == $fileModTime)) {
            // Client's cache IS current, so we just respond '304 Not Modified'.
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT';
            $responseCode = 304;
        } else */{
            // Image not cached or cache outdated, we respond '200 OK' and output the image.
            $headers += array(
                'Last-Modified' => gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT',
                'Content-transfer-encoding' => 'binary',
                'Content-length' => filesize($imagePath),
            );
            $responseCode = 200;
        }

        return new Response(file_get_contents($imagePath), $responseCode, $headers);
    }
}
