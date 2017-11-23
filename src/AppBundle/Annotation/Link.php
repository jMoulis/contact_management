<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 21/11/17
 * Time: 14:02
 */

namespace AppBundle\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Link
{
    /**
     * @Required
     */
    public $name;

    /**
     * @Required
     */
    public $route;

    public $params = [];
}