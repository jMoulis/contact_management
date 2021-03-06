<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 21/11/17
 * Time: 12:56
 */

namespace AppBundle\Pagination;


use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PaginationFactory
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createCollection(QueryBuilder $qb, Request $request, $route, array $routeParams = [])
    {

        $page = $request->query->get('page', 1);

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page);


        $contacts = [];
        foreach ($pagerfanta->getCurrentPageResults() as $contact) {
            $contacts[] = $contact;
        }

        $paginatedCollection = new PaginetedCollection(
            $contacts,
            $pagerfanta->getNbResults()
        );

        $routeParams = array_merge($routeParams, $request->query->all());

        $createLinkUrl = function($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                [
                    'page' => $targetPage
                ]
            ));
        };

        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($pagerfanta->getNbPages()));

        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($pagerfanta->getNextPage()));

        }
        if ($pagerfanta->hasPreviousPage()) {
        $paginatedCollection->addLink('prev', $createLinkUrl($pagerfanta->getPreviousPage()));
        }

        return $paginatedCollection;
    }
}