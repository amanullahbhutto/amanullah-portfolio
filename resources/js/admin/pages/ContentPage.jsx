import React, { useState, useEffect, useCallback, useMemo } from 'react';
import axios from 'axios';
import DataTable from '../components/DataTable';

export default function ContentPage({ type, getApiBaseUrl }) {
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modalOpen, setModalOpen] = useState(false);
    const [editingItem, setEditingItem] = useState(null);
    const [formData, setFormData] = useState({});
    const [submitting, setSubmitting] = useState(false);
    const [notification, setNotification] = useState(null);
    const [activeFilterTab, setActiveFilterTab] = useState('all');

    const typeConfig = {
        projects: {
            title: 'Projects',
            subtitle: 'Manage case studies, portfolio applications, and technologies',
            searchPlaceholder: 'Search projects by title, type, technologies, excerpt...',
            fields: ['title', 'slug', 'excerpt', 'project_type', 'project_url', 'github_url', 'technologies', 'is_featured', 'is_published'],
        },
        posts: {
            title: 'Blog Posts',
            subtitle: 'Articles, coding notes, and Laravel tutorials',
            searchPlaceholder: 'Search blog posts by title, slug, excerpt...',
            fields: ['title', 'slug', 'excerpt', 'content', 'is_published'],
        },
        services: {
            title: 'Services',
            subtitle: 'Web development offerings and specializations',
            searchPlaceholder: 'Search services by title, description...',
            fields: ['title', 'slug', 'short_description', 'icon', 'sort_order', 'is_active'],
        },
        skills: {
            title: 'Skills & Tech Stack',
            subtitle: 'Programming languages, frameworks, and databases',
            searchPlaceholder: 'Search skills by name, category...',
            fields: ['name', 'category', 'proficiency', 'sort_order', 'is_active'],
        },
        experiences: {
            title: 'Experience History',
            subtitle: 'Professional employment timeline and roles',
            searchPlaceholder: 'Search experience by company, position...',
            fields: ['company', 'position', 'location', 'start_date', 'end_date', 'is_current', 'summary'],
        },
        educations: {
            title: 'Education & Qualifications',
            subtitle: 'Academic credentials and certifications',
            searchPlaceholder: 'Search education by institution, degree...',
            fields: ['institution', 'degree', 'field', 'start_year', 'end_year', 'description'],
        },
    };

    const currentConfig = typeConfig[type] || { title: type, searchPlaceholder: 'Search...', fields: ['title'] };

    const loadData = useCallback(() => {
        setLoading(true);
        axios.get(`${getApiBaseUrl()}/admin/content/${type}`)
            .then((res) => {
                setItems(res.data.items || []);
                setLoading(false);
            })
            .catch(() => {
                setLoading(false);
            });
    }, [getApiBaseUrl, type]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    // Quick filter tabs for projects
    const filterTabs = useMemo(() => {
        if (type !== 'projects') return [];
        return [
            { id: 'all', label: 'All Projects', count: items.length },
            { id: 'full_dev', label: 'Full Development', count: items.filter((i) => i.project_type === 'full_development').length },
            { id: 'mod_enh', label: 'Modification', count: items.filter((i) => i.project_type === 'modification_enhancement').length },
            { id: 'featured', label: 'Featured', count: items.filter((i) => i.is_featured).length },
            { id: 'published', label: 'Published', count: items.filter((i) => i.is_published).length },
        ];
    }, [type, items]);

    // Filter items based on active quick tab
    const filteredByTab = useMemo(() => {
        if (type !== 'projects' || activeFilterTab === 'all') return items;
        if (activeFilterTab === 'full_dev') return items.filter((i) => i.project_type === 'full_development');
        if (activeFilterTab === 'mod_enh') return items.filter((i) => i.project_type === 'modification_enhancement');
        if (activeFilterTab === 'featured') return items.filter((i) => i.is_featured);
        if (activeFilterTab === 'published') return items.filter((i) => i.is_published);
        return items;
    }, [type, items, activeFilterTab]);

    // Fast 1-click Toggle (Optimistic Update)
    const handleQuickToggle = async (item, field) => {
        const newValue = item[field] ? 0 : 1;
        // Optimistic UI update
        setItems((prev) =>
            prev.map((i) => (i.id === item.id ? { ...i, [field]: newValue } : i))
        );

        try {
            await axios.put(`${getApiBaseUrl()}/admin/content/${type}/${item.id}`, {
                ...item,
                [field]: newValue,
            });
        } catch {
            // Revert on error
            setItems((prev) =>
                prev.map((i) => (i.id === item.id ? { ...i, [field]: item[field] } : i))
            );
            alert(`Failed to update ${field}.`);
        }
    };

    const handleOpenAdd = () => {
        setEditingItem(null);
        setFormData({
            is_active: 1,
            is_published: 1,
            is_featured: 0,
            project_type: 'full_development',
            proficiency: 80,
            sort_order: (items.length + 1),
        });
        setModalOpen(true);
    };

    const handleOpenEdit = (item) => {
        setEditingItem(item);
        setFormData({
            ...item,
            technologies: Array.isArray(item.technologies) ? item.technologies.join(', ') : (item.technologies || ''),
        });
        setModalOpen(true);
    };

    const handleDelete = async (item) => {
        if (!window.confirm(`Delete "${item.title || item.name || item.position || item.institution || 'this item'}"?`)) {
            return;
        }

        // Instant optimistic delete
        setItems((prev) => prev.filter((i) => i.id !== item.id));

        try {
            await axios.delete(`${getApiBaseUrl()}/admin/content/${type}/${item.id}`);
            setNotification({ type: 'success', text: 'Item deleted successfully.' });
        } catch {
            setNotification({ type: 'danger', text: 'Failed to delete item.' });
            loadData();
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);

        const payload = { ...formData };
        if (type === 'projects' && typeof payload.technologies === 'string') {
            payload.technologies = payload.technologies.split(',').map((t) => t.trim()).filter(Boolean);
        }

        try {
            if (editingItem) {
                await axios.put(`${getApiBaseUrl()}/admin/content/${type}/${editingItem.id}`, payload);
                setNotification({ type: 'success', text: 'Item updated successfully.' });
            } else {
                await axios.post(`${getApiBaseUrl()}/admin/content/${type}`, payload);
                setNotification({ type: 'success', text: 'Item created successfully.' });
            }
            setModalOpen(false);
            loadData();
        } catch (err) {
            setNotification({ type: 'danger', text: err.response?.data?.message || 'Error saving item.' });
        } finally {
            setSubmitting(false);
        }
    };

    // Columns config
    const getColumns = () => {
        if (type === 'projects') {
            return [
                {
                    header: 'Project',
                    accessor: 'title',
                    render: (item) => (
                        <div className="d-flex align-items-center gap-3">
                            <div
                                style={{
                                    width: '54px',
                                    height: '38px',
                                    borderRadius: '8px',
                                    overflow: 'hidden',
                                    background: 'var(--surface-2)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    flexShrink: 0,
                                    border: '1px solid var(--line)',
                                }}
                            >
                                {item.image_url ? (
                                    <img src={item.image_url} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                ) : (
                                    <i className="bi bi-image text-muted-custom"></i>
                                )}
                            </div>
                            <div>
                                <strong className="d-block text-truncate" style={{ maxWidth: '280px' }}>{item.title}</strong>
                                <small className="text-muted-custom font-monospace" style={{ fontSize: '0.75rem' }}>{item.slug}</small>
                            </div>
                        </div>
                    ),
                },
                {
                    header: 'Type',
                    accessor: 'project_type',
                    render: (item) => (
                        <span className={`project-type-pill ${item.project_type_badge_class || 'type-full-dev'}`}>
                            {item.project_type_label || item.project_type}
                        </span>
                    ),
                },
                {
                    header: 'Featured',
                    accessor: 'is_featured',
                    render: (item) => (
                        <button
                            type="button"
                            className={`badge border-0 ${item.is_featured ? 'bg-primary text-white' : 'bg-secondary-subtle text-secondary'}`}
                            style={{ cursor: 'pointer', padding: '6px 10px' }}
                            title="Click to toggle featured status"
                            onClick={() => handleQuickToggle(item, 'is_featured')}
                        >
                            <i className={`bi ${item.is_featured ? 'bi-star-fill text-warning' : 'bi-star'} me-1`}></i>
                            {item.is_featured ? 'Featured' : 'Standard'}
                        </button>
                    ),
                },
                {
                    header: 'Status',
                    accessor: 'is_published',
                    render: (item) => (
                        <button
                            type="button"
                            className={`badge border-0 ${item.is_published ? 'bg-success text-white' : 'bg-warning text-dark'}`}
                            style={{ cursor: 'pointer', padding: '6px 10px' }}
                            title="Click to toggle published status"
                            onClick={() => handleQuickToggle(item, 'is_published')}
                        >
                            <i className={`bi ${item.is_published ? 'bi-check-circle-fill' : 'bi-pause-circle-fill'} me-1`}></i>
                            {item.is_published ? 'Published' : 'Draft'}
                        </button>
                    ),
                },
                {
                    header: 'Views',
                    accessor: 'project_views_count',
                    render: (item) => (
                        <span className="badge bg-secondary-subtle text-secondary font-monospace">
                            <i className="bi bi-eye me-1"></i>{item.project_views_count || 0}
                        </span>
                    ),
                },
            ];
        }

        if (type === 'posts') {
            return [
                {
                    header: 'Article Title',
                    accessor: 'title',
                    render: (item) => (
                        <div>
                            <strong>{item.title}</strong>
                            <small className="text-muted-custom d-block font-monospace" style={{ fontSize: '0.75rem' }}>{item.slug}</small>
                        </div>
                    ),
                },
                {
                    header: 'Excerpt',
                    accessor: 'excerpt',
                    render: (item) => <span className="text-muted-custom small">{item.excerpt?.substring(0, 65)}...</span>,
                },
                {
                    header: 'Status',
                    accessor: 'is_published',
                    render: (item) => (
                        <button
                            type="button"
                            className={`badge border-0 ${item.is_published ? 'bg-success text-white' : 'bg-warning text-dark'}`}
                            style={{ cursor: 'pointer', padding: '6px 10px' }}
                            onClick={() => handleQuickToggle(item, 'is_published')}
                        >
                            {item.is_published ? 'Published' : 'Draft'}
                        </button>
                    ),
                },
            ];
        }

        if (type === 'services') {
            return [
                {
                    header: 'Service',
                    accessor: 'title',
                    render: (item) => (
                        <div className="d-flex align-items-center gap-2">
                            <div className="p-2 rounded-2" style={{ background: 'var(--surface-2)', color: 'var(--accent)' }}>
                                <i className={`bi ${item.icon || 'bi-gear'}`}></i>
                            </div>
                            <strong>{item.title}</strong>
                        </div>
                    ),
                },
                {
                    header: 'Description',
                    accessor: 'short_description',
                    render: (item) => <span className="text-muted-custom small">{item.short_description}</span>,
                },
                {
                    header: 'Order',
                    accessor: 'sort_order',
                },
            ];
        }

        if (type === 'skills') {
            return [
                {
                    header: 'Skill Name',
                    accessor: 'name',
                    render: (item) => <strong>{item.name}</strong>,
                },
                {
                    header: 'Category',
                    accessor: 'category',
                },
                {
                    header: 'Proficiency',
                    accessor: 'proficiency',
                    render: (item) => (
                        <div className="d-flex align-items-center gap-2" style={{ minWidth: '140px' }}>
                            <div className="progress flex-grow-1" style={{ height: '7px', borderRadius: '4px' }}>
                                <div className="progress-bar bg-accent" style={{ width: `${item.proficiency}%` }}></div>
                            </div>
                            <span className="small text-muted-custom fw-semibold">{item.proficiency}%</span>
                        </div>
                    ),
                },
                {
                    header: 'Order',
                    accessor: 'sort_order',
                },
            ];
        }

        if (type === 'experiences') {
            return [
                {
                    header: 'Role & Company',
                    accessor: 'position',
                    render: (item) => (
                        <div>
                            <strong>{item.position}</strong>
                            <div className="text-muted-custom small">{item.company} {item.location ? `• ${item.location}` : ''}</div>
                        </div>
                    ),
                },
                {
                    header: 'Duration',
                    accessor: 'start_date',
                    render: (item) => (
                        <span className="small">{item.start_date_formatted} - {item.is_current ? 'Present' : item.end_date_formatted}</span>
                    ),
                },
            ];
        }

        if (type === 'educations') {
            return [
                {
                    header: 'Degree',
                    accessor: 'degree',
                    render: (item) => (
                        <div>
                            <strong>{item.degree}</strong>
                            {item.field && <span className="text-muted-custom small d-block">in {item.field}</span>}
                        </div>
                    ),
                },
                {
                    header: 'Institution',
                    accessor: 'institution',
                },
                {
                    header: 'Years',
                    accessor: 'end_year',
                    render: (item) => <span className="small">{item.start_year} - {item.end_year}</span>,
                },
            ];
        }

        return [{ header: 'Title', accessor: 'title' }];
    };

    return (
        <div>
            {notification && (
                <div
                    className={`alert alert-${notification.type} alert-dismissible fade show mb-4`}
                    role="alert"
                    style={{ borderRadius: '12px' }}
                >
                    {notification.text}
                    <button
                        type="button"
                        className="btn-close"
                        onClick={() => setNotification(null)}
                        aria-label="Close"
                    ></button>
                </div>
            )}

            <DataTable
                title={currentConfig.title}
                subtitle={currentConfig.subtitle}
                searchPlaceholder={currentConfig.searchPlaceholder}
                columns={getColumns()}
                data={filteredByTab}
                loading={loading}
                filterTabs={filterTabs}
                activeTab={activeFilterTab}
                onTabChange={setActiveFilterTab}
                onAdd={handleOpenAdd}
                addButtonLabel={`Add ${currentConfig.title.replace(/s$/, '')}`}
                actions={(item) => (
                    <>
                        {item.slug && type === 'projects' && (
                            <a
                                href={`${getApiBaseUrl().replace('/api', '')}/projects/${item.slug}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="btn btn-sm btn-soft"
                                title="Live Preview on Website"
                            >
                                <i className="bi bi-box-arrow-up-right"></i>
                            </a>
                        )}
                        <button
                            type="button"
                            className="btn btn-sm btn-soft"
                            title="Edit"
                            onClick={() => handleOpenEdit(item)}
                        >
                            <i className="bi bi-pencil-square"></i>
                        </button>
                        <button
                            type="button"
                            className="btn btn-sm btn-soft text-danger"
                            title="Delete"
                            onClick={() => handleDelete(item)}
                        >
                            <i className="bi bi-trash"></i>
                        </button>
                    </>
                )}
            />

            {/* Quick Add / Edit Modal */}
            {modalOpen && (
                <div
                    className="modal fade show d-block"
                    tabIndex="-1"
                    style={{ backgroundColor: 'rgba(0,0,0,0.65)', zIndex: 1055 }}
                >
                    <div className="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div className="modal-content" style={{ background: 'var(--surface)', border: '1px solid var(--line)', borderRadius: '16px' }}>
                            <div className="modal-header border-bottom py-3">
                                <h5 className="modal-title fw-bold">
                                    {editingItem ? `Edit ${currentConfig.title.replace(/s$/, '')}` : `Create New ${currentConfig.title.replace(/s$/, '')}`}
                                </h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={() => setModalOpen(false)}
                                    aria-label="Close"
                                ></button>
                            </div>

                            <form onSubmit={handleSubmit}>
                                <div className="modal-body p-4">
                                    <div className="row g-3">
                                        {(type === 'projects' || type === 'posts' || type === 'services') && (
                                            <>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Title *</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.title || ''}
                                                        onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                                        required
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Slug (URL friendly)</label>
                                                    <input
                                                        type="text"
                                                        className="form-control font-monospace"
                                                        value={formData.slug || ''}
                                                        onChange={(e) => setFormData({ ...formData, slug: e.target.value })}
                                                        placeholder="auto-generated from title"
                                                    />
                                                </div>
                                            </>
                                        )}

                                        {type === 'projects' && (
                                            <>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Project Type</label>
                                                    <select
                                                        className="form-select"
                                                        value={formData.project_type || 'full_development'}
                                                        onChange={(e) => setFormData({ ...formData, project_type: e.target.value })}
                                                    >
                                                        <option value="full_development">Full Development (Start se khud banaya)</option>
                                                        <option value="modification_enhancement">Modification / Enhancement</option>
                                                    </select>
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Live Project URL</label>
                                                    <input
                                                        type="url"
                                                        className="form-control"
                                                        value={formData.project_url || ''}
                                                        onChange={(e) => setFormData({ ...formData, project_url: e.target.value })}
                                                        placeholder="https://..."
                                                    />
                                                </div>
                                                <div className="col-md-12">
                                                    <label className="form-label fw-semibold">Technologies (comma separated)</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.technologies || ''}
                                                        onChange={(e) => setFormData({ ...formData, technologies: e.target.value })}
                                                        placeholder="PHP, Laravel, MySQL, Bootstrap, JavaScript"
                                                    />
                                                </div>
                                                <div className="col-md-12">
                                                    <label className="form-label fw-semibold">Excerpt (Summary) *</label>
                                                    <textarea
                                                        className="form-control"
                                                        rows="2"
                                                        value={formData.excerpt || ''}
                                                        onChange={(e) => setFormData({ ...formData, excerpt: e.target.value })}
                                                        required
                                                    ></textarea>
                                                </div>
                                                <div className="col-md-12">
                                                    <label className="form-label fw-semibold">Detailed Description</label>
                                                    <textarea
                                                        className="form-control"
                                                        rows="4"
                                                        value={formData.description || ''}
                                                        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                                    ></textarea>
                                                </div>
                                                <div className="col-md-6">
                                                    <div className="form-check form-switch mt-2">
                                                        <input
                                                            className="form-check-input"
                                                            type="checkbox"
                                                            id="is_featured"
                                                            checked={!!formData.is_featured}
                                                            onChange={(e) => setFormData({ ...formData, is_featured: e.target.checked ? 1 : 0 })}
                                                        />
                                                        <label className="form-check-label fw-semibold" htmlFor="is_featured">Featured Project</label>
                                                    </div>
                                                </div>
                                                <div className="col-md-6">
                                                    <div className="form-check form-switch mt-2">
                                                        <input
                                                            className="form-check-input"
                                                            type="checkbox"
                                                            id="is_published"
                                                            checked={formData.is_published !== 0}
                                                            onChange={(e) => setFormData({ ...formData, is_published: e.target.checked ? 1 : 0 })}
                                                        />
                                                        <label className="form-check-label fw-semibold" htmlFor="is_published">Published (Visible on site)</label>
                                                    </div>
                                                </div>
                                            </>
                                        )}

                                        {type === 'posts' && (
                                            <>
                                                <div className="col-md-12">
                                                    <label className="form-label fw-semibold">Excerpt *</label>
                                                    <textarea
                                                        className="form-control"
                                                        rows="2"
                                                        value={formData.excerpt || ''}
                                                        onChange={(e) => setFormData({ ...formData, excerpt: e.target.value })}
                                                        required
                                                    ></textarea>
                                                </div>
                                                <div className="col-md-12">
                                                    <label className="form-label fw-semibold">Article Content *</label>
                                                    <textarea
                                                        className="form-control"
                                                        rows="6"
                                                        value={formData.content || ''}
                                                        onChange={(e) => setFormData({ ...formData, content: e.target.value })}
                                                        required
                                                    ></textarea>
                                                </div>
                                                <div className="col-md-6">
                                                    <div className="form-check form-switch mt-2">
                                                        <input
                                                            className="form-check-input"
                                                            type="checkbox"
                                                            id="post_published"
                                                            checked={formData.is_published !== 0}
                                                            onChange={(e) => setFormData({ ...formData, is_published: e.target.checked ? 1 : 0 })}
                                                        />
                                                        <label className="form-check-label fw-semibold" htmlFor="post_published">Published</label>
                                                    </div>
                                                </div>
                                            </>
                                        )}

                                        {type === 'services' && (
                                            <>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Bootstrap Icon Class</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.icon || 'bi-gear'}
                                                        onChange={(e) => setFormData({ ...formData, icon: e.target.value })}
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Sort Order</label>
                                                    <input
                                                        type="number"
                                                        className="form-control"
                                                        value={formData.sort_order || 1}
                                                        onChange={(e) => setFormData({ ...formData, sort_order: Number(e.target.value) })}
                                                    />
                                                </div>
                                                <div className="col-md-12">
                                                    <label className="form-label fw-semibold">Short Description *</label>
                                                    <textarea
                                                        className="form-control"
                                                        rows="3"
                                                        value={formData.short_description || ''}
                                                        onChange={(e) => setFormData({ ...formData, short_description: e.target.value })}
                                                        required
                                                    ></textarea>
                                                </div>
                                            </>
                                        )}

                                        {type === 'skills' && (
                                            <>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Skill Name *</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.name || ''}
                                                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                                        required
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Category</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.category || 'Backend'}
                                                        onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Proficiency: {formData.proficiency || 80}%</label>
                                                    <input
                                                        type="range"
                                                        className="form-range"
                                                        min="10"
                                                        max="100"
                                                        value={formData.proficiency || 80}
                                                        onChange={(e) => setFormData({ ...formData, proficiency: Number(e.target.value) })}
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Sort Order</label>
                                                    <input
                                                        type="number"
                                                        className="form-control"
                                                        value={formData.sort_order || 1}
                                                        onChange={(e) => setFormData({ ...formData, sort_order: Number(e.target.value) })}
                                                    />
                                                </div>
                                            </>
                                        )}

                                        {type === 'experiences' && (
                                            <>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Role / Position *</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.position || ''}
                                                        onChange={(e) => setFormData({ ...formData, position: e.target.value })}
                                                        required
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Company *</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.company || ''}
                                                        onChange={(e) => setFormData({ ...formData, company: e.target.value })}
                                                        required
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Location</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.location || ''}
                                                        onChange={(e) => setFormData({ ...formData, location: e.target.value })}
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Start Date</label>
                                                    <input
                                                        type="date"
                                                        className="form-control"
                                                        value={formData.start_date ? formData.start_date.substring(0, 10) : ''}
                                                        onChange={(e) => setFormData({ ...formData, start_date: e.target.value })}
                                                    />
                                                </div>
                                                <div className="col-md-12">
                                                    <label className="form-label fw-semibold">Summary *</label>
                                                    <textarea
                                                        className="form-control"
                                                        rows="3"
                                                        value={formData.summary || ''}
                                                        onChange={(e) => setFormData({ ...formData, summary: e.target.value })}
                                                        required
                                                    ></textarea>
                                                </div>
                                            </>
                                        )}

                                        {type === 'educations' && (
                                            <>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Degree / Certification *</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.degree || ''}
                                                        onChange={(e) => setFormData({ ...formData, degree: e.target.value })}
                                                        required
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Field of Study</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.field || ''}
                                                        onChange={(e) => setFormData({ ...formData, field: e.target.value })}
                                                    />
                                                </div>
                                                <div className="col-md-6">
                                                    <label className="form-label fw-semibold">Institution *</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        value={formData.institution || ''}
                                                        onChange={(e) => setFormData({ ...formData, institution: e.target.value })}
                                                        required
                                                    />
                                                </div>
                                                <div className="col-md-3">
                                                    <label className="form-label fw-semibold">Start Year</label>
                                                    <input
                                                        type="number"
                                                        className="form-control"
                                                        value={formData.start_year || 2020}
                                                        onChange={(e) => setFormData({ ...formData, start_year: Number(e.target.value) })}
                                                    />
                                                </div>
                                                <div className="col-md-3">
                                                    <label className="form-label fw-semibold">End Year</label>
                                                    <input
                                                        type="number"
                                                        className="form-control"
                                                        value={formData.end_year || 2024}
                                                        onChange={(e) => setFormData({ ...formData, end_year: Number(e.target.value) })}
                                                    />
                                                </div>
                                            </>
                                        )}
                                    </div>
                                </div>

                                <div className="modal-footer border-top py-3">
                                    <button
                                        type="button"
                                        className="btn btn-outline-theme"
                                        onClick={() => setModalOpen(false)}
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        className="btn btn-accent"
                                        disabled={submitting}
                                    >
                                        {submitting ? 'Saving...' : (editingItem ? 'Save Changes' : 'Create Item')}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
