<?php

namespace Phosh\MainBundle\Pager;
 
class Pager
{
    private $page;
    private $perPage;
    private $count;

    public function __construct($page, $perPage, $count = null)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->count = $count;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getPerPage()
    {
        return $this->perPage;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getOffset()
    {
        return $this->page * $this->perPage;
    }

    public function getPagesCount()
    {
        return ceil($this->count / $this->perPage);
    }

    public function isFirstPage()
    {
        return $this->page == 0;
    }

    public function isLastPage()
    {
        return $this->page == $this->getPagesCount() - 1;
    }
}
