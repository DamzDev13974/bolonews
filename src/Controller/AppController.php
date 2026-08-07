<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            //Je récupère et envoi au template les 6 derniers articles
            'articles' => $articleRepository->findForLast(),
            //Je récupère et envoi au template le dernier article
            'une'=>$articleRepository->findForUne(),
        ]);
    }

    #[Route('/rechercher', name: 'app_recherche', methods: ['GET']) ]
    public function rechercher(ArticleRepository $articleRepository, Request $Request): Response
    {
        //Je récupère le mot saisie pour la recherche
        $word= trim($Request->query->get("search"));
        //Si un mot a été tapé et validé dans la recherche
        if($word){
            //Je fais une recherche filtrée par la méthode crée dans le repo d'Article
            $articles = $articleRepository->findByWord($word);
            //Je renvoi sur la page Articles avec les résultats de la recherche
            return $this->render('article/recherche.html.twig', [
                "articles"=>$articles,
                "search"=>$word,
            ]);
        }

        //Sinon j'affiche la page Articles avec un message générique
        $message = "Veuillez saisir votre mot clé pour la recherche ";
        return $this->render('article/recherche.html.twig',[
            "message"=>$message,
        ]);
    }
}
