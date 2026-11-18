import React, { useState, useEffect, useCallback } from 'react';
import { fetchPortfolioData } from './api/client';
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import BackToTop from './components/BackToTop';
import HomePage from './pages/HomePage';
import ProjectsPage from './pages/ProjectsPage';
import ProjectDetailPage from './pages/ProjectDetailPage';
import ServicesPage from './pages/ServicesPage';
import AboutPage from './pages/AboutPage';
import BlogPage from './pages/BlogPage';
import PostDetailPage from './pages/PostDetailPage';
import ContactPage from './pages/ContactPage';

export default function App() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Initial theme from localStorage or 'dark'
    const [theme, setTheme] = useState(() => {
        return localStorage.getItem('portfolio-theme') || 'dark';
    });

    // Determine base path (e.g. /amanullah-portfolio/amanullah/public or empty)
    const getBasePath = useCallback(() => {
        const path = window.location.pathname;
        const match = path.match(/^(.*\/public)/);
        return match ? match[1] : '';
    }, []);

    // Parse current URL into route and params
    const parseUrl = useCallback(() => {
        const basePath = getBasePath();
        let pathname = window.location.pathname;
        if (basePath && pathname.startsWith(basePath)) {
            pathname = pathname.substring(basePath.length);
        }
        if (!pathname.startsWith('/')) {
            pathname = '/' + pathname;
        }

        const searchParams = new URLSearchParams(window.location.search);
        const params = {};
        for (const [k, v] of searchParams.entries()) {
            params[k] = v;
        }

        if (pathname === '/' || pathname === '') {
            return { route: 'home', params };
        }
        if (pathname === '/projects') {
            return { route: 'projects', params };
        }
        const projectMatch = pathname.match(/^\/projects\/([^/]+)$/);
        if (projectMatch) {
            return { route: 'project-detail', params: { ...params, slug: projectMatch[1] } };
        }
        if (pathname === '/blog' || pathname === '/posts') {
            return { route: 'blog', params };
        }
        const postMatch = pathname.match(/^\/(?:blog|posts)\/([^/]+)$/);
        if (postMatch) {
            return { route: 'post-detail', params: { ...params, slug: postMatch[1] } };
        }
        if (pathname === '/services') {
            return { route: 'services', params };
        }
        if (pathname === '/about') {
            return { route: 'about', params };
        }
        if (pathname === '/contact') {
            return { route: 'contact', params };
        }

        return { route: 'home', params };
    }, [getBasePath]);

    const [currentRoute, setCurrentRoute] = useState(() => parseUrl().route);
    const [routeParams, setRouteParams] = useState(() => parseUrl().params);

    // Sync theme with document.documentElement
    useEffect(() => {
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('portfolio-theme', theme);
    }, [theme]);

    const toggleTheme = () => {
        setTheme((prev) => (prev === 'dark' ? 'light' : 'dark'));
    };

    // Load initial portfolio data
    useEffect(() => {
        let isMounted = true;
        fetchPortfolioData()
            .then((res) => {
                if (isMounted) {
                    setData(res);
                    setLoading(false);
                }
            })
            .catch((err) => {
                if (isMounted) {
                    console.error('Failed to load portfolio data:', err);
                    setError('Unable to load portfolio details. Please check your database connection.');
                    setLoading(false);
                }
            });

        return () => { isMounted = false; };
    }, []);

    // Handle browser popstate (back / forward navigation)
    useEffect(() => {
        const handlePopState = () => {
            const { route, params } = parseUrl();
            setCurrentRoute(route);
            setRouteParams(params);
        };

        window.addEventListener('popstate', handlePopState);
        return () => window.removeEventListener('popstate', handlePopState);
    }, [parseUrl]);

    // Navigation handler
    const navigate = (route, sectionId = null, params = {}) => {
        const basePath = getBasePath();
        let targetUrl = basePath;

        if (route === 'home') {
            targetUrl += '/';
        } else if (route === 'projects') {
            targetUrl += '/projects';
        } else if (route === 'project-detail') {
            targetUrl += `/projects/${params.slug || ''}`;
        } else if (route === 'blog') {
            targetUrl += '/blog';
        } else if (route === 'post-detail') {
            targetUrl += `/blog/${params.slug || ''}`;
        } else if (route === 'services') {
            targetUrl += '/services';
        } else if (route === 'about') {
            targetUrl += '/about';
        } else if (route === 'contact') {
            targetUrl += '/contact';
            const query = new URLSearchParams();
            if (params.hire) query.set('hire', '1');
            if (params.service) query.set('service', params.service);
            const qStr = query.toString();
            if (qStr) targetUrl += `?${qStr}`;
        }

        if (window.location.pathname !== targetUrl) {
            window.history.pushState(null, '', targetUrl);
        }

        setCurrentRoute(route);
        setRouteParams(params);

        if (sectionId) {
            setTimeout(() => {
                const el = document.querySelector(sectionId);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            }, 100);
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    return (
        <div className="public-body d-flex flex-column min-vh-100">
            <a className="skip-link" href="#main-content">Skip to content</a>

            <Navbar
                currentRoute={currentRoute}
                navigate={navigate}
                theme={theme}
                toggleTheme={toggleTheme}
            />

            <main id="main-content" style={{ overflowX: 'hidden', flex: '1 0 auto' }}>
                {loading ? (
                    <div className="d-flex align-items-center justify-content-center" style={{ minHeight: '65vh' }}>
                        <div className="spinner-border text-accent" role="status" style={{ width: '3rem', height: '3rem' }}>
                            <span className="visually-hidden">Loading Amanullah Portfolio...</span>
                        </div>
                    </div>
                ) : error ? (
                    <div className="container py-5 text-center" style={{ minHeight: '60vh', paddingTop: '150px' }}>
                        <div className="alert alert-danger d-inline-block p-4" style={{ borderRadius: '12px' }}>
                            <i className="bi bi-exclamation-triangle-fill fs-3 text-danger d-block mb-2"></i>
                            <h4>Connection Note</h4>
                            <p className="mb-0">{error}</p>
                        </div>
                    </div>
                ) : (
                    <>
                        {currentRoute === 'home' && (
                            <HomePage data={data} navigate={navigate} />
                        )}

                        {currentRoute === 'projects' && (
                            <ProjectsPage initialProjects={data?.projects} navigate={navigate} />
                        )}

                        {currentRoute === 'project-detail' && (
                            <ProjectDetailPage slug={routeParams?.slug} navigate={navigate} />
                        )}

                        {currentRoute === 'services' && (
                            <ServicesPage initialServices={data?.services} navigate={navigate} />
                        )}

                        {currentRoute === 'about' && (
                            <AboutPage
                                profile={data?.profile}
                                experiences={data?.experiences}
                                educations={data?.educations}
                                skills={data?.skills}
                                navigate={navigate}
                            />
                        )}

                        {currentRoute === 'blog' && (
                            <BlogPage initialPosts={data?.posts} navigate={navigate} />
                        )}

                        {currentRoute === 'post-detail' && (
                            <PostDetailPage slug={routeParams?.slug} navigate={navigate} />
                        )}

                        {currentRoute === 'contact' && (
                            <ContactPage profile={data?.profile} initialParams={routeParams} />
                        )}
                    </>
                )}
            </main>

            <Footer profile={data?.profile} navigate={navigate} />

            <BackToTop />
        </div>
    );
}
