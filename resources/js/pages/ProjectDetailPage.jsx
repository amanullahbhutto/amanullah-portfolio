import React, { useState, useEffect } from 'react';
import { fetchProjectDetail } from '../api/client';

export default function ProjectDetailPage({ slug, navigate }) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [selectedImage, setSelectedImage] = useState(null);
    const [modalOpen, setModalOpen] = useState(false);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);
        fetchProjectDetail(slug)
            .then((res) => {
                if (isMounted) {
                    setData(res);
                    const mainImg = res.project?.image_urls?.[0] || res.project?.image_url;
                    setSelectedImage(mainImg);
                    setLoading(false);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            })
            .catch(() => {
                if (isMounted) setLoading(false);
            });
        return () => { isMounted = false; };
    }, [slug]);

    if (loading) {
        return (
            <div className="container py-5 text-center" style={{ minHeight: '60vh', paddingTop: '150px' }}>
                <div className="spinner-border text-accent" role="status">
                    <span className="visually-hidden">Loading project...</span>
                </div>
            </div>
        );
    }

    if (!data?.project) {
        return (
            <div className="container py-5 text-center" style={{ minHeight: '60vh', paddingTop: '150px' }}>
                <h2>Project Not Found</h2>
                <p className="text-muted-custom">The requested project could not be found or has not been published yet.</p>
                <button type="button" className="btn btn-accent mt-3" onClick={() => navigate('projects')}>
                    Back to Projects
                </button>
            </div>
        );
    }

    const { project, prev, next } = data;
    const imageUrls = project.image_urls || (project.image_url ? [project.image_url] : []);

    return (
        <>
            <section className="section-space" style={{ paddingTop: 'calc(var(--header-height) + 50px)' }}>
                <div className="container">
                    <article className="article-wrap">
                        <div className="breadcrumbs">
                            <a href="#home" onClick={(e) => { e.preventDefault(); navigate('home'); }}>
                                <i className="bi bi-house-door me-1"></i>Home
                            </a>
                            <span>/</span>
                            <a href="#projects" onClick={(e) => { e.preventDefault(); navigate('projects'); }}>
                                Projects
                            </a>
                            <span>/</span>
                            <span className="text-truncate" style={{ maxWidth: '250px' }}>{project.title}</span>
                        </div>

                        <header className="article-header mb-4">
                            <div className="d-flex align-items-center gap-2 flex-wrap mb-3">
                                <span className="eyebrow mb-0">Project case study</span>
                                <span className={`project-type-pill ${project.project_type_badge_class || 'type-full-dev'}`}>
                                    <i className={`bi ${project.project_type === 'modification_enhancement' ? 'bi-gear-wide-connected' : 'bi-hammer'} me-1`}></i>
                                    {project.project_type_label || 'Full Development'}
                                </span>
                                {project.is_featured && (
                                    <span className="featured-tag" style={{ position: 'static' }}>Featured</span>
                                )}
                            </div>

                            <h1 className="mb-3">{project.title}</h1>
                            <p className="hero-lead mb-3">{project.excerpt}</p>

                            <div className="p-3 rounded-3 mb-4" style={{ background: 'var(--surface-2)', border: '1px solid var(--line)' }}>
                                <p className="text-muted-custom small mb-0">
                                    <i className="bi bi-info-circle-fill text-accent me-1"></i>
                                    <strong>Development scope:</strong> {project.project_type_label} &mdash; {project.project_type_description}
                                </p>
                            </div>

                            {project.technologies && project.technologies.length > 0 && (
                                <div className="mb-4">
                                    <span className="text-muted-custom small d-block mb-2 font-monospace text-uppercase" style={{ letterSpacing: '.05em' }}>
                                        Technologies Used
                                    </span>
                                    <ul className="tech-list">
                                        {project.technologies.map((tech, tIdx) => (
                                            <li key={tIdx}>{tech}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </header>

                        {imageUrls.length > 0 && (
                            <div className="project-detail-gallery project-detail-hero-wrapper mb-4">
                                <div
                                    className="project-detail-hero-image"
                                    onClick={() => setModalOpen(true)}
                                    title="Click to view full image"
                                    style={{ cursor: 'zoom-in' }}
                                >
                                    <img
                                        src={selectedImage || imageUrls[0]}
                                        alt={`${project.title} project preview`}
                                        loading="eager"
                                        style={{ width: '100%', maxHeight: '550px', objectFit: 'contain', borderRadius: '12px' }}
                                    />
                                    <div className="hero-zoom-badge">
                                        <i className="bi bi-arrows-fullscreen"></i>
                                    </div>
                                </div>

                                {imageUrls.length > 1 && (
                                    <div className="d-flex flex-wrap gap-2 mt-3">
                                        {imageUrls.map((imgUrl, idx) => (
                                            <div
                                                key={idx}
                                                onClick={() => setSelectedImage(imgUrl)}
                                                style={{
                                                    cursor: 'pointer',
                                                    border: selectedImage === imgUrl ? '2px solid var(--accent)' : '2px solid transparent',
                                                    borderRadius: '8px',
                                                    overflow: 'hidden',
                                                    width: '90px',
                                                    height: '60px'
                                                }}
                                            >
                                                <img
                                                    src={imgUrl}
                                                    alt={`Thumbnail ${idx + 1}`}
                                                    style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                                />
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="d-flex flex-wrap gap-3 mb-5">
                            {project.project_url && (
                                <a
                                    href={project.project_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="btn btn-accent"
                                >
                                    <i className="bi bi-box-arrow-up-right me-2"></i>Live Demo / Preview
                                </a>
                            )}
                            {project.github_url && (
                                <a
                                    href={project.github_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="btn btn-outline-theme"
                                >
                                    <i className="bi bi-github me-2"></i>Source Code
                                </a>
                            )}
                        </div>

                        {project.description && (
                            <div className="article-content mb-5">
                                <h2 className="h4 mb-3">Project Overview</h2>
                                {project.description.split('\n').map((para, pIdx) => (
                                    para.trim() ? <p key={pIdx}>{para}</p> : null
                                ))}
                            </div>
                        )}

                        <div className="d-flex justify-content-between align-items-center pt-4 border-top">
                            {prev ? (
                                <button
                                    type="button"
                                    className="btn btn-soft"
                                    onClick={() => navigate('project-detail', null, { slug: prev.slug })}
                                >
                                    <i className="bi bi-arrow-left me-2"></i> {prev.title}
                                </button>
                            ) : <div></div>}

                            {next ? (
                                <button
                                    type="button"
                                    className="btn btn-soft"
                                    onClick={() => navigate('project-detail', null, { slug: next.slug })}
                                >
                                    {next.title} <i className="bi bi-arrow-right ms-2"></i>
                                </button>
                            ) : <div></div>}
                        </div>
                    </article>
                </div>
            </section>

            {modalOpen && (
                <div
                    className="modal fade show d-block"
                    tabIndex="-1"
                    style={{ backgroundColor: 'rgba(0,0,0,0.85)', zIndex: 1050 }}
                    onClick={() => setModalOpen(false)}
                >
                    <div className="modal-dialog modal-xl modal-dialog-centered">
                        <div className="modal-content bg-transparent border-0 text-center">
                            <img
                                src={selectedImage || imageUrls[0]}
                                alt="Fullscreen preview"
                                style={{ maxWidth: '100%', maxHeight: '85vh', objectFit: 'contain', margin: '0 auto' }}
                            />
                            <button
                                type="button"
                                className="btn btn-accent mt-3 align-self-center"
                                onClick={() => setModalOpen(false)}
                            >
                                <i className="bi bi-x-lg me-1"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
