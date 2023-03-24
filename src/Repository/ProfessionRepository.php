<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\Profession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function getPage(int $page): array
    {
        $ids = $this->createQueryBuilder('p')
            ->select('p.id')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(Profession::PAGE_SIZE)
            ->setFirstResult(($page - 1) * Profession::PAGE_SIZE)
            ->getQuery()
            ->getArrayResult();

        $qb = $this->createQueryBuilder('p')
            ->addSelect("e")
            ->leftJoin('p.employees', 'e')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('p.name', 'ASC');

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getById(int $id): Profession
    {
        return $this->createQueryBuilder('p')
            ->addSelect('e')
            ->leftJoin('p.employees', 'e')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->orderBy('p.name', 'DESC')
            ->getQuery()
            ->getSingleResult();
    }

    public function countEmployees(int $id): int
    {
        return $this->_em->createQueryBuilder()
            ->select('count(e.id)')
            ->from(Employee::class, 'e')
            ->where('e.profession = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
