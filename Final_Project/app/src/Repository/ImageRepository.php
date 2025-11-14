<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Image>
 *
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function findPublicImages()
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.isPublic = :public')
            ->setParameter('public', true)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser($user): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.user = :user')
            ->setParameter('user', $user)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function findByKeywords(string $query): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $words = preg_split('/[-\s,.;:!?()"\']+/', mb_strtolower($query));

        if (!$words) {
            return [];
        }

        $conditions = [];
        $params = [];
        foreach ($words as $i => $word) {
            $conditions[] = "EXISTS (
            SELECT 1
            FROM jsonb_array_elements_text(i.keywords::jsonb) AS kw
            WHERE lower(kw) = :word$i
        )";
            $params["word$i"] = $word;
        }

        $sql = "SELECT id FROM image i WHERE " . implode(' OR ', $conditions);

        $result = $conn->executeQuery($sql, $params);

        $ids = array_column($result->fetchAllAssociative(), 'id');

        if (!$ids) {
            return [];
        }

        $images = $this->getEntityManager()->getRepository(\App\Entity\Image::class)
            ->findBy(['id' => $ids]);

        usort($images, function($a, $b) use ($words) {
            $aKeywords = array_map('mb_strtolower', $a->getKeywords());
            $bKeywords = array_map('mb_strtolower', $b->getKeywords());

            $aCount = count(array_intersect($words, $aKeywords));
            $bCount = count(array_intersect($words, $bKeywords));

            return $bCount <=> $aCount;
        });

        return $images;
    }




    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}
