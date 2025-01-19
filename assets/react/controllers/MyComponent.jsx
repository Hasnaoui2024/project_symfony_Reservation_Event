// assets/react/controllers/MyComponent.jsx
import React, { useEffect, useState } from 'react';

const MyComponent = () => {
    const [username, setUsername] = useState('');

    useEffect(() => {
        const rootElement = document.getElementById('root');
        if (rootElement) {
            const username = rootElement.getAttribute('data-username');
            setUsername(username);
        }
    }, []);

    return (
        <div>
            <h1>Bonjour, {username} !</h1>
            <p>Nous espérons que vous passez une bonne journée.</p>
        </div>
    );
};

export default MyComponent;