<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\WorkTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getById(int $id, int $page): Project
    {
        return $this->createQueryBuilder('p')
            ->addSelect('w')
            ->addSelect('e')
            ->leftJoin('p.workTimes', 'w')
            ->leftJoin('w.employee', 'e')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->orderBy('w.createdAt', 'DESC')
            ->setMaxResults(WorkTime::PAGE_SIZE)
            ->setFirstResult(($page - 1) * WorkTime::PAGE_SIZE)
            ->getQuery()
            ->getSingleResult();
    }

    public function getAllOpen(): array 
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.deliveredAt IS NULL')
            ->orderBy('p.name', 'ASC');

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getAllDelivered(): array 
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.deliveredAt IS NOT NULL')
            ->orderBy('p.name', 'ASC');

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getPage(int $page): array
    {
        $ids = $this->_em->createQueryBuilder()
            ->select('p.id')
            ->from(Project::class, 'p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(Project::PAGE_SIZE)
            ->setFirstResult(($page - 1) * Project::PAGE_SIZE)
            ->getQuery()
            ->getArrayResult();
    
        $qb = $this->createQueryBuilder('p')
            ->addSelect("w")
            ->addSelect("e")
            ->leftJoin('p.workTimes', 'w')
            ->leftJoin('w.employee', 'e')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('p.createdAt', 'DESC');
    
        return $qb
            ->getQuery()
            ->getResult();
    }

    public function countEmployeesOfProject(int $projectId): int
    {
        return $this->_em->createQueryBuilder()
            ->select('count(distinct w.employee)')
            ->from(WorkTime::class, 'w')
            ->where('w.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countWorkTimesOfProject(int $projectId): int
    {
        return $this->_em->createQueryBuilder()
            ->select('count(w)')
            ->from(WorkTime::class, 'w')
            ->where('w.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getLatest(int $resultCount = 6): array
    {
        $ids = $this->_em->createQueryBuilder()
            ->select('p.id')
            ->from(Project::class, 'p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($resultCount)
            ->getQuery()
            ->getArrayResult();

        $qb = $this->createQueryBuilder('p')
            ->addSelect("w")
            ->addSelect("e")
            ->leftJoin('p.workTimes', 'w')
            ->leftJoin('w.employee', 'e')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('p.createdAt', 'DESC');

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getOldest()
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'ASC')
            ->setMaxResults(1);

        return $qb
            ->getQuery()
            ->getSingleResult();
    }

    private function addWhereProjectClause(QueryBuilder $qb, int $projectId): void
    {
        $qb->where('w.project = :projectId')
            ->setParameter('projectId', $projectId);
    }

//    /**
//     * @return Project[] Returns an array of Project objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Project
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
