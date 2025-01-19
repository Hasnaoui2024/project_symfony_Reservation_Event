<?php
namespace App\Service;

use App\Entity\Event;
use App\Message\NotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Events;
use Symfony\Component\Messenger\MessageBusInterface;

class EventReminderService
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    public function sendReminders(): void
    {
        // Récupérer les événements à venir
        $events = $this->entityManager->getRepository(Events::class)->findUpcomingEvents();

        foreach ($events as $event) {
            $users = $event->getUsers(); // Supposons que vous avez une relation entre Event et User
            foreach ($users as $user) {
                $message = new NotificationMessage(
                    $user->getEmail(),
                    'Rappel : ' . $event->getName(),
                    'L\'événement "' . $event->getName() . '" commence bientôt.'
                );
                $this->messageBus->dispatch($message);
            }
        }
    }
}