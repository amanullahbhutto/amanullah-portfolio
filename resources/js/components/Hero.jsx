import React from 'react';

export default function Hero({ profile, experienceStat, navigate }) {
    return (
        <section className="hero">
            <div className="container">
                <div className="row align-items-center g-5">
                    <div className="col-lg-7">
                        <div className="status-pill">
                            <span className="status-dot"></span>
                            {profile?.availability || 'Available for work'}
                        </div>
                        <h1>
                            Hi, I'm <span>{profile?.full_name || 'Amanullah'}</span>
                        </h1>
                        <p className="hero-lead">
                            <strong>{profile?.headline || 'PHP & Laravel Web Developer'}.</strong>{' '}
                            {profile?.short_bio}
                        </p>
                        <div className="hero-actions">
                            <button
                                type="button"
                                className="btn btn-accent"
                                onClick={() => navigate('projects')}
                            >
                                View my work <i className="bi bi-arrow-down-right ms-2"></i>
                            </button>
                            <button
                                type="button"
                                className="btn btn-outline-theme"
                                onClick={() => navigate('contact')}
                            >
                                Get in touch
                            </button>
                            {profile?.cv_file_url && (
                                <a className="btn btn-soft" href={profile.cv_file_url} download>
                                    Download CV <i className="bi bi-download ms-2"></i>
                                </a>
                            )}
                        </div>
                        <div className="hero-stats">
                            <div className="hero-stat">
                                <strong>{profile?.project_count || 12}+</strong>
                                <span>Projects done</span>
                            </div>
                            <div className="hero-stat">
                                <strong>{profile?.happy_clients || 8}+</strong>
                                <span>Happy clients</span>
                            </div>
                            <div className="hero-stat">
                                <strong>{experienceStat || `${profile?.years_experience || 2}y`}</strong>
                                <span>Experience</span>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-5">
                        <div className="portrait-card">
                            <div className={`portrait-frame ${profile?.profile_image_url ? 'has-image' : ''}`}>
                                {profile?.profile_image_url && (
                                    <img
                                        src={profile.profile_image_url}
                                        alt={`${profile.full_name || 'Amanullah'} - PHP and Laravel developer`}
                                        width="600"
                                        height="700"
                                    />
                                )}
                            </div>
                            <div className="floating-code">
                                <i className="bi bi-braces-asterisk"></i>
                            </div>
                            <button
                                type="button"
                                className="portrait-badge border-0"
                                onClick={() => navigate('projects')}
                            >
                                Open to projects <i className="bi bi-arrow-up-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
