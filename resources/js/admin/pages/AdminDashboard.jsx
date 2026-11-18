import React, { useState, useEffect } from 'react';
import axios from 'axios';

export default function AdminDashboard({ setCurrentTab, getApiBaseUrl }) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let isMounted = true;
        axios.get(`${getApiBaseUrl()}/admin/dashboard`)
            .then((res) => {
                if (isMounted) {
                    setData(res.data);
                    setLoading(false);
                }
            })
            .catch(() => {
                if (isMounted) setLoading(false);
            });
        return () => { isMounted = false; };
    }, [getApiBaseUrl]);

    if (loading) {
        return (
            <div className="text-center py-5">
                <div className="spinner-border text-accent" role="status">
                    <span className="visually-hidden">Loading dashboard metrics...</span>
                </div>
            </div>
        );
    }

    const stats = data?.stats || [];
    const latestMessages = data?.latest_messages || [];
    const latestProjects = data?.latest_projects || [];
    const recentVisitors = data?.recent_visitors || [];

    return (
        <div className="admin-dashboard-view">
            {/* Stat Cards Grid */}
            <div className="row g-4 mb-5">
                {stats.map((stat, idx) => (
                    <div className="col-sm-6 col-xl-4 col-xxl-2" key={idx}>
                        <div
                            className="admin-stat-card p-3 rounded-3"
                            style={{
                                background: 'var(--surface)',
                                border: '1px solid var(--line)',
                                cursor: 'pointer',
                                transition: 'transform 0.2s, box-shadow 0.2s',
                            }}
                            onClick={() => setCurrentTab(stat.route)}
                        >
                            <div className="d-flex align-items-center justify-content-between mb-2">
                                <span className="text-muted-custom small text-uppercase fw-semibold" style={{ letterSpacing: '.05em' }}>
                                    {stat.label}
                                </span>
                                <div
                                    className="stat-icon-wrap d-flex align-items-center justify-content-center rounded-2"
                                    style={{
                                        width: '36px',
                                        height: '36px',
                                        background: 'rgba(249, 115, 22, 0.1)',
                                        color: 'var(--accent)',
                                    }}
                                >
                                    <i className={`bi ${stat.icon} fs-5`}></i>
                                </div>
                            </div>
                            <div className="h3 mb-0 fw-bold">{stat.value}</div>
                        </div>
                    </div>
                ))}
            </div>

            <div className="row g-4">
                {/* Recent Inquiries */}
                <div className="col-lg-7">
                    <section className="admin-card">
                        <div className="admin-card-head d-flex justify-content-between align-items-center">
                            <div>
                                <h2>Recent Inquiries</h2>
                                <p className="text-muted-custom small mb-0">Latest messages submitted via the portfolio</p>
                            </div>
                            <button
                                type="button"
                                className="btn btn-outline-theme btn-sm"
                                onClick={() => setCurrentTab('messages')}
                            >
                                View all <i className="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Sender</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {latestMessages.length === 0 ? (
                                        <tr>
                                            <td colSpan="4" className="text-center py-4 text-muted-custom">
                                                No inquiries yet.
                                            </td>
                                        </tr>
                                    ) : (
                                        latestMessages.map((msg) => (
                                            <tr
                                                key={msg.id}
                                                style={{ cursor: 'pointer' }}
                                                onClick={() => setCurrentTab('messages')}
                                            >
                                                <td>
                                                    <strong>{msg.name}</strong>
                                                    <div className="small text-muted-custom">{msg.email}</div>
                                                </td>
                                                <td>
                                                    <span className="text-truncate d-block" style={{ maxWidth: '200px' }}>
                                                        {msg.subject}
                                                    </span>
                                                </td>
                                                <td>
                                                    {msg.is_read ? (
                                                        <span className="badge bg-secondary-subtle text-secondary">Read</span>
                                                    ) : (
                                                        <span className="badge bg-danger-subtle text-danger">Unread</span>
                                                    )}
                                                </td>
                                                <td className="small text-muted-custom" style={{ whiteSpace: 'nowrap' }}>
                                                    {new Date(msg.created_at).toLocaleDateString()}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                {/* Recent Projects & Live Views */}
                <div className="col-lg-5">
                    <section className="admin-card mb-4">
                        <div className="admin-card-head d-flex justify-content-between align-items-center">
                            <div>
                                <h2>Recent Projects</h2>
                            </div>
                            <button
                                type="button"
                                className="btn btn-outline-theme btn-sm"
                                onClick={() => setCurrentTab('projects')}
                            >
                                Manage <i className="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                        <div className="p-3">
                            {latestProjects.map((proj) => (
                                <div
                                    key={proj.id}
                                    className="d-flex align-items-center justify-content-between p-2 rounded-2 mb-2"
                                    style={{ background: 'var(--surface-2)' }}
                                >
                                    <div>
                                        <strong className="d-block">{proj.title}</strong>
                                        <small className="text-muted-custom">{proj.project_type_label}</small>
                                    </div>
                                    <span className={`badge ${proj.is_published ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}`}>
                                        {proj.is_published ? 'Published' : 'Draft'}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="admin-card">
                        <div className="admin-card-head d-flex justify-content-between align-items-center">
                            <div>
                                <h2>Live Traffic Activity</h2>
                            </div>
                            <button
                                type="button"
                                className="btn btn-outline-theme btn-sm"
                                onClick={() => setCurrentTab('visitors')}
                            >
                                Logs <i className="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                        <div className="p-3">
                            {recentVisitors.map((vis) => (
                                <div key={vis.id} className="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary-subtle">
                                    <div>
                                        <div className="small fw-semibold">{vis.path}</div>
                                        <small className="text-muted-custom">{vis.browser} on {vis.platform}</small>
                                    </div>
                                    <span className="small text-muted-custom">{vis.visited_at}</span>
                                </div>
                            ))}
                        </div>
                    </section>
                </div>
            </div>
        </div>
    );
}
