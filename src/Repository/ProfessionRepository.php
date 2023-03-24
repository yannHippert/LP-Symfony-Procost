<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\Profession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Profession>
 *
 * @method Profession|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profession|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profession[]    findAll()
 * @method Profession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profession::class);
    }

    public function save(Profession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Profession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getPage(int $page = 1, int $pageSize = Profession::PAGE_SIZE): array
    {
        $idQb = $this->createQueryBuilder('p')
            ->select('p.id');
        $this->addOrderByName($idQb);
        $this->addPagination($idQb, $page, $pageSize);

        $ids = $idQb->getQuery()->getArrayResult();

        $qb = $this->createQueryBuilder('p');
        $this->addSelectEmployees($qb);
        $this->addWhereIdIn($qb, $ids);
        $this->addOrderByName($qb);

        return $qb->getQuery()->getResult();
    }

    public function getById(int $professionId): Profession
    {
        $qb = $this->createQueryBuilder('p');
        $this->addSelectEmployees($qb);
        $this->addOrderByName($qb);
        $this->addWhereProfession($qb, $professionId);

        return $qb->getQuery()->getSingleResult();
    }

    private function addSelectEmployees(QueryBuilder $qb): void
    {
        $qb
            ->addSelect('e')
            ->leftJoin('p.employees', 'e');
    }

    private function addWhereProfession(QueryBuilder $qb, int $professionId): void
    {
        $qb
            ->where('e.profession = :professionId')
            ->setParameter('professionId', $professionId);
    }

    private function addWhereIdIn(QueryBuilder $qb, array $professionIds): void
    {
        $qb
            ->where('p.id IN (:professionIds)')
            ->setParameter('professionIds', $professionIds);
    }

    private function addOrderByName(QueryBuilder $qb, bool $isDescending = false): void
    {
        $qb
            ->orderBy('p.name', $isDescending ? "DESC" : 'ASC');
    }

    private function addPagination(QueryBuilder $qb, ?int $page, int $pageSize = Profession::PAGE_SIZE): void
    {
        if($page == null) return;
        
        $qb
            ->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize);
        
    }
}
