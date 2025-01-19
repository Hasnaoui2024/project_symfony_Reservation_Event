<?php

namespace App\Controller\Admin;

use App\Entity\Events;
use App\Entity\Reservations;
use App\Form\EventsType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/events')]
#[IsGranted("ROLE_ADMIN")]
final class EventsController extends AbstractController
{
    #[Route('/admin_events', name: 'admin_events_index', methods: ['GET'])]
public function index(Request $request, EventsRepository $eventsRepository): Response
{
    // Récupérer le terme de recherche depuis la requête
    $search = $request->query->get('search');

    // Récupérer les événements filtrés par titre
    $events = $search ? $eventsRepository->findByTitle($search) : $eventsRepository->findAll();

    return $this->render('admin/events/index.html.twig', [
        'events' => $events,
    ]);
}

    #[Route('/new', name: 'admin_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Events();
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été créé avec succès.');

            return $this->redirectToRoute('admin_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/events/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_events_show', methods: ['GET'])]
    public function show(Events $event): Response
    {
        return $this->render('admin/events/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été modifié avec succès.');

            return $this->redirectToRoute('admin_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/events/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_events_delete', methods: ['POST'])]
public function delete(Request $request, Events $event, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
        // Récupérer toutes les réservations associées à cet événement
        $reservations = $entityManager->getRepository(Reservations::class)->findBy(['event' => $event]);

        // Supprimer toutes les réservations associées
        foreach ($reservations as $reservation) {
            $entityManager->remove($reservation);
        }

        // Supprimer l'événement
        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'L\'événement et ses réservations associées ont été supprimés avec succès.');
    } else {
        $this->addFlash('error', 'Token CSRF invalide.');
    }

    return $this->redirectToRoute('admin_events_index', [], Response::HTTP_SEE_OTHER);
}

#[Route('/{id}/reservations', name: 'admin_events_reservations', methods: ['GET'])]
public function eventReservations(Events $event, Request $request): Response
{
    // Récupérer le terme de recherche depuis l'URL (paramètre GET 'search')
    $search = $request->query->get('search');

    // Récupérer toutes les réservations pour cet événement
    $reservations = $event->getReservations();

    // Filtrer les réservations si un terme de recherche est fourni
    if ($search) {
        $reservations = $reservations->filter(function($reservation) use ($search) {
            // Vérifier si l'email de l'utilisateur contient le terme de recherche
            return stripos($reservation->getUser()->getEmail(), $search) !== false;
        });
    }

    // Passer les réservations filtrées au template Twig
    return $this->render('admin/events/reservations.html.twig', [
        'event' => $event,
        'reservations' => $reservations, // Assurez-vous que cette ligne est présente
    ]);
}
}