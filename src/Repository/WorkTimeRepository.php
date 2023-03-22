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
        return $this->_em->createQueryBuilder()
            ->select('count(w.id)')
            ->from(Worktime::class, 'w')
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
            ->setMaxResults(Worktime::PAGE_SIZE)
            ->setFirstResult(($page - 1) * Worktime::PAGE_SIZE)
            ->getQuery()
            ->getResult();
    }

    public function getOfEmployee(int $employeeId, int $page = null): array
    {
        $qb = $this->createQueryBuilder('w')
            ->addSelect('p')
            ->addSelect('e')
            ->leftJoin('w.project', 'p')
            ->leftJoin('w.employee', 'e')
            ->orderBy('w.createdAt', 'DESC');
            
        $this->addWhereEmployee($qb, $employeeId);
        $this->addPagination($qb, $page);

        return $qb 
                ->getQuery()
                ->getResult();
    }

    public function getOfProject(int $projectId, int $page = null): array
    {
        $qb = $this->createQueryBuilder('w')
            ->addSelect('e')
            ->leftJoin('w.employee', 'e');

        $this->addWhereProject($qb, $projectId);
        $this->addPagination($qb, $page);

        return $qb 
            ->getQuery()
            ->getResult();
    }

    public function countOfProject(int $projectId): int
    {
        return $this->_em->createQueryBuilder()
            ->select('count(w.id)')
            ->from(Worktime::class, 'w')
            ->where('w.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getLatest(int $resultCount = 6): array
    {
        return $this->createQueryBuilder('w')
            ->addSelect('p')
            ->addSelect('e')
            ->leftJoin('w.project', 'p')
            ->leftJoin('w.employee', 'e')
            ->orderBy('w.createdAt', 'DESC')
            ->setMaxResults($resultCount)
            ->getQuery()
            ->getResult();
    }

    public function getGlobalProductionTime(): int 
    {
        return $this->_em->createQueryBuilder()
            ->select('sum(w.daysSpent)')
            ->from(Worktime::class, 'w')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function addPagination(QueryBuilder $qb, ?int $page): void
    {
        if($page != null) {
            $qb
                ->setMaxResults(Worktime::PAGE_SIZE)
                ->setFirstResult(($page - 1) * Worktime::PAGE_SIZE);
        }
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

}
