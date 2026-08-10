<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route( name: 'app_admin_liste')]
    public function index(UserRepository $userRepository, ArticleRepository $articleRepository): Response
    {
        /* =================================
        Role : affiche le dashboard de l'admin connecté avec ses articles publiés et non publiés, tout les articles et les users
        ====================================*/

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

    #[Route('/{id}/edit', name: 'app_admin_article_edit')]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        /* =================================
        Role : permet à l'admin de modifier un article
        ====================================*/

        //Je fabrique la vue avec les champs liés à l'entité Article
        $form = $this->createForm(ArticleType::class, $article);
        //Je charge les informations saisis dans les POST(hydrate l'objet) et rempli les valeurs des attributs
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //Je récupère la nouvelle image si modifiée($_FILES)
            $image = $form->get('photo')->getData();
            //Si il y a une nouvelle image
            if ($image) {
                //Je crée le fichier avec un nom unique
                $nomImage = uniqid() . '.' . $image->guessExtension();
                //Si l'article a déjà  une image
                if($article->getPhoto()){
                    //Je supprime l'ancienne image
                    $file = $this->getParameter('images_articles_directory') . '/' . $article->getPhoto();
                    //Si le fichier existe on le supprime
                    if(file_exists($file)){
                        unlink($file);
                    }
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

    #[Route('/{id}/delete', name: 'app__admin_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {   
        /* =================================
        Role : permet à l'admin de supprimer un article
        ====================================*/
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/user/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        /* =================================
        Role : permet à l'admin de supprimer un user
        ====================================*/

        //Je reprends le même fonctionnement que pour la suppression d'un article 
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_liste', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/{id}/ban', name: 'app_ban_user')]
    public function bannir(Request $request, User $user, EntityManagerInterface $em): Response
    {   
        /* =================================
        Role : permet à l'admin de bannir ou débannir un user
        ====================================*/

        //Si l'user n'est pas banni, je ban (passe son champ "banni" à 1)
            if(!$user->isBanni()){
                $user->setBanni(true);
            }else{
        //Sinon je déban (passe son champ "banni" à 0)
                $user->setBanni(false);
            }
        
        //J'enregistre les modification 
        $em->flush();

        //Je redirige vers le dashboard ADMIN
        return $this->redirectToRoute('app_admin_liste');
    }

    #[Route('/{id}/publier', name: 'app_publier')]
    public function publier(EntityManagerInterface $em, Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        /* =================================
        Role : permet à l'admin de publier ou dépublier un article
        ====================================*/

        //Si l'article est publié, je dépublie
        if($article->isPublie()){
            $article->setPublie(false);
        }else{
            $article->setPublie(true);
        }
        //J'enregistre les modification 
        $em->flush();
        //Je redirige vers la page détails de l'article modifié
        return $this->redirectToRoute('app_article_show',[
            'id'=> $article->getId(),
        ]);
    }
}
