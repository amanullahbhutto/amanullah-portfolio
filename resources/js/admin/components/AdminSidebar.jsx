import React from 'react';

export default function AdminSidebar({
    currentTab,
    setCurrentTab,
    unreadMessagesCount = 0,
    sidebarOpen,
    setSidebarOpen,
    basePath = '',
}) {
    const handleNav = (tab, e) => {
        if (e) e.preventDefault();
        setCurrentTab(tab);
        setSidebarOpen(false);
    };

    return (
        <aside className={`admin-sidebar ${sidebarOpen ? 'is-open' : ''}`} id="adminSidebar">
            <div className="sidebar-head">
                <a
                    className="brand-mark"
                    href={`${basePath}/admin`}
                    onClick={(e) => handleNav('dashboard', e)}
                >
                    <span className="brand-symbol">A</span>
                    <div className="brand-info">
                        <span className="brand-name">AMANULLAH<span className="brand-dot">.</span></span>
                        <span className="brand-subtext">Personal Workspace</span>
                    </div>
                </a>
                <button
                    className="sidebar-close d-lg-none"
                    type="button"
                    onClick={() => setSidebarOpen(false)}
                    aria-label="Close sidebar"
                >
                    <i className="bi bi-x-lg"></i>
                </button>
            </div>

            <nav className="sidebar-nav" aria-label="Admin navigation">
                <span className="sidebar-label">Workspace</span>
                <a
                    href={`${basePath}/admin`}
                    className={currentTab === 'dashboard' ? 'active' : ''}
                    onClick={(e) => handleNav('dashboard', e)}
                >
                    <i className="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>

                <span className="sidebar-label mt-3">Portfolio Content</span>
                <a
                    href={`${basePath}/admin/projects`}
                    className={currentTab === 'projects' ? 'active' : ''}
                    onClick={(e) => handleNav('projects', e)}
                >
                    <i className="bi bi-kanban"></i>
                    <span>Projects</span>
                </a>
                <a
                    href={`${basePath}/admin/posts`}
                    className={currentTab === 'posts' ? 'active' : ''}
                    onClick={(e) => handleNav('posts', e)}
                >
                    <i className="bi bi-journal-richtext"></i>
                    <span>Blog Posts</span>
                </a>
                <a
                    href={`${basePath}/admin/services`}
                    className={currentTab === 'services' ? 'active' : ''}
                    onClick={(e) => handleNav('services', e)}
                >
                    <i className="bi bi-grid-3x3-gap"></i>
                    <span>Services</span>
                </a>

                <span className="sidebar-label mt-3">Resume & Skills</span>
                <a
                    href={`${basePath}/admin/skills`}
                    className={currentTab === 'skills' ? 'active' : ''}
                    onClick={(e) => handleNav('skills', e)}
                >
                    <i className="bi bi-bar-chart"></i>
                    <span>Skills</span>
                </a>
                <a
                    href={`${basePath}/admin/experiences`}
                    className={currentTab === 'experiences' ? 'active' : ''}
                    onClick={(e) => handleNav('experiences', e)}
                >
                    <i className="bi bi-briefcase"></i>
                    <span>Experience</span>
                </a>
                <a
                    href={`${basePath}/admin/educations`}
                    className={currentTab === 'educations' ? 'active' : ''}
                    onClick={(e) => handleNav('educations', e)}
                >
                    <i className="bi bi-mortarboard"></i>
                    <span>Education</span>
                </a>
                <a
                    href={`${basePath}/admin/profile`}
                    className={currentTab === 'profile' ? 'active' : ''}
                    onClick={(e) => handleNav('profile', e)}
                >
                    <i className="bi bi-person-badge"></i>
                    <span>Profile Info</span>
                </a>

                <span className="sidebar-label mt-3">Management</span>
                <a
                    href={`${basePath}/admin/messages`}
                    className={currentTab === 'messages' ? 'active' : ''}
                    onClick={(e) => handleNav('messages', e)}
                >
                    <i className="bi bi-envelope"></i>
                    <span>Messages</span>
                    {unreadMessagesCount > 0 && (
                        <span className="sidebar-badge">
                            {unreadMessagesCount > 99 ? '99+' : unreadMessagesCount}
                        </span>
                    )}
                </a>
                <a
                    href={`${basePath}/admin/visitors`}
                    className={currentTab === 'visitors' ? 'active' : ''}
                    onClick={(e) => handleNav('visitors', e)}
                >
                    <i className="bi bi-graph-up-arrow"></i>
                    <span>Visitors Log</span>
                </a>
                <a
                    href={`${basePath}/admin/users`}
                    className={currentTab === 'users' ? 'active' : ''}
                    onClick={(e) => handleNav('users', e)}
                >
                    <i className="bi bi-people"></i>
                    <span>Users & Access</span>
                </a>

                <span className="sidebar-label mt-3">Modules & Tools</span>
                <a href={`${basePath}/admin/namaz-attendance`}>
                    <i className="bi bi-moon-stars"></i>
                    <span>Namaz Attendance</span>
                </a>
                <a href={`${basePath}/admin/zikr`}>
                    <i className="bi bi-gem"></i>
                    <span>Zikr / Tasbeeh</span>
                </a>
                <a href={`${basePath}/admin/duas`}>
                    <i className="bi bi-book-half"></i>
                    <span>Duas (دعائیں)</span>
                </a>
                <a href={`${basePath}/admin/khata`}>
                    <i className="bi bi-journal-bookmark"></i>
                    <span>Khata System</span>
                </a>
                <a href={`${basePath}/admin/investments`}>
                    <i className="bi bi-cash-stack"></i>
                    <span>Investments</span>
                </a>
                <a href={`${basePath}/admin/date-of-births`}>
                    <i className="bi bi-calendar-heart"></i>
                    <span>Date of Birth</span>
                </a>
            </nav>
        </aside>
    );
}
