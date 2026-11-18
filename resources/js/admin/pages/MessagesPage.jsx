import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import DataTable from '../components/DataTable';

export default function MessagesPage({ getApiBaseUrl, onCountChange }) {
    const [messages, setMessages] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedMessage, setSelectedMessage] = useState(null);
    const [viewModalOpen, setViewModalOpen] = useState(false);

    const loadMessages = useCallback(() => {
        setLoading(true);
        axios.get(`${getApiBaseUrl()}/admin/messages`)
            .then((res) => {
                setMessages(res.data.messages || []);
                const unread = (res.data.messages || []).filter((m) => !m.is_read).length;
                if (onCountChange) onCountChange(unread);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, [getApiBaseUrl, onCountChange]);

    useEffect(() => {
        loadMessages();
    }, [loadMessages]);

    const handleView = async (msg) => {
        setSelectedMessage(msg);
        setViewModalOpen(true);

        if (!msg.is_read) {
            try {
                await axios.patch(`${getApiBaseUrl()}/admin/messages/${msg.id}/read`);
                setMessages((prev) =>
                    prev.map((m) => (m.id === msg.id ? { ...m, is_read: true } : m))
                );
                if (onCountChange) onCountChange((prev) => Math.max(0, prev - 1));
            } catch {
                // silent
            }
        }
    };

    const handleDelete = async (msg) => {
        if (!window.confirm(`Delete message from "${msg.name}"?`)) return;

        try {
            await axios.delete(`${getApiBaseUrl()}/admin/messages/${msg.id}`);
            setMessages((prev) => prev.filter((m) => m.id !== msg.id));
            if (selectedMessage?.id === msg.id) setViewModalOpen(false);
        } catch {
            alert('Failed to delete message.');
        }
    };

    const handleMarkAllRead = async () => {
        try {
            await axios.patch(`${getApiBaseUrl()}/admin/messages/all/read`);
            setMessages((prev) => prev.map((m) => ({ ...m, is_read: true })));
            if (onCountChange) onCountChange(0);
        } catch {
            alert('Failed to mark all as read.');
        }
    };

    const columns = [
        {
            header: 'Sender',
            accessor: 'name',
            render: (m) => (
                <div>
                    <strong>{m.name}</strong>
                    <div className="small text-muted-custom">{m.email}</div>
                </div>
            ),
        },
        {
            header: 'Subject',
            accessor: 'subject',
            render: (m) => (
                <span className="text-truncate d-block" style={{ maxWidth: '240px' }} title={m.subject}>
                    {m.subject}
                </span>
            ),
        },
        {
            header: 'Status',
            accessor: 'is_read',
            render: (m) => (
                m.is_read ? (
                    <span className="badge bg-secondary-subtle text-secondary">Read</span>
                ) : (
                    <span className="badge bg-danger-subtle text-danger">New</span>
                )
            ),
        },
        {
            header: 'Received At',
            accessor: 'created_at',
            render: (m) => (
                <div>
                    <div>{m.created_at}</div>
                    <small className="text-muted-custom">{m.created_at_human}</small>
                </div>
            ),
        },
    ];

    return (
        <div>
            <DataTable
                title="Client & Visitor Inquiries"
                searchPlaceholder="Search messages by sender, email, subject, text..."
                columns={columns}
                data={messages}
                loading={loading}
                extraHeaderActions={
                    <button
                        type="button"
                        className="btn btn-outline-theme btn-sm"
                        onClick={handleMarkAllRead}
                    >
                        <i className="bi bi-check-all me-1"></i>Mark all as read
                    </button>
                }
                actions={(msg) => (
                    <>
                        <button
                            type="button"
                            className="btn btn-sm btn-accent"
                            title="View Full Message"
                            onClick={() => handleView(msg)}
                        >
                            <i className="bi bi-eye"></i>
                        </button>
                        <button
                            type="button"
                            className="btn btn-sm btn-soft text-danger"
                            title="Delete"
                            onClick={() => handleDelete(msg)}
                        >
                            <i className="bi bi-trash"></i>
                        </button>
                    </>
                )}
            />

            {/* View Message Modal */}
            {viewModalOpen && selectedMessage && (
                <div
                    className="modal fade show d-block"
                    tabIndex="-1"
                    style={{ backgroundColor: 'rgba(0,0,0,0.6)', zIndex: 1055 }}
                >
                    <div className="modal-dialog modal-lg modal-dialog-centered">
                        <div className="modal-content" style={{ background: 'var(--surface)', border: '1px solid var(--line)', borderRadius: '16px' }}>
                            <div className="modal-header border-bottom py-3">
                                <div>
                                    <h5 className="modal-title mb-1">{selectedMessage.subject}</h5>
                                    <span className="small text-muted-custom">
                                        Received: {selectedMessage.created_at} ({selectedMessage.created_at_human})
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={() => setViewModalOpen(false)}
                                    aria-label="Close"
                                ></button>
                            </div>

                            <div className="modal-body p-4">
                                <div className="row g-3 mb-4 p-3 rounded-3" style={{ background: 'var(--surface-2)' }}>
                                    <div className="col-sm-6">
                                        <small className="text-muted-custom d-block">From</small>
                                        <strong>{selectedMessage.name}</strong>
                                    </div>
                                    <div className="col-sm-6">
                                        <small className="text-muted-custom d-block">Email</small>
                                        <a href={`mailto:${selectedMessage.email}`} className="text-accent">
                                            {selectedMessage.email}
                                        </a>
                                    </div>
                                    {selectedMessage.phone && (
                                        <div className="col-sm-6">
                                            <small className="text-muted-custom d-block">Phone / WhatsApp</small>
                                            <a
                                                href={`https://wa.me/${selectedMessage.phone.replace(/\D+/g, '')}`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-accent"
                                            >
                                                <i className="bi bi-whatsapp me-1 text-success"></i>
                                                {selectedMessage.phone}
                                            </a>
                                        </div>
                                    )}
                                    <div className="col-sm-6">
                                        <small className="text-muted-custom d-block">IP Address</small>
                                        <span>{selectedMessage.ip_address || 'N/A'}</span>
                                    </div>
                                </div>

                                <div>
                                    <label className="text-muted-custom small text-uppercase fw-semibold mb-2" style={{ letterSpacing: '.05em' }}>
                                        Message Body
                                    </label>
                                    <div
                                        className="p-3 rounded-3"
                                        style={{
                                            background: 'var(--bg)',
                                            border: '1px solid var(--line)',
                                            whiteSpace: 'pre-wrap',
                                            lineHeight: 1.6,
                                            minHeight: '120px',
                                        }}
                                    >
                                        {selectedMessage.message}
                                    </div>
                                </div>
                            </div>

                            <div className="modal-footer border-top py-3 d-flex justify-content-between">
                                <button
                                    type="button"
                                    className="btn btn-outline-danger btn-sm"
                                    onClick={() => handleDelete(selectedMessage)}
                                >
                                    <i className="bi bi-trash me-1"></i>Delete
                                </button>
                                <div className="d-flex gap-2">
                                    <a
                                        href={`mailto:${selectedMessage.email}?subject=Re: ${encodeURIComponent(selectedMessage.subject)}`}
                                        className="btn btn-accent btn-sm"
                                    >
                                        <i className="bi bi-reply-fill me-1"></i>Reply via Email
                                    </a>
                                    <button
                                        type="button"
                                        className="btn btn-soft btn-sm"
                                        onClick={() => setViewModalOpen(false)}
                                    >
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
