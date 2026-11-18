import React, { useState, useEffect } from 'react';

export default function BackToTop() {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const toggleVisibility = () => {
            if (window.scrollY > 300) {
                setVisible(true);
            } else {
                setVisible(false);
            }
        };

        window.addEventListener('scroll', toggleVisibility, { passive: true });
        return () => window.removeEventListener('scroll', toggleVisibility);
    }, []);

    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    return (
        <button
            className={`back-to-top ${visible ? 'active' : ''}`}
            type="button"
            onClick={scrollToTop}
            aria-label="Back to top"
            style={{ display: visible ? 'flex' : 'none' }}
        >
            <i className="bi bi-arrow-up"></i>
        </button>
    );
}
