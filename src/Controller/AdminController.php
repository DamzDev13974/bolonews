<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route( name: 'app_admin_liste')]
    public function index(UserRepository $userRepository, ArticleRepository $articleRepository): Response
    {
        /* =============Partie pour usage similaire à role_user=================== */
        //Je récupère les articles de l'admin pour la section comme un user co classique
        //Je récupère l'utilisateur connecté
        $user = $this->getUser();
        //Je récupère les articles
        $articlesUser = $articleRepository->findBy([
            'auteur'=>$user,
        ]);

        //Parmis ses articles je fais le tris entre les articles publiés et non publiés
        //articles publiés
        $articlesPublies =[];
        foreach($articlesUser as $article){
            //Si le booleen publie est à 1(true)(récup via le getters de l'entité article)
            if($article->isPublie()){
                $articlesPublies [] = $article;
            }
        }
        //articles non publiés
        $articlesNonPublies = [];
        foreach($articlesUser as $article){
            //Si le booleen publie est à 0(false)(récup via le getters de l'entité article)
            if(!$article->isPublie()){
                $articlesNonPublies [] = $article;
            }
        }

        /* ==========================Partie avec les droits admin ===================== */
        //Je récupère la liste de tout les articles
        $articles = $articleRepository->findAll();
        //Je récupère la liste de tout les utilisateurs crées
        $users = $userRepository->findAll();


        //Je fait le return au template
        return $this->render('admin/index.html.twig', [
            'articlesPublies' =>  $articlesPublies,
            'articlesNonPublies' =>  $articlesNonPublies,
            'articles' => $articles,
            'users' => $users,
        ]);
    }
}
