<?php

namespace App\Repository;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use RandomLib\Factory;
use RandomLib\Generator;

/**
 * @method ApiToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiToken[]    findAll()
 * @method ApiToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    private Generator $generator;

    public function __construct(ManagerRegistry $registry/*, Factory $factory*/) {
        parent::__construct($registry, ApiToken::class);
//        /$this->generator = $factory->getLowStrengthGenerator();
    }

    public function findUserByToken(string $token): ?User {
        $token = $this->findOneBy(["token"=>$token]);

        return $token?->getUser();
    }

    /**
     * @throws \Exception
     */
    public function generateToken(int $length = 8): string {
        do{
            $token = md5(random_bytes($length));
        }while($this->tokenExists($token));

        return $token;
    }

    private function tokenExists(string $token): bool {
        $q = $this->createQueryBuilder('p')
            ->where('p.token = :token')
            ->setParameter('token',$token)
            ->setMaxResults(1)
            ->getQuery()->getResult();

        return sizeof($q) > 0;
    }

    // /**
    //  * @return ApiToken[] Returns an array of ApiToken objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ApiToken
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
