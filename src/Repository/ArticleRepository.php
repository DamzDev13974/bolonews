<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
        * @return Article[] Returns an array of Article objects
        */
        public function findByWord($word): array
        {
            return $this->createQueryBuilder('a')
                ->leftJoin('a.categorie','c')//Jointure de la table Catégorie
                ->Where('a.titre LIKE :val')//Permettant la recherche dans le champ tritre de l'Article avec la valeur du $word
                ->orWhere('a.chapeau = :val')//Dans le champs chapeau de l'Article avec la valeur du $word
                ->orWhere('c.libelle LIKE :val')//Dans le champs libellé des Categorie avec la valeur du $word
                ->setParameter('val', '%' . $word . '%') //parametres de la requete
                ->orderBy('a.titre', 'ASC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult()
            ;
        }
        //Retourner le dernier article publié
        public function findForUne(): array
        {
            return $this->createQueryBuilder('a')
                ->Where('a.publie = :publie')//Permettant la recherche dans le champ  publie = true ou 1
                ->setParameter('publie', true)//parametres du champs publie = true
                ->orderBy('a.date_creation', 'DESC')//trié par date de création en descendant
                ->setMaxResults(1)
                ->getQuery()
                ->getResult()
            ;
        }

        //Retourner les 6 derniers articles publiés
        public function findForLast(): array
        {
            return $this->createQueryBuilder('a')
                ->Where('a.publie = :publie')//Permettant la recherche dans le champ  publie = true ou 1
                ->setParameter('publie', true)//parametres du champs publie = true
                ->orderBy('a.date_creation', 'DESC')//trié par date de création en descendant
                ->setMaxResults(4)
                ->getQuery()
                ->getResult()
            ;
        }
}
