# Projet Symfony de Gestion d'Événements

## Description du Projet

Ce projet est une application web de gestion d'événements développée avec le framework Symfony. Il permet aux utilisateurs de consulter des événements, de réserver des billets et de gérer leurs réservations. Les administrateurs peuvent créer, modifier et supprimer des événements, ainsi que gérer les réservations des utilisateurs.

## Fonctionnalités Principales

### Utilisateurs

- **Inscription et Connexion** : Les utilisateurs peuvent s'inscrire et se connecter pour accéder aux fonctionnalités de l'application.
- **Consultation des Événements** : Les utilisateurs peuvent consulter la liste des événements disponibles.
- **Réservation de Billets** : Les utilisateurs peuvent réserver des billets pour les événements.
- **Gestion des Réservations** : Les utilisateurs peuvent consulter et annuler leurs réservations.

### Administrateurs

- **Gestion des Événements** : Les administrateurs peuvent créer, modifier et supprimer des événements.
- **Gestion des Réservations** : Les administrateurs peuvent consulter et gérer les réservations des utilisateurs.

### Sécurité

- **Gestion des Rôles** : Les rôles `ROLE_USER` et `ROLE_ADMIN` sont utilisés pour gérer les permissions des utilisateurs et des administrateurs.
- **Protection CSRF** : Les formulaires sont protégés contre les attaques CSRF.
