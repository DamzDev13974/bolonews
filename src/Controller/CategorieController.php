<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }

    #[Route('/categorie/ajouter', name: 'ajouter_categorie')]
    public function ajouter(Request $Request, EntityManagerInterface $em): Response
    {
        /* =================================
        Role : permet d'ajouter une nouvelle catégorie d'article
        ====================================*/

        //Je récupère l'user connecté
        $user = $this->getUser();
        //Je crée mon objet categorie vide
        $categorie = new Categorie();
        //Je fabrique la vue avec les champs liés à l'entité
        $form = $this->createForm(CategorieType::class, $categorie);
        //Je charge les infos saisis dans les POST(hydrade l'objet)
        $form->handleRequest($Request);
        //Si le formulaire est posté et valide
        if ($form->isSubmitted() && $form->isValid()){
            //Je traite les infos récup par l'hydration sur l'objet crée
            $em->persist($categorie);
            //J'execute les requetes et enregistre dans la base
            $em->flush();
            //Je redirige
            return $this->redirectToRoute('app_categorie');
        }
        //Sinon j'affiche le formulaire vide ou formulaire invalide
        return $this->render('categorie/form.html.twig',[
            "form"=>$form
        ]);
    }

    
}
