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

    public function getPage(int $page): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.id', 'ASC')
            ->addSelect('p')
            ->leftJoin('e.profession', 'p')
            ->setMaxResults(Employee::PAGE_SIZE)
            ->setFirstResult(($page - 1) * Employee::PAGE_SIZE);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getById(int $id): Employee
    {
        return $this->createQueryBuilder('e')
            ->addSelect('p')
            ->leftJoin('e.profession', 'p')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    public function getBest() 
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.worktimes', 'w')
            ->groupBy('e')
            ->orderBy('SUM(w.daysSpent * e.dailySalary)', "DESC")
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    public function getOfProfession(int $professionId, ?int $page): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.profession = :professionId')
            ->setParameter('professionId', $professionId)
            ->orderBy('e.lastName', 'ASC');
        
        $this->addPagination($qb, $page);

        return $qb
            ->getQuery()
            ->getResult();
    }

    private function addPagination(QueryBuilder $qb, ?int $page): void
    {
        if($page != null) {
            $qb
                ->setMaxResults(Employee::PAGE_SIZE)
                ->setFirstResult(($page - 1) * Employee::PAGE_SIZE);
        }
    }

}
