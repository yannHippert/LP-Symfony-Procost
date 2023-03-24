<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Worktime;
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

    public function getById(int $id): Project
    {
        $qb = $this->createQueryBuilder('p');
        $this->addSelectWorktimes($qb);
        $this->addSelectEmployee($qb);
        $this->addWhereProject($qb, $id);
            
        return $qb
            ->getQuery()
            ->getSingleResult();
    }

    public function getAllOpen(): array 
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.deliveredAt IS NULL');
        $this->addOrderByCreation($qb);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getAllDelivered(): array 
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.deliveredAt IS NOT NULL');
        $this->addOrderByCreation($qb);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getPage(int $page, int $pageSize = Project::PAGE_SIZE): array
    {
        $idQb = $this->createQueryBuilder('p')
            ->select('p.id');
        $this->addOrderByCreation($idQb);
        $this->addPagination($idQb, $page, $pageSize);

        $ids = $idQb
            ->getQuery()
            ->getArrayResult();
    
        $qb = $this->createQueryBuilder('p');
        $this->addOrderByCreation($qb);
        $this->addSelectWorktimes($qb);
        $this->addSelectEmployee($qb);
        $this->addWhereIdIn($qb, $ids);
    
        return $qb
            ->getQuery()
            ->getResult();
    }

    public function countEmployeesOfProject(int $projectId): int
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('count(distinct w.employee)')
            ->from(Worktime::class, 'w')
            ->where('w.project = :projectId')
            ->setParameter('projectId', $projectId);
        
        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countProfitable()
    {
        $subquery = $this->_em->createQueryBuilder()
            ->select('SUM(w.daysSpent * e.dailySalary)')
            ->from(Worktime::class, 'w')
            ->leftJoin('w.employee', 'e')
            ->where('w.project = p.id')
            ->getDQL();
    
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.deliveredAt IS NOT NULL')
            ->andWhere("p.price < ($subquery)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getLatest(int $resultCount = 6): array
    {
        $idQb = $this->createQueryBuilder('p')
            ->select('p.id')
            ->setMaxResults($resultCount);
        $this->addOrderByCreation($idQb);
        
        $ids = $idQb
            ->getQuery()
            ->getArrayResult();

        $qb = $this->createQueryBuilder('p');
        $this->addOrderByCreation($qb);
        $this->addSelectWorktimes($qb);
        $this->addSelectEmployee($qb);
        $this->addWhereIdIn($qb, $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }

    private function addSelectWorktimes(QueryBuilder $qb): void
    {
        $qb
            ->addSelect("w")
            ->leftJoin('p.worktimes', 'w');
    }

    private function addSelectEmployee(QueryBuilder $qb): void
    {
        $qb
            ->addSelect("e")
            ->leftJoin('w.employee', 'e');
    }

    private function addWhereProject(QueryBuilder $qb, int $projectId): void
    {
        $qb
            ->where('w.project = :projectId')
            ->setParameter('projectId', $projectId);
    }

    private function addWhereIdIn(QueryBuilder $qb, array $projectIds): void
    {
        $qb
            ->where('p.id IN (:projectIds)')
            ->setParameter('projectIds', $projectIds);
    }

    private function addOrderByCreation(QueryBuilder $qb, bool $isDescending = true): void
    {
        $qb
            ->orderBy('p.createdAt', $isDescending ? 'DESC' : 'ASC');
    }

    private function addPagination(QueryBuilder $qb, ?int $page, int $pageSize = Worktime::PAGE_SIZE): void
    {
        if($page != null) {
            $qb
                ->setMaxResults($pageSize)
                ->setFirstResult(($page - 1) * $pageSize);
        }
    }

}

