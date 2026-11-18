import React from 'react';

export default function Footer({ profile, navigate }) {
    const currentYear = new Date().getFullYear();

    const handleLinkClick = (e, route) => {
        e.preventDefault();
        navigate(route);
    };

    return (
        <footer className="site-footer">
            <div className="container">
                <div className="footer-main row g-5 align-items-start">
                    <div className="col-lg-5">
                        <a
                            className="brand-mark footer-brand"
                            href="#home"
                            onClick={(e) => handleLinkClick(e, 'home')}
                        >
                            <span className="brand-symbol">A</span>
                            <span>AMANULLAH<span className="brand-dot">.</span></span>
                        </a>
                        <p className="footer-summary">
                            {profile?.short_bio || 'PHP and Laravel developer building responsive and maintainable web applications.'}
                        </p>
                        <div className="social-links">
                            {profile?.github_url && (
                                <a href={profile.github_url} target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                                    <i className="bi bi-github"></i>
                                </a>
                            )}
                            {profile?.linkedin_url && (
                                <a href={profile.linkedin_url} target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                    <i className="bi bi-linkedin"></i>
                                </a>
                            )}
                            {profile?.whatsapp_url && (
                                <a className="whatsapp-link" href={profile.whatsapp_url} target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                                    <i className="bi bi-whatsapp"></i>
                                </a>
                            )}
                            <a
                                className="gmail-link"
                                href={profile?.gmail_compose_url || 'https://mail.google.com/mail/?view=cm&fs=1&to=aman.ullah.csc%40gmail.com'}
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Email"
                            >
                                <i className="bi bi-envelope"></i>
                            </a>
                        </div>
                    </div>

                    <div className="col-6 col-lg-2 offset-lg-1">
                        <h2 className="footer-title">Explore</h2>
                        <ul className="footer-links">
                            <li><a href="#projects" onClick={(e) => handleLinkClick(e, 'projects')}>Projects</a></li>
                            <li><a href="#services" onClick={(e) => handleLinkClick(e, 'services')}>Services</a></li>
                            <li><a href="#blog" onClick={(e) => handleLinkClick(e, 'blog')}>Blog</a></li>
                            <li><a href="#about" onClick={(e) => handleLinkClick(e, 'about')}>About</a></li>
                        </ul>
                    </div>

                    <div className="col-6 col-lg-4">
                        <h2 className="footer-title">Contact</h2>
                        <ul className="footer-links footer-contact">
                            <li>
                                <a
                                    className="gmail-link"
                                    href={profile?.gmail_compose_url || 'https://mail.google.com/mail/?view=cm&fs=1&to=aman.ullah.csc%40gmail.com'}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {profile?.email || 'aman.ullah.csc@gmail.com'}
                                </a>
                            </li>
                            {profile?.phone && (
                                <li>
                                    <a
                                        className="whatsapp-link"
                                        href={profile.whatsapp_url || '#'}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <i className="bi bi-whatsapp me-2"></i>{profile.phone}
                                    </a>
                                </li>
                            )}
                            <li>
                                {profile?.city || 'Karachi'}, {profile?.country || 'Pakistan'}
                            </li>
                        </ul>
                    </div>
                </div>

                <div className="footer-bottom d-flex flex-column flex-md-row justify-content-between gap-2">
                    <span>&copy; {currentYear} {profile?.full_name || 'Amanullah'}. All rights reserved.</span>
                </div>
            </div>
        </footer>
    );
}
