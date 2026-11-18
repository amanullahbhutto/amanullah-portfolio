import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import AdminSidebar from './components/AdminSidebar';
import AdminHeader from './components/AdminHeader';
import AdminDashboard from './pages/AdminDashboard';
import ContentPage from './pages/ContentPage';
import MessagesPage from './pages/MessagesPage';
import VisitorsPage from './pages/VisitorsPage';
import UsersPage from './pages/UsersPage';
import ProfilePage from './pages/ProfilePage';

export default function AdminApp() {
    const [bootstrapped, setBootstrapped] = useState(false);
    const [user, setUser] = useState(null);
    const [profile, setProfile] = useState(null);
    const [unreadCount, setUnreadCount] = useState(0);
    const [sidebarOpen, setSidebarOpen] = useState(false);

    // Initial theme
    const [theme, setTheme] = useState(() => {
        return localStorage.getItem('portfolio-theme') || 'dark';
    });

    // Detect base URL (e.g. /amanullah-portfolio/amanullah/public or empty)
    const getBasePath = useCallback(() => {
        const path = window.location.pathname;
        const match = path.match(/^(.*\/public)/);
        return match ? match[1] : '';
    }, []);

    const getApiBaseUrl = useCallback(() => {
        return `${getBasePath()}/api`;
    }, [getBasePath]);

    // Parse active tab from URL path (e.g. /admin/posts -> posts, /admin -> dashboard)
    const parseAdminTab = useCallback(() => {
        const basePath = getBasePath();
        let pathname = window.location.pathname;
        if (basePath && pathname.startsWith(basePath)) {
            pathname = pathname.substring(basePath.length);
        }

        const match = pathname.match(/^\/admin(?:\/([a-zA-Z0-9_-]+))?/);
        if (match && match[1]) {
            return match[1];
        }

        // Support hash fallback if anyone opens with #posts
        const hash = window.location.hash.replace('#', '');
        return hash || 'dashboard';
    }, [getBasePath]);

    const [currentTab, setCurrentTab] = useState(parseAdminTab);

    // Sync browser Back / Forward buttons (popstate)
    useEffect(() => {
        const handlePopState = () => {
            setCurrentTab(parseAdminTab());
        };
        window.addEventListener('popstate', handlePopState);
        return () => window.removeEventListener('popstate', handlePopState);
    }, [parseAdminTab]);

    // Automatically convert any legacy #posts hash in URL to clean /admin/posts
    useEffect(() => {
        if (window.location.hash) {
            const hashTab = window.location.hash.replace('#', '');
            if (hashTab) {
                const basePath = getBasePath();
                const targetUrl = hashTab === 'dashboard' ? `${basePath}/admin` : `${basePath}/admin/${hashTab}`;
                window.history.replaceState(null, '', targetUrl);
                setCurrentTab(hashTab);
            }
        }
    }, [getBasePath]);

    // Clean URL navigation without hashes (e.g. /admin/posts, /admin/projects)
    const handleTabChange = (tab) => {
        const basePath = getBasePath();
        const targetUrl = tab === 'dashboard' ? `${basePath}/admin` : `${basePath}/admin/${tab}`;

        if (window.location.pathname !== targetUrl) {
            window.history.pushState(null, '', targetUrl);
        }

        setCurrentTab(tab);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Theme sync
    useEffect(() => {
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('portfolio-theme', theme);
    }, [theme]);

    const toggleTheme = () => {
        setTheme((prev) => (prev === 'dark' ? 'light' : 'dark'));
    };

    // Load bootstrap data
    useEffect(() => {
        let isMounted = true;
        axios.get(`${getApiBaseUrl()}/admin/bootstrap`)
            .then((res) => {
                if (isMounted) {
                    setUser(res.data.user);
                    setProfile(res.data.profile);
                    setUnreadCount(res.data.unread_messages_count || 0);
                    setBootstrapped(true);
                }
            })
            .catch(() => {
                if (isMounted) setBootstrapped(true);
            });

        return () => { isMounted = false; };
    }, [getApiBaseUrl]);

    if (!bootstrapped) {
        return (
            <div className="d-flex align-items-center justify-content-center min-vh-100" style={{ background: 'var(--bg)' }}>
                <div className="spinner-border text-accent" role="status" style={{ width: '3rem', height: '3rem' }}>
                    <span className="visually-hidden">Loading Admin Workspace...</span>
                </div>
            </div>
        );
    }

    return (
        <div className="admin-body">
            <div className="admin-shell">
                <AdminSidebar
                    currentTab={currentTab}
                    setCurrentTab={handleTabChange}
                    unreadMessagesCount={unreadCount}
                    sidebarOpen={sidebarOpen}
                    setSidebarOpen={setSidebarOpen}
                    basePath={getBasePath()}
                />

                <div className="admin-main">
                    <AdminHeader
                        user={user}
                        profile={profile}
                        sidebarOpen={sidebarOpen}
                        setSidebarOpen={setSidebarOpen}
                        theme={theme}
                        toggleTheme={toggleTheme}
                        unreadCount={unreadCount}
                        currentTab={currentTab}
                        setCurrentTab={handleTabChange}
                        basePath={getBasePath()}
                    />

                    {sidebarOpen && (
                        <div
                            className="sidebar-overlay show d-lg-none"
                            style={{
                                display: 'block',
                                position: 'fixed',
                                top: 0,
                                left: 0,
                                width: '100vw',
                                height: '100vh',
                                background: 'rgba(0,0,0,0.6)',
                                zIndex: 1039,
                            }}
                            onClick={() => setSidebarOpen(false)}
                        />
                    )}

                    <main className="admin-content">
                        {currentTab === 'dashboard' && (
                            <AdminDashboard
                                setCurrentTab={handleTabChange}
                                getApiBaseUrl={getApiBaseUrl}
                            />
                        )}

                        {['projects', 'posts', 'services', 'skills', 'experiences', 'educations'].includes(currentTab) && (
                            <ContentPage
                                key={currentTab}
                                type={currentTab}
                                getApiBaseUrl={getApiBaseUrl}
                            />
                        )}

                        {currentTab === 'messages' && (
                            <MessagesPage
                                getApiBaseUrl={getApiBaseUrl}
                                onCountChange={setUnreadCount}
                            />
                        )}

                        {currentTab === 'visitors' && (
                            <VisitorsPage getApiBaseUrl={getApiBaseUrl} />
                        )}

                        {currentTab === 'users' && (
                            <UsersPage getApiBaseUrl={getApiBaseUrl} />
                        )}

                        {currentTab === 'profile' && (
                            <ProfilePage getApiBaseUrl={getApiBaseUrl} />
                        )}
                    </main>
                </div>
            </div>
        </div>
    );
}
