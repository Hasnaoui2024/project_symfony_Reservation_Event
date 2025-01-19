import { registerReactControllerComponents } from '@symfony/ux-react';
import React, { useState } from 'react';
import '../../bootstrap.js'; 
import '../../styles/app.css';
import { createRoot } from 'react-dom/client';
 


// Composant React pour le logo avec animation
const Logo = () => {
    const [hovered, setHovered] = useState(false);

    const handleMouseEnter = () => {
        setHovered(true); // Active l'animation au survol
    };

    const handleMouseLeave = () => {
        setHovered(false); // Désactive l'animation quand la souris quitte
    };

    return (
        <img
            src="/images/logo.webp"
            alt="Reservation Event"
            className={`logo ${hovered ? 'logo-hover' : ''}`} // Ajoute une classe conditionnelle
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
            style={{ cursor: 'pointer' }} // Gérer seulement le curseur
        />
    );
};

// Rendre le composant React dans l'élément avec l'ID 'react-logo'
document.addEventListener('DOMContentLoaded', () => {
    const logoContainer = document.getElementById('react-logo');
    if (logoContainer) {
        const root = createRoot(logoContainer); // Créer un "root" React
        root.render(<Logo />); // Rendre le composant dans le root
    }
});

// Enregistrer les composants React pour Symfony UX
//registerReactControllerComponents(require.context('./react/controllers', true, /\.(j|t)sx?$/));
