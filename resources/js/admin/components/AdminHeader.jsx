import React, { useState } from 'react';

export default function AdminHeader({
    user,
    profile,
    sidebarOpen,
    setSidebarOpen,
    theme,
    toggleTheme,
    unreadCount = 0,
    currentTab = 'dashboard',
    setCurrentTab,
    basePath = '',
}) {
    const [dropdownOpen, setDropdownOpen] = useState(false);

    // Title mapping
    const titleMap = {
        dashboard: 'Dashboard',
        projects: 'Projects',
        posts: 'Blog Posts',
        services: 'Services',
        skills: 'Skills',
        experiences: 'Experience History',
        educations: 'Education & Qualifications',
        profile: 'Profile Settings',
        messages: 'Inquiries & Messages',
        visitors: 'Traffic & Visitors',
        users: 'Users Management',
    };

    const pageTitle = titleMap[currentTab] || 'Dashboard';
    const userInitial = (user?.name || profile?.full_name || 'Amanullah').charAt(0).toUpperCase();

    return (
        <header className="admin-topbar">
            {/* Left Side: Mobile Menu Button + Page Title */}
            <div className="d-flex align-items-center gap-2 gap-sm-3 min-w-0">
                <button
                    className="topbar-icon d-lg-none flex-shrink-0"
                    type="button"
                    onClick={() => setSidebarOpen(!sidebarOpen)}
                    aria-label="Toggle sidebar"
                >
                    <i className="bi bi-list fs-5"></i>
                </button>
                <div className="min-w-0">
                    <h1 className="topbar-title text-nowrap text-truncate mb-0">
                        {pageTitle}
                    </h1>
                </div>
            </div>

            {/* Right Side: Exact layout as requested [ ↗ ] [ 🔔 ] [ 🌙 ] [ A ▾ ] */}
            <div className="topbar-actions d-flex align-items-center flex-nowrap">
                {/* 1. View Website External Link */}
                <a
                    className="topbar-icon flex-shrink-0"
                    href={`${basePath}/`}
                    target="_blank"
                    rel="noopener noreferrer"
                    title="View live website"
                    aria-label="View live website"
                >
                    <i className="bi bi-box-arrow-up-right"></i>
                </a>

                {/* 2. Notifications / Messages Icon */}
                <button
                    className="topbar-icon notification-link flex-shrink-0 border-0"
                    type="button"
                    onClick={() => setCurrentTab('messages')}
                    title="Unread messages"
                    aria-label="Unread messages"
                >
                    <i className="bi bi-bell"></i>
                    {unreadCount > 0 && (
                        <span className="notification-badge">
                            {unreadCount > 99 ? '99+' : unreadCount}
                        </span>
                    )}
                </button>

                {/* 3. Day / Night Mode Toggle */}
                <button
                    className="theme-toggle flex-shrink-0"
                    type="button"
                    onClick={toggleTheme}
                    aria-label="Switch colour theme"
                    title="Day / night mode"
                >
                    <i className="bi bi-sun-fill theme-icon-light"></i>
                    <i className="bi bi-moon-stars-fill theme-icon-dark"></i>
                </button>

                {/* 4. User Profile Dropdown Button */}
                <div className="dropdown flex-shrink-0 position-relative">
                    <button
                        className="topbar-profile dropdown-toggle border-0 bg-transparent"
                        type="button"
                        onClick={() => setDropdownOpen(!dropdownOpen)}
                        aria-expanded={dropdownOpen}
                        title={user?.name || 'Amanullah'}
                        aria-label="User menu"
                    >
                        <span>{userInitial}</span>
                    </button>

                    {dropdownOpen && (
                        <>
                            {/* Backdrop to close dropdown on outside click */}
                            <div
                                style={{
                                    position: 'fixed',
                                    top: 0,
                                    left: 0,
                                    right: 0,
                                    bottom: 0,
                                    zIndex: 1040,
                                }}
                                onClick={() => setDropdownOpen(false)}
                            />

                            <ul
                                className="dropdown-menu dropdown-menu-end shadow-lg show d-block"
                                style={{
                                    minWidth: '240px',
                                    position: 'absolute',
                                    right: 0,
                                    top: '100%',
                                    marginTop: '8px',
                                    zIndex: 1050,
                                    borderRadius: '16px',
                                    padding: '8px',
                                }}
                            >
                                <li className="px-3 py-2 border-bottom border-secondary border-opacity-25 mb-1">
                                    <div className="fw-bold small text-truncate">{user?.name || 'Amanullah'}</div>
                                    <div className="text-secondary small text-truncate" style={{ fontSize: '0.76rem' }}>
                                        {user?.email || 'admin@amanullah.com'}
                                    </div>
                                </li>

                                <li>
                                    <button
                                        type="button"
                                        className="dropdown-item d-flex align-items-center py-2 rounded-2"
                                        onClick={() => {
                                            setDropdownOpen(false);
                                            setCurrentTab('profile');
                                        }}
                                    >
                                        <i className="bi bi-person-gear me-2"></i>
                                        <span>Profile Settings</span>
                                    </button>
                                </li>

                                <li>
                                    <button
                                        type="button"
                                        className="dropdown-item d-flex align-items-center py-2 rounded-2"
                                        onClick={() => {
                                            setDropdownOpen(false);
                                            setCurrentTab('projects');
                                        }}
                                    >
                                        <i className="bi bi-kanban me-2"></i>
                                        <span>Manage Projects</span>
                                    </button>
                                </li>

                                <li>
                                    <hr className="dropdown-divider border-secondary border-opacity-25 my-1" />
                                </li>

                                <li>
                                    <form method="POST" action={`${basePath}/logout`}>
                                        <input
                                            type="hidden"
                                            name="_token"
                                            value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''}
                                        />
                                        <button
                                            className="dropdown-item text-danger d-flex align-items-center py-2 rounded-2"
                                            type="submit"
                                        >
                                            <i className="bi bi-box-arrow-right me-2"></i>
                                            <span>Sign out</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </>
                    )}
                </div>
            </div>
        </header>
    );
}
