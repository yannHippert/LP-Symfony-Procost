<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 *
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function save(Employee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Employee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getPage(int $page = 1, int $pageSize = Employee::PAGE_SIZE): array
    {
        $qb = $this->createQueryBuilder('e');
        $this->addSelectProfession($qb);
        $this->addOrderByEmploymentDate($qb);
        $this->addPagination($qb, $page, $pageSize);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getById(int $employeeId): Employee
    {
        $qb = $this->createQueryBuilder('e');
        $this->addSelectProfession($qb);
        $this->addWhereEmployee($qb, $employeeId);

        return $qb->getQuery()->getSingleResult();
    }

    public function getBest(): Employee
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.worktimes', 'w')
            ->groupBy('e')
            ->orderBy('SUM(w.daysSpent * e.dailySalary)', "DESC");
            
        return $qb->setMaxResults(1)->getQuery()->getSingleResult();
    }

    public function countOfProfession(int $professionId): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('count(e.id)');
        $this->addWhereProfession($qb, $professionId);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getOfProfession(int $professionId, ?int $page): array
    {
        $qb = $this->createQueryBuilder('e');
        $this->addWhereProfession($qb, $professionId);
        $this->addOrderByLastName($qb);
        $this->addPagination($qb, $page);

        return $qb->getQuery()->getResult();
    }

    private function addSelectProfession(QueryBuilder $qb): void
    {
        $qb
            ->addSelect('p')
            ->leftJoin('e.profession', 'p');
    }

    private function addWhereEmployee(QueryBuilder $qb, int $employeeId): void
    {
        $qb
            ->where('e.id = :employeeId')
            ->setParameter('employeeId', $employeeId);
    }

    private function addWhereProfession(QueryBuilder $qb, int $professionId): void
    {
        $qb
            ->where('e.profession = :professionId')
            ->setParameter('professionId', $professionId);
    }

    private function addOrderByLastName(QueryBuilder $qb, bool $isDescending = false): void
    {
        $qb
            ->orderBy('p.lastName', $isDescending ? "DESC" : "ASC");
    }

    private function addOrderByEmploymentDate(QueryBuilder $qb, bool $isDescending = true): void
    {
        $qb
            ->orderBy('e.employmentDate', $isDescending ? "DESC" : "ASC");
    }

    private function addPagination(QueryBuilder $qb, ?int $page, int $pageSize = Employee::PAGE_SIZE): void
    {
        if($page == null) return;

        $qb
            ->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize);
    }

}
