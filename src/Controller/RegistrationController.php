<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        //Je crée mon objet User vide
        $user = new User();
        //Je fabrique la vue avec les champs liés à l'entité User
        $form = $this->createForm(RegistrationFormType::class, $user);
        //Je charge les informations saisis dans les POST(hydrate l'objet) et rempli les valeurs des attributs
        $form->handleRequest($request);

        /* ===================================
            UPLOAD IMAGE
        =====================================
        */
        //Si l'affichage provient d'un post, j'affichage les erreurs possibles
        if($request->isMethod('POST')){
            //Pour récuperer l'image (fichier =file)
            //1)On récupère les infos du post file ($_FILES)
            $image= $form->get("photo")->getData();
            //On souhaite que l'image soit obligatoire à la création du produit
            if(!$image){
                //Autre controle de sécurité en plus des contraintes dans le formulaire)
                $form->get("photo")->addError(new FormError("l'image est obligatoire !"));
            }else{
                //2)On reconstruit le nom de l'image (pour éviter les doublons)
                $nomImage = uniqid().".".$image->guessExtension();// .jpg , .png etc
                //3)On déplace le fichier vers le dossier prévu
                $image->move($this->getParameter("images_directory"),$nomImage);
                //4) Je rempli l'attribut photo de mon Product
                $user->setPhoto($nomImage);
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
           //Je récupère le post du mdp
            $plainPassword = $form->get('plainPassword')->getData();
            //Je hache le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            //Je traite les autres infos ( qui sont directement chargés par symfo et le persist)
            $entityManager->persist($user);
            //Execute les requetes et enregistre dans la base
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
