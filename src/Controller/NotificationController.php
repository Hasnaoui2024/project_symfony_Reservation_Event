<?php

// src/Controller/NotificationController.php
namespace App\Controller;

use App\Service\EventReminderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/send-reminders', name: 'send_reminders')]
    public function sendReminders(EventReminderService $reminderService): Response
    {
        // Appeler le service pour envoyer les rappels
        $reminderService->sendReminders();

        return new Response('Les rappels ont été envoyés avec succès !');
    }
}
