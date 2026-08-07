<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
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
                $article->setPhoto($nomImage);
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
            //Execute les requetes et enregistre dans la base
            $entityManager->flush();
            //Je redirige vers la page de l'article crée
            return $this->redirectToRoute('app_article_show',[
                'id'=> $article->getId(),
            ]);
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
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, ArticleRepository $articleRepository): Response
    {
        //Je récupère l'user connecté via abstractController
        $user = $this->getUser();
        //Je fabrique la vue avec les champs liés à l'entité Article
        $form = $this->createForm(ArticleType::class, $article);
        //Je charge les informations saisis dans les POST(hydrate l'objet) et rempli les valeurs des attributs
        $form->handleRequest($request);
        //Je vérifie que l'user connecté est bien l'auteur de l'article
            if($article->getAuteur() !== $user){
                //Si il n'est pas l'auteur de l'article,je redirige vers la page d'accueil avec un message d'erreur
                $message = "Vous n'êtes pas l'auteur de cet article";
                return $this->render('article/index.html.twig',[
                    'message'=>$message,
                    'articles' => $articleRepository->findAll(),
                ]);
            }
        if ($form->isSubmitted() && $form->isValid()) {
            //Je récupère la nouvelle image si modifiée($_FILES)
            $image = $form->get('photo')->getData();
            //Si il y a une nouvelle image
            if ($image) {
                //Je crée le fichier avec un nom unique
                $nomImage = uniqid() . '.' . $image->guessExtension();
                //Je supprime l'ancienne image
                $file = $this->getParameter('images_articles_directory') . '/' . $article->getPhoto();
                //Si le fichier existe on le supprime
                if(file_exists($file)){
                    unlink($file);
                }
                //Je déplace la nouvelle image dans le dossier prévu
                $image->move($this->getParameter("images_articles_directory"),$nomImage);
                //Je valorise l'attribut image avec la nouvelle photo
                $article->setPhoto($nomImage);
            }
            //Je modifie la date_modification
            //Je récupère la date actuelle avec l'objet DateTime
            $dateActuelle = new DateTime();
            $article->setDateModification($dateActuelle);
            //Execute les requetes et enregistre dans la base
            $entityManager->flush();
            //Je redirige vers la page détails de l'article modifié
            return $this->redirectToRoute('app_article_show',[
                'id'=> $article->getId(),
            ]);
        }
        //Sinon j'affiche le formulaire ou formulaire invalide
        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager,ArticleRepository $articleRepository): Response
    {   
        //Je récupère l'user connecté
        $user = $this->getUser();
        //Si l'user connecté n'est pas l'auteurde l'article
        if($article->getAuteur() !== $user){
                //Si il n'est pas l'auteur de l'article,je redirige vers la page d'accueil avec un message d'erreur
                $message = "Vous n'êtes pas l'auteur de cet article";
                return $this->render('article/index.html.twig',[
                    'message'=>$message,
                    'articles' => $articleRepository->findAll(),
                ]);
            }
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/dashboard', name: 'app_liste')]
    public function liste(UserRepository $userRepository, ArticleRepository $articleRepository): Response
    {
    //Récupèrela liste des articles de l'utilisateur connecté
    //Je récupère l'utilisateur connecté
    $user = $this->getUser();
    //Je cherche depuis le repo les infos de l'user connecté(instance chargée)
    $user1 = $userRepository->find($user);
    //Je récupère ses articles via le getters dans User et la relation crée avec article
    $articlesUser = $user1->getArticles();

    /* 
    =======================
    Autre méthode (avec le repo de l'entité Article)
    =========================
    $mesArticles = $articleRepository->findBy([
        'auteur'=>$user,
    ]); */

    //Parmis ses articles je fais le tris entre les articles publiés et non publiés
    //articles publiés
    $articlesPublies =[];
    foreach($articlesUser as $article){
        //Si le champs publie est à 1(récup via le getters de l'entité article)
        if($article->isPublie() == 1){
            $articlesPublies [] = $article;
        }
    }
    //articles non publiés
    $articlesNonPublies = [];
    foreach($articlesUser as $article){
        if($article->isPublie() == 0){
            $articlesNonPublies [] = $article;
        }
    }
    //Je fais le return au template
    return $this->render('article/dashboard.html.twig', [
            'articlesPublies' =>  $articlesPublies,
            'articlesNonPublies' =>  $articlesNonPublies,
        ]);
    }
}
