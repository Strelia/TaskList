<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Task;
use App\Helper\TimeCalc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    public function findAllIsset() {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select(['g'])
            ->where('g.status != :status')
            ->setParameters(['status' => Group::STATUS_DELETED]);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function findForDelete($id) {
        // TODO
        $queryBuilder = $this->createQueryBuilder('g')
            ->select(['g', 't'])
            ->where('g.id = :id')
            ->innerJoin('g.tasks', 't')
            ->setParameters([
                'id' => $id
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findSearch($name = '', $description = '', $eta = []) {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select('g.name')
            ->where('g.status != :status')
            ->setParameters([
                'status' => Group::STATUS_DELETED
            ]);

        if ($name) {
            $queryBuilder->andWhere('g.name LIKE :name')
                ->setParameter('name',"{$name}%");
        }

        if ($description) {
            $queryBuilder->andWhere('g.description LIKE :description')
                ->setParameter('description',"{$description}%");
        }

        if (count($eta)) {
            $queryBuilder->andWhere("g.eta {$eta['cond']} :eta")
                ->setParameter('eta', TimeCalc::strTimeToSecond($eta['value']));
        }

        $result = [];

        $taskLists = $queryBuilder->getQuery()->getArrayResult();
        foreach ($taskLists as $taskList) {
            $result[] = $this->find($taskList['id']);
        }

        return $result;
    }

    // /**
    //  * @return Group[] Returns an array of Group objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Group
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
