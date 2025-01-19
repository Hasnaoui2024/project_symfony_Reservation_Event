<?php
namespace App\Controller;

use App\Repository\EventsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersEventsController extends AbstractController
{
    #[Route('/events', name: 'user_events_index', methods: ['GET'])]
    public function index(EventsRepository $eventsRepository): Response
    {
        // Empêcher l'admin d'accéder à cette page
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_events_index');
        }

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Extraire le nom d'utilisateur (partie avant '@gmail.com')
        $username = 'Guest'; // Par défaut, si aucun utilisateur n'est connecté
        if ($user instanceof \App\Entity\Users) {
            $email = $user->getEmail();
            $username = explode('@', $email)[0];
        }

        // Récupérer tous les événements
        $events = $eventsRepository->findAll();

        return $this->render('users_events/index.html.twig', [
            'events' => $events,
            'username' => $username, // Passer le nom d'utilisateur au template
        ]);
    }
}