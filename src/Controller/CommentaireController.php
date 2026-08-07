<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentaireController extends AbstractController
{
    #[Route('/commentaire', name: 'app_commentaire')]
    public function index(): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'controller_name' => 'CommentaireController',
        ]);
    }

    #[Route('/commentaire/ajouter/{id}', name: 'ajouter_commentaire')]
    public function ajouter(Request $request, EntityManagerInterface $em, Article $article): Response
    {
        //Je récupère l'user connecté
        $user = $this->getUser();
        //Je crée mon objet commentaire vide
        $commentaire = new Commentaire();
        //Je fabrique la vue avec les champs liés à l'entité
        $form = $this->createForm(CommentaireType::class, $commentaire);
        //Je charge les infos saisis dans les POST(hydrade l'objet)
        $form->handleRequest($request);
        //Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //Je valorise l'attribut auteur
            $commentaire->setAuteur($user);
            //Je récupère la date actuelle avec l'objet DateTime
            $dateActuelle = new DateTime();
            //Je valorise la date de création et modification
            $commentaire->setDateCreation($dateActuelle);
            //Je valorise l'article commenté via le setters et la relation avec article
            $commentaire->setArticle($article);
            $em->persist($article);
            //Execute les requetes et enregistre dans la base
            $em->flush();
            //Je redirige vers la page de l'article crée
            return $this->redirectToRoute('app_article_show',[
                'id'=> $article->getId(),
            ]);

        }

        //Sinon j'affiche la page détail article avec le formulaire vide ou invalide
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
}
