<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 21/11/17
 * Time: 12:43
 */

namespace AppBundle\Pagination;


class PaginetedCollection
{
    private $items;
    private $total;
    private $count;
    private $_links = [];

    public function __construct($items, $total)
    {
        $this->items = $items;
        $this->total = $total;
        $this->count = count($items);
    }

    public function addLink($rel, $url)
    {
        $this->_links[$rel] = $url;
    }
}