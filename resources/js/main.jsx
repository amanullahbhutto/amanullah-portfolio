import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';

const rootElement = document.getElementById('root');

if (rootElement) {
    const root = ReactDOM.createRoot(rootElement);
    root.render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}

// Initialize AOS animations if loaded
if (typeof window !== 'undefined' && window.AOS) {
    window.AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
}
