<?php

namespace App\Repository;

use App\Entity\Worktime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Worktime>
 *
 * @method Worktime|null find($id, $lockMode = null, $lockVersion = null)
 * @method Worktime|null findOneBy(array $criteria, array $orderBy = null)
 * @method Worktime[]    findAll()
 * @method Worktime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorktimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Worktime::class);
    }

    public function save(Worktime $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Worktime $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countOfEmployee(int $employeeId): int
    {
        $qb = $this->createQueryBuilder('w')
            ->select('count(w.id)');
        $this->addWhereEmployee($qb, $employeeId);   
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getOfEmployee(int $employeeId, int $page = null): array
    {
        $qb = $this->createQueryBuilder('w');
        $this->addSelectEmployee($qb);
        $this->addSelectProject($qb);
        $this->addOrderByCreation($qb);
        $this->addWhereEmployee($qb, $employeeId);
        $this->addPagination($qb, $page);

        return $qb->getQuery()->getResult();
    }

    public function getOfProject(
        int $projectId, 
        int $page = null, 
        int $pageSize = Worktime::PAGE_SIZE
    ): array {
        $qb = $this->createQueryBuilder('w');
        $this->addSelectEmployee($qb);
        $this->addOrderByCreation($qb);
        $this->addWhereProject($qb, $projectId);
        $this->addPagination($qb, $page, $pageSize);

        return $qb->getQuery()->getResult();
    }

    public function countOfProject(int $projectId): int
    {
        $qb = $this->createQueryBuilder('w')
            ->select('count(w.id)');
        $this->addWhereProject($qb, $projectId);   
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getLatest(int $resultCount = 6): array
    {
        $qb = $this->createQueryBuilder('w')
            ->setMaxResults($resultCount);
        $this->addOrderByCreation($qb);
        $this->addSelectEmployee($qb);
        $this->addSelectProject($qb);

        return $qb->getQuery()->getResult();
    }

    public function getGlobalProductionTime(): int 
    {
        $qb = $this->createQueryBuilder('w')
            ->select('sum(w.daysSpent)');
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    private function addSelectEmployee(QueryBuilder $qb): void
    {
        $qb
            ->addSelect('e')
            ->leftJoin('w.employee', 'e');
    }

    private function addSelectProject(QueryBuilder $qb): void
    {
        $qb
            ->addSelect('p')
            ->leftJoin('w.project', 'p');
    }

    private function addWhereProject(QueryBuilder $qb, int $projectId): void
    {
        $qb
            ->where('w.project = :projectId')
            ->setParameter('projectId', $projectId);
    }

    private function addWhereEmployee(QueryBuilder $qb, int $employeeId): void
    {
        $qb
            ->where('w.employee = :employeeId')
            ->setParameter('employeeId', $employeeId);
    }

    private function addOrderByCreation(QueryBuilder $qb, bool $isDescending = true): void
    {
        $qb
            ->orderBy('w.createdAt', $isDescending ? 'DESC' : 'ASC');
    }

    private function addPagination(QueryBuilder $qb, ?int $page, int $pageSize = Worktime::PAGE_SIZE): void
    {
        if($page == null) return;
        
        $qb
            ->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize);
        
    }
}
