<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\TaskList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TaskList|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskList|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskList[]    findAll()
 * @method TaskList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskList::class);
    }

    public function findAllIsset() {
        $queryBuilder = $this->createQueryBuilder('t')
            ->where('t.status != :status')
            ->setParameters(['status' => TaskList::STATUS_DELETED]);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function findSearch($name = '') {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select(['t.id'])
            ->where('t.status != :status')
            ->setParameter('status', TaskList::STATUS_DELETED);

        if ($name) {
            $queryBuilder->andWhere('t.name LIKE :nameEntity')
                ->setParameter('nameEntity',"{$name}%");

        };

        $result = [];

        $taskLists = $queryBuilder->getQuery()->getArrayResult();
        foreach ($taskLists as $taskList) {
            $result[] = $this->find($taskList['id']);
        }

        return $result;
    }

    /**
     * @param string $name
     * @return TaskList[] Returns an array of TaskList objects
     */
    public function findByI(string $name)
    {
//        $queryBuilder = $this->createQueryBuilder('t')
//            ->select(['t', 'g'])
//            ->andWhere('t.id = :id')
//            ->innerJoin(Group::class, 'g', 'WITH', 't.id = g.taskList')
////            ->innerJoin('g.task', 't2')
//            ->setParameter('id', $id)
//            ->getQuery();
//
//        return $queryBuilder->getResult();
        return $this->findBy(['name' => $name]);
    }


    /*
    public function findOneBySomeField($value): ?TaskList
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
