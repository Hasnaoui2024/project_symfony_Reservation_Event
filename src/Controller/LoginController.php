<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * Affiche et traite le formulaire de connexion.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, redirigez-le en fonction de son rôle
        /** @var \App\Entity\Users $user */
        $user = $this->getUser();
    
        if ($user) {
            $email = $user->getEmail(); // L'IDE reconnaîtra maintenant que $user est une instance de Users
            $username = explode('@', $email)[0]; // Récupère la partie avant '@gmail.com'
    
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard'); // Redirection vers le tableau de bord admin
            } else {
                return $this->redirectToRoute('user_events_index'); // Redirection pour les utilisateurs simples
            }
        }
    
        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
    
        // Dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();
    
        // Afficher la page de connexion
        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    /**
     * Gère la déconnexion.
     * Cette méthode peut être vide, elle sera interceptée par le firewall.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut être vide - elle sera interceptée par le firewall.');
    }
}