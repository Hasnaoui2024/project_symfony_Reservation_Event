<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\EventsRepository; // Ajoutez cette ligne

#[Route('/admin')]
#[IsGranted("ROLE_ADMIN")]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EventsRepository $eventsRepository): Response // Injectez EventsRepository
    {
        // Récupérer tous les événements
        $events = $eventsRepository->findAll();

        // Afficher le tableau de bord admin avec la liste des événements
        return $this->render('admin/events/index.html.twig', [
            'events' => $events, // Passez la variable `events` au template
        ]);
    }
}