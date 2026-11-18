import React, { useState, useEffect } from 'react';
import { fetchProjects } from '../api/client';

export default function ProjectsPage({ initialProjects = [], navigate }) {
    const [projects, setProjects] = useState(initialProjects);
    const [filter, setFilter] = useState('');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);
        fetchProjects(filter)
            .then((data) => {
                if (isMounted) {
                    setProjects(data);
                    setLoading(false);
                }
            })
            .catch(() => {
                if (isMounted) setLoading(false);
            });
        return () => { isMounted = false; };
    }, [filter]);

    return (
        <>
            <section className="page-hero">
                <div className="container">
                    <span className="eyebrow">Selected work</span>
                    <h1>Projects</h1>
                    <p>Web applications designed to solve practical problems with responsive interfaces and maintainable code.</p>

                    <div className="d-flex flex-wrap gap-2 mt-4">
                        <button
                            type="button"
                            className={`btn btn-sm ${filter === '' ? 'btn-accent' : 'btn-outline-theme'}`}
                            onClick={() => setFilter('')}
                        >
                            All Projects
                        </button>
                        <button
                            type="button"
                            className={`btn btn-sm ${filter === 'full_development' ? 'btn-accent' : 'btn-outline-theme'}`}
                            onClick={() => setFilter('full_development')}
                        >
                            Full Development
                        </button>
                        <button
                            type="button"
                            className={`btn btn-sm ${filter === 'modification_enhancement' ? 'btn-accent' : 'btn-outline-theme'}`}
                            onClick={() => setFilter('modification_enhancement')}
                        >
                            Modification / Enhancement
                        </button>
                    </div>
                </div>
            </section>

            <section className="section-space">
                <div className="container">
                    {loading ? (
                        <div className="text-center py-5">
                            <div className="spinner-border text-accent" role="status">
                                <span className="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    ) : projects.length === 0 ? (
                        <div className="col-12">
                            <div className="info-card p-5 text-center">
                                <h2>No projects found</h2>
                                <p className="text-muted-custom mb-0">No projects found in this category.</p>
                            </div>
                        </div>
                    ) : (
                        <div className="row g-4 align-items-stretch">
                            {projects.map((project, index) => {
                                const techList = project.technologies || [];
                                return (
                                    <div className="col-md-6 col-xl-4 d-flex" key={project.id || index}>
                                        <article className="project-card d-flex flex-column h-100 w-100">
                                            <div
                                                className="project-visual d-block"
                                                style={{ cursor: 'pointer' }}
                                                onClick={() => navigate('project-detail', null, { slug: project.slug })}
                                                role="button"
                                                tabIndex={0}
                                                aria-label={`View ${project.title}`}
                                            >
                                                {project.image_url ? (
                                                    <img
                                                        src={project.image_url}
                                                        alt={`${project.title} project preview`}
                                                        loading="lazy"
                                                        width="800"
                                                        height="500"
                                                    />
                                                ) : (
                                                    <div className="project-placeholder">
                                                        <div className="project-window">
                                                            <div className="project-window-bar"><i></i><i></i><i></i></div>
                                                            <div className="project-code-lines">
                                                                <span></span><span></span><span></span><span></span><span></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                                <div className="project-card-tags">
                                                    <span className={`project-type-pill ${project.project_type_badge_class || 'type-full-dev'}`}>
                                                        {project.project_type_label || 'Full Development'}
                                                    </span>
                                                    {project.is_featured && <span className="featured-tag">Featured</span>}
                                                </div>
                                            </div>

                                            <div className="project-body d-flex flex-column flex-grow-1">
                                                <h3 className="project-card-title">
                                                    <a
                                                        href={`#project-${project.slug}`}
                                                        onClick={(e) => {
                                                            e.preventDefault();
                                                            navigate('project-detail', null, { slug: project.slug });
                                                        }}
                                                        title={project.title}
                                                    >
                                                        {project.title}
                                                    </a>
                                                </h3>
                                                <p className="project-card-excerpt" title={project.excerpt}>
                                                    {project.excerpt}
                                                </p>

                                                {techList.length > 0 && (
                                                    <ul className="tech-list mb-3">
                                                        {techList.slice(0, 4).map((tech, tIdx) => (
                                                            <li key={tIdx}>{tech}</li>
                                                        ))}
                                                    </ul>
                                                )}

                                                <div className="project-card-footer mt-auto">
                                                    <button
                                                        type="button"
                                                        className="card-arrow bg-transparent border-0 p-0 text-start"
                                                        onClick={() => navigate('project-detail', null, { slug: project.slug })}
                                                    >
                                                        View full detail <i className="bi bi-arrow-up-right ms-2"></i>
                                                    </button>
                                                    {project.project_url && (
                                                        <a
                                                            href={project.project_url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="btn-preview-link"
                                                            title="Live Preview"
                                                            aria-label="Open live demo"
                                                        >
                                                            <i className="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    )}
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </section>
        </>
    );
}
