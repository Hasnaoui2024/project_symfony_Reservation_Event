<?php
namespace App\Controller;

use App\Entity\Events;
use App\Enum\Statut;
use App\Form\ReservationsType;
use App\Entity\Reservations;
use App\Repository\EventsRepository;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationsController extends AbstractController
{
    #[Route('/events', name: 'events_list', methods: ['GET'])]
public function listEvents(Request $request, EventsRepository $eventRepository): Response
{
    // Récupérer le terme de recherche depuis la requête
    $search = $request->query->get('search');

    // Récupérer les événements filtrés par titre
    $events = $search ? $eventRepository->findByTitle($search) : $eventRepository->findAll();

    return $this->render('reservations/events.html.twig', [
        'events' => $events,
    ]);
}

    #[Route('/event/{id}', name: 'event_show', methods: ['GET'])]
public function showEvent(Events $event): Response
{
    return $this->render('reservations/show.html.twig', [
        'event' => $event,
    ]);
}

#[Route('/cancel-reservation/{id}', name: 'cancel_reservation', methods: ['POST'])]
public function cancelReservation(Reservations $reservation, EntityManagerInterface $entityManager): Response
{
    // Vérifier que l'utilisateur connecté est bien celui qui a fait la réservation
    if ($reservation->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette réservation.');
    }

    // Récupérer l'événement lié à la réservation
    $event = $reservation->getEvent();

    // Incrémenter le nombre de places disponibles
    $event->setNbrPlace($event->getNbrPlace() + 1);

    // Supprimer la réservation
    $entityManager->remove($reservation);
    $entityManager->persist($event);
    $entityManager->flush();

    $this->addFlash('success', 'Votre réservation a été annulée.');
    return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
}

    #[Route('/event/{id}/reserve', name: 'reserve_event', methods: ['POST'])]
    public function reserveEvent(Request $request, Events $event, EntityManagerInterface $entityManager, ReservationsRepository $reservationRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Nombre de places restantes (déjà mis à jour après chaque réservation)
        $availableSeats = $event->getNbrPlace();

        // Vérifier combien de réservations l'utilisateur a déjà faites
        $userReservationsCount = $reservationRepository->count([
            'user' => $this->getUser(),
            'event' => $event
        ]);

        // Récupérer le nombre de billets demandés depuis le formulaire
        $requestedTickets = (int) $request->request->get('ticket_count', 1);

        // Déterminer la limite de réservation (max 3 ou le nombre de places disponibles)
        $maxReservable = ($event->getNbrPlace() > 3) ? 3 : $event->getNbrPlace();

        // Vérifier que l'utilisateur ne dépasse pas la limite autorisée
        if ($requestedTickets > $maxReservable) {
            $this->addFlash('error', "Vous ne pouvez pas réserver plus de $maxReservable billets au total pour cet événement.");
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        // Vérifier si le nombre de billets demandés est disponible
        if ($requestedTickets > $availableSeats) {
            $this->addFlash('error', 'Il n\'y a pas assez de places disponibles.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        // Créer plusieurs réservations en fonction du nombre de billets demandés
        for ($i = 0; $i < $requestedTickets; $i++) {
            $reservation = new Reservations();
            $reservation->setEvent($event);
            $reservation->setUser($this->getUser());
            $reservation->setDateReservation(new \DateTime());

            $entityManager->persist($reservation);
        }

        // Mettre à jour le nombre de places disponibles
        $event->setNbrPlace($event->getNbrPlace() - $requestedTickets);

        // Sauvegarder en base de données
        $entityManager->persist($event);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été confirmée !');

        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }







#[Route('/clear-reservation-success', name: 'clear_reservation_success', methods: ['POST'])]
public function clearReservationSuccess(Request $request): Response
{
    // Effacer l'indicateur de la session
    $request->getSession()->remove('show_reservation_success');

    // Retourner une réponse vide (204 No Content)
    return new Response(null, Response::HTTP_NO_CONTENT);
}

#[Route('/admin/reservation/{id}/update-status', name: 'admin_reservation_update_status', methods: ['POST'])]
public function updateReservationStatus(Request $request, Reservations $reservation, EntityManagerInterface $entityManager): Response
{
    // Vérifier que l'utilisateur est un admin
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Récupérer le nouveau statut depuis la requête
    $newStatus = $request->request->get('status');

    // Vérifier que le statut est valide
    if (!in_array($newStatus, [Statut::PENDING->value, Statut::CANCELLED->value])) {
        $this->addFlash('error', 'Statut invalide.');
        return $this->redirectToRoute('admin_events_reservations', ['id' => $reservation->getEvent()->getId()]);
    }

    // Mettre à jour le statut de la réservation
    $reservation->setStatut(Statut::from($newStatus));
    $entityManager->flush();

    $this->addFlash('success', 'Le statut de la réservation a été mis à jour.');
    return $this->redirectToRoute('admin_events_reservations', ['id' => $reservation->getEvent()->getId()]);
}



}