<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Je crée mon objet Article vide
        $article = new Article();
        //Je fabrique la vue avec les champs liés à l'entité Article
        $form = $this->createForm(ArticleType::class, $article);
        //Je charge les informations saisis dans les POST(hydrate l'objet) et rempli les valeurs des attributs
        $form->handleRequest($request);
        //Je récupère l'user connecté via abstractController
        $user = $this->getUser();

        
        //Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //Je récupère les infos du post $_FILES, pas de contrainte car photo = null possible donc pas d'obligation à la création d'article
            $image = $form->get("photo")->getData();
            //Si il y a une image
            if($image){
                //Je genere un nom unique pour éviter les doublons
                $nomImage = uniqid().".".$image->guessExtension();
                //Je déplace le fichier vers le dossier prévuy
                $image->move($this->getParameter("images_articles_directory"),$nomImage);
                //Je valorise l'attribut photo de mon article
                $article->setPhoto($image);
            }
            //Je valorise l'attribut auteur
            $article->setAuteur($user);
            //Je récupère la date actuelle avec l'objet DateTime
            $dateActuelle = new DateTime();
            //Je valorise la date de création et modification
            $article->setDateCreation($dateActuelle);
            $article->setDateModification($dateActuelle);
            //Je traite les autres infos ( qui sont directement chargés par symfo et le persist)
            $entityManager->persist($article);
            $entityManager->flush();
            //Execute les requetes et enregistre dans la base
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        //Sinon j'affiche le formulaire vide ou formulaire invalide
        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/detail', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
