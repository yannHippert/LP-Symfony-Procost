<?php

namespace App\Repository;

use App\Entity\WorkTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkTime>
 *
 * @method WorkTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkTime[]    findAll()
 * @method WorkTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkTime::class);
    }

    public function save(WorkTime $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WorkTime $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countOfEmployee(int $employeeId): int
    {
        return $this->_em->createQueryBuilder()
            ->select('count(w.id)')
            ->from(WorkTime::class, 'w')
            ->where('w.employee = :employeeId')
            ->setParameter('employeeId', $employeeId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByEmployeeId(int $employeeId, int $page = 1): array
    {
        return $this->createQueryBuilder('w')
            ->addSelect('p')
            ->addSelect('e')
            ->leftJoin('w.project', 'p')
            ->leftJoin('w.employee', 'e')
            ->where('w.employee = :employeeId')
            ->setParameter('employeeId', $employeeId)
            ->orderBy('w.createdAt', 'DESC')
            ->setMaxResults(WorkTime::PAGE_SIZE)
            ->setFirstResult(($page - 1) * WorkTime::PAGE_SIZE)
            ->getQuery()
            ->getResult();
    }

    public function getOfProject(int $projectId): array
    {
        return $this->createQueryBuilder('w')
            ->addSelect('e')
            ->leftJoin('w.employee', 'e')
            ->where('w.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return WorkTime[] Returns an array of WorkTime objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?WorkTime
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
