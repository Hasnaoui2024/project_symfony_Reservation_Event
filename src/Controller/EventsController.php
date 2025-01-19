<?php
// src/Controller/EventsController.php
namespace App\Controller;

use App\Entity\Events;
use App\Entity\Reservations;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/events')]
#[IsGranted("ROLE_USER")]
class EventsController extends AbstractController
{
   

    #[Route('/{id}/reserve', name: 'app_events_reserve', methods: ['POST'])]
    public function reserve(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur a déjà réservé cet événement
        $existingReservation = $entityManager->getRepository(Reservations::class)->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if ($existingReservation) {
            $this->addFlash('error', 'Vous avez déjà réservé cet événement.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        // Vérifier s'il reste des places disponibles
        if ($event->getNbrPlace() <= 0) {
            $this->addFlash('error', 'Désolé, il n\'y a plus de places disponibles pour cet événement.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        // Créer une nouvelle réservation
        $reservation = new Reservations();
        $reservation->setEvent($event);
        $reservation->setUser($user);
        $reservation->setDateReservation(new \DateTime());

        $entityManager->persist($reservation);

        // Mettre à jour le nombre de places disponibles
        $event->setNbrPlace($event->getNbrPlace() - 1);

        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été effectuée avec succès.');
        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }

    #[Route('/{id}/cancel', name: 'app_events_cancel', methods: ['POST'])]
    public function cancel(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Trouver la réservation de l'utilisateur pour cet événement
        $reservation = $entityManager->getRepository(Reservations::class)->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if (!$reservation) {
            $this->addFlash('error', 'Vous n\'avez pas de réservation pour cet événement.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        // Supprimer la réservation
        $entityManager->remove($reservation);

        // Mettre à jour le nombre de places disponibles
        $event->setNbrPlace($event->getNbrPlace() + 1);

        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été annulée avec succès.');
        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }

    #[Route('/events', name: 'app_events_index', methods: ['GET'])]
public function index(EventsRepository $eventsRepository): Response
{
    // Récupérer tous les événements
    $events = $eventsRepository->findAll();

    // Afficher la liste des événements
    return $this->render('events/index.html.twig', [
        'events' => $events,
    ]);
}
}