<?php

namespace Phosh\MainBundle\Twig\Extension;

use \Phosh\MainBundle\Entity\Post;
use \Phosh\MainBundle\PhotoStorage;
use \Symfony\Component\Routing\Router;

class PostExtension extends \Twig_Extension
{
    private $photoStorage;
    private $router;

    public function __construct(PhotoStorage $photoStorage, Router $router)
    {
        $this->photoStorage = $photoStorage;
        $this->router = $router;
    }

    public function getFilters()
    {
        return array(
            'markdown' => new \Twig_Filter_Method($this, 'transformMarkdown'),
            'post_html' => new \Twig_Filter_Method($this, 'getPostHTML'),
        );
    }

    public function transformMarkdown($string)
    {
        $result = \Markdown($string);

        return $result;
    }

    public function getPostHTML(Post $post)
    {
        $imageThumbUrl = $this->router->generate('post_image_thumb', array('token' => $post->getToken()));
        $imageFullUrl = $this->router->generate('post_image_full', array('token' => $post->getToken()));
        $postTitle = htmlspecialchars($post->getTitle());

        $html = $this->transformMarkdown($post->getBody());
        $html = preg_replace('/<photo\s+src="([^"]+)"\s*(rotate="(90cw|90ccw|180)")?\s*\/>/i', sprintf("<a class=\"colorbox\" rel=\"%s\" href=\"%s?r=$3&p=$1\"><img src=\"%s?r=$3&p=$1\"/></a>", $postTitle, $imageFullUrl, $imageThumbUrl), $html);
        $html = preg_replace('/<photo\s+(rotate="(90cw|90ccw|180)")?\s*src="([^"]+)"\s*\/>/i', sprintf("<a class=\"colorbox\" rel=\"%s\" href=\"%s?r=$2&p=$3\"><img src=\"%s?r=$2&p=$3\"/></a>", $postTitle, $imageFullUrl, $imageThumbUrl), $html);

        return $html;
    }

    public function getName()
    {
        return 'post';
    }
}