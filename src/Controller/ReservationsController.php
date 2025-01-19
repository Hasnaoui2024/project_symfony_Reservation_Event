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

    

    #[Route('/event/{eventId}/book', name: 'reservations_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, int $eventId): Response
    {
        // Récupérer l'événement
        $event = $entityManager->getRepository(Events::class)->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        // Vérifier la disponibilité des places
        $availableSeats = $event->getNbrPlace() - $event->getReservations()->count();
        if ($availableSeats <= 0) {
            $this->addFlash('error', 'Désolé, cet événement est complet.');
            return $this->redirectToRoute('event_show', ['id' => $eventId]);
        }

        // Créer une nouvelle réservation
        $reservation = new Reservations();
        $reservation->setEvent($event);

        // Créer le formulaire
        $form = $this->createForm(ReservationsType::class, $reservation, [
            'max_attendees' => $availableSeats, // Passer le nombre de places disponibles
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer la réservation
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réservation a été confirmée !');
            return $this->redirectToRoute('event_show', ['id' => $eventId]);
        }

        return $this->render('reservations/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
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

    // Supprimer la réservation
    $entityManager->remove($reservation);
    $entityManager->flush();

    $this->addFlash('success', 'Votre réservation a été annulée.');
    return $this->redirectToRoute('event_show', ['id' => $reservation->getEvent()->getId()]);
}

#[Route('/reserve/{id}', name: 'reserve_event', methods: ['GET', 'POST'])]
public function reserveEvent(Request $request, Events $event, EntityManagerInterface $entityManager, ReservationsRepository $reservationRepository): Response
{
    // Vérifier si l'utilisateur est connecté
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    // Vérifier la disponibilité des places
    $reservationsCount = $reservationRepository->count(['event' => $event]);
    if ($reservationsCount >= $event->getNbrPlace()) {
        $this->addFlash('error', 'Désolé, cet événement est complet.');
        return $this->redirectToRoute('events_list');
    }

    // Créer une nouvelle réservation
    $reservation = new Reservations();
    $reservation->setEvent($event);
    $reservation->setUser($this->getUser()); // Associer l'utilisateur connecté
    $reservation->setDateReservation(new \DateTime());

    // Enregistrer la réservation
    $entityManager->persist($reservation);
    $entityManager->flush();

    // Ajouter un message flash et un indicateur pour la boîte de dialogue
    $this->addFlash('success', 'Votre réservation a été confirmée !');
    $request->getSession()->set('show_reservation_success', true);

    return $this->redirectToRoute('events_list');
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