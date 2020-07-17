<?php

namespace App\Repository;

use Requestum\ApiBundle\Repository\ApiRepositoryTrait;
use Requestum\ApiBundle\Repository\FilterableRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class ApiRepository
 */
class ApiRepository extends EntityRepository implements FilterableRepositoryInterface
{
    use ApiRepositoryTrait;
}
