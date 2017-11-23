<?php

namespace AppBundle\Repository;

/**
 * ContactRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContactRepository extends \Doctrine\ORM\EntityRepository
{
    public function finAllQueryBuilder()
    {
        return $this->createQueryBuilder('contact');
    }

    public function findAllQueryBuilder($filter = '')
    {
        $qb = $this->createQueryBuilder('contact');

        if ($filter) {
            $qb->andWhere('contact.firstname LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        return $qb;
    }
}
