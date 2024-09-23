<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    //    /**
    //     * @return Book[] Returns an array of Book objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $query = $this->createQueryBuilder('b');

        if (!empty($criteria['author'])) {
            $query->andWhere('b.author = :author')
                ->setParameter('author', $criteria['author']->getId());
        }

        if (!empty($criteria['title'])) {
            $query->andWhere('b.title LIKE :title')
                ->setParameter('title', '%'.$criteria['title'].'%');
        }

        return $query->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->execute();
    }

    public function countBooks()
    {
        $qb = $this->createQueryBuilder('b');
        $qb->select('count(b.id)');
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getBooks($limit = null, $offset = null): array
    {
        $qb = $this->createQueryBuilder('b')
            ->orderBy('b.id', 'ASC');

        if (false === is_null($offset))
            $qb->setFirstResult($offset);

        if (false === is_null($limit))
            $qb->setMaxResults($limit);
        
        return $qb->getQuery()
            ->getResult();
    }
}
