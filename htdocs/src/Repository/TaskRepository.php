<?php

namespace App\Repository;

use App\Entity\Task;
use App\Helper\TimeCalc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findAllIsset() {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select(['t'])
            ->where('t.status != :status')
            ->setParameters(['status' => Task::STATUS_DELETED]);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function findSearch($name = '', $description = '', $eta = [], $spend = [], $status = '')
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->where('g.status != :status')
            ->setParameters([
                'status' => Task::STATUS_DELETED
            ]);

        if ($name) {
            $queryBuilder->andWhere('g.name LIKE :name')
                ->setParameter('name', "{$name}%");
        }

        if ($description) {
            $queryBuilder->andWhere('g.description LIKE :description')
                ->setParameter('description', "{$description}%");
        }

        if ($eta['value']) {
            $queryBuilder->andWhere("g.eta {$eta['cond']} :eta")
                ->setParameter('eta', TimeCalc::strTimeToSecond($eta['value']));
        }

        if ($spend['value']) {
            $queryBuilder->andWhere("g.spend {$spend['cond']} :spend")
                ->setParameter('spend', TimeCalc::strTimeToSecond($spend['value']));
        }

        if ($status) {
            $queryBuilder->andWhere('g.status = :status')
                ->setParameter('status', Task::CHOICES_STATUS[$status]);
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }

    // /**
    //  * @return Task[] Returns an array of Task objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Task
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
