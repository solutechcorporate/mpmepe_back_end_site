<?php

namespace App\Repository;

use App\Entity\Dirigeant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dirigeant>
 *
 * @method Dirigeant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dirigeant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dirigeant[]    findAll()
 * @method Dirigeant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DirigeantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dirigeant::class);
    }

//    /**
//     * @return Dirigeant[] Returns an array of Dirigeant objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Dirigeant
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
