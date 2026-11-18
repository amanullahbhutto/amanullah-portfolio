import React, { useState } from 'react';

export default function Navbar({ currentRoute, navigate, theme, toggleTheme }) {
    const [mobileOpen, setMobileOpen] = useState(false);

    const handleLinkClick = (e, route, sectionId = null) => {
        e.preventDefault();
        setMobileOpen(false);
        navigate(route, sectionId);
    };

    return (
        <header className="site-header" id="siteHeader">
            <nav className="navbar navbar-expand-lg" aria-label="Main navigation">
                <div className="container">
                    <a
                        className="navbar-brand brand-mark"
                        href="#home"
                        onClick={(e) => handleLinkClick(e, 'home')}
                        aria-label="Amanullah home"
                    >
                        <span className="brand-symbol">A</span>
                        <span>AMANULLAH<span className="brand-dot">.</span></span>
                    </a>

                    <div className="d-flex align-items-center gap-2 order-lg-3">
                        <button
                            className="theme-toggle"
                            type="button"
                            onClick={toggleTheme}
                            aria-label="Switch colour theme"
                            title="Day / night mode"
                        >
                            <i className="bi bi-sun-fill theme-icon-light"></i>
                            <i className="bi bi-moon-stars-fill theme-icon-dark"></i>
                        </button>

                        <button
                            type="button"
                            onClick={(e) => handleLinkClick(e, 'contact')}
                            className="btn btn-accent d-none d-sm-inline-flex"
                        >
                            Hire me <i className="bi bi-arrow-up-right ms-2"></i>
                        </button>

                        <button
                            className={`navbar-toggler ${mobileOpen ? '' : 'collapsed'}`}
                            type="button"
                            onClick={() => setMobileOpen(!mobileOpen)}
                            aria-controls="mainNav"
                            aria-expanded={mobileOpen}
                            aria-label="Toggle navigation"
                        >
                            <span className="navbar-toggler-icon"></span>
                        </button>
                    </div>

                    <div className={`collapse navbar-collapse ${mobileOpen ? 'show' : ''}`} id="mainNav">
                        <ul className="navbar-nav mx-auto align-items-lg-center">
                            <li className="nav-item">
                                <a
                                    className={`nav-link ${currentRoute === 'home' ? 'active' : ''}`}
                                    href="#home"
                                    onClick={(e) => handleLinkClick(e, 'home')}
                                >
                                    Home
                                </a>
                            </li>
                            <li className="nav-item">
                                <a
                                    className={`nav-link ${currentRoute === 'projects' || currentRoute === 'project-detail' ? 'active' : ''}`}
                                    href="#projects"
                                    onClick={(e) => handleLinkClick(e, 'projects')}
                                >
                                    Projects
                                </a>
                            </li>
                            <li className="nav-item">
                                <a
                                    className={`nav-link ${currentRoute === 'blog' || currentRoute === 'post-detail' ? 'active' : ''}`}
                                    href="#blog"
                                    onClick={(e) => handleLinkClick(e, 'blog')}
                                >
                                    Blog Posts
                                </a>
                            </li>
                            <li className="nav-item">
                                <a
                                    className={`nav-link ${currentRoute === 'services' ? 'active' : ''}`}
                                    href="#services"
                                    onClick={(e) => handleLinkClick(e, 'services')}
                                >
                                    Services
                                </a>
                            </li>
                            <li className="nav-item">
                                <a
                                    className={`nav-link ${currentRoute === 'about' ? 'active' : ''}`}
                                    href="#about"
                                    onClick={(e) => handleLinkClick(e, 'about')}
                                >
                                    About Me
                                </a>
                            </li>
                            <li className="nav-item d-sm-none">
                                <a
                                    className={`nav-link ${currentRoute === 'contact' ? 'active' : ''}`}
                                    href="#contact"
                                    onClick={(e) => handleLinkClick(e, 'contact')}
                                >
                                    Contact
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
    );
}
