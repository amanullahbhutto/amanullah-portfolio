import React from 'react';
import SkillsSection from '../components/SkillsSection';

export default function AboutPage({ profile, experiences = [], educations = [], skills = [], navigate }) {
    const languages = Array.isArray(profile?.languages)
        ? profile.languages.join(', ')
        : (profile?.languages || '');

    return (
        <>
            <section className="page-hero">
                <div className="container">
                    <span className="eyebrow">My background</span>
                    <h1>About Me</h1>
                    <p>PHP and Laravel developer focused on responsive, secure, and maintainable web applications.</p>
                </div>
            </section>

            <section className="section-space">
                <div className="container">
                    <div className="row align-items-center g-5">
                        <div className="col-lg-5">
                            <div className={`about-photo ${profile?.profile_image_url ? 'has-image' : ''}`}>
                                {profile?.profile_image_url && (
                                    <img
                                        src={profile.profile_image_url}
                                        alt={`${profile.full_name || 'Amanullah'} profile image`}
                                    />
                                )}
                            </div>
                        </div>

                        <div className="col-lg-7">
                            <div className="about-copy">
                                <span className="eyebrow">Who I am</span>
                                <h2>{profile?.headline}</h2>
                                {profile?.long_bio && (
                                    <div className="mb-4">
                                        {profile.long_bio.split('\n').map((para, pIdx) => (
                                            para.trim() ? <p key={pIdx}>{para}</p> : null
                                        ))}
                                    </div>
                                )}
                                <div className="detail-grid">
                                    <div className="detail-item">
                                        <i className="bi bi-chevron-right"></i>
                                        <span>
                                            <strong>Email:</strong>{' '}
                                            {profile?.email && (
                                                <a
                                                    className="text-accent gmail-link"
                                                    href={profile.gmail_compose_url || `mailto:${profile.email}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    {profile.email}
                                                </a>
                                            )}
                                        </span>
                                    </div>
                                    <div className="detail-item">
                                        <i className="bi bi-chevron-right"></i>
                                        <span>
                                            <strong>Phone:</strong>{' '}
                                            {profile?.phone && (
                                                <a
                                                    className="text-accent whatsapp-link"
                                                    href={profile.whatsapp_url || `tel:${profile.phone}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    {profile.phone}
                                                </a>
                                            )}
                                        </span>
                                    </div>
                                    <div className="detail-item">
                                        <i className="bi bi-chevron-right"></i>
                                        <span><strong>Address:</strong> {profile?.address || 'Karachi, Pakistan'}</span>
                                    </div>
                                    <div className="detail-item">
                                        <i className="bi bi-chevron-right"></i>
                                        <span><strong>City:</strong> {profile?.city || 'Karachi'}, {profile?.country || 'Pakistan'}</span>
                                    </div>
                                    <div className="detail-item">
                                        <i className="bi bi-chevron-right"></i>
                                        <span><strong>Languages:</strong> {languages}</span>
                                    </div>
                                    <div className="detail-item">
                                        <i className="bi bi-chevron-right"></i>
                                        <span><strong>Availability:</strong> {profile?.availability || 'Available for work'}</span>
                                    </div>
                                </div>
                                <div className="d-flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        className="btn btn-accent"
                                        onClick={() => navigate('contact')}
                                    >
                                        Get in touch
                                    </button>
                                    {profile?.cv_file_url && (
                                        <a className="btn btn-outline-theme" href={profile.cv_file_url} download>
                                            Download CV
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="section-space section-alt">
                <div className="container">
                    <div className="row g-5">
                        <div className="col-lg-7">
                            <div className="section-heading">
                                <span className="eyebrow">Professional history</span>
                                <h2>Experience</h2>
                            </div>
                            <div className="experience-wrap">
                                {experiences.map((exp, index) => (
                                    <article className="experience-item" key={exp.id || index}>
                                        <div className="experience-icon">
                                            <i className="bi bi-code-slash"></i>
                                        </div>
                                        <div>
                                            <div className="experience-date">{exp.date_range}</div>
                                            <h3>{exp.position}</h3>
                                            <div className="experience-company">
                                                {exp.company}
                                                {exp.location && (
                                                    <span className="text-muted-custom"> - {exp.location}</span>
                                                )}
                                            </div>
                                            <p className="experience-summary">{exp.summary}</p>
                                            {exp.bullets && exp.bullets.length > 0 && (
                                                <ul className="experience-bullets">
                                                    {exp.bullets.map((bullet, bIdx) => (
                                                        <li key={bIdx}>{bullet}</li>
                                                    ))}
                                                </ul>
                                            )}
                                        </div>
                                    </article>
                                ))}
                            </div>
                        </div>

                        <div className="col-lg-5">
                            <div className="section-heading">
                                <span className="eyebrow">Education</span>
                                <h2>Qualifications</h2>
                            </div>
                            <div className="d-grid gap-3">
                                {educations.map((edu, index) => (
                                    <article className="education-card info-card" key={edu.id || index}>
                                        <span className="education-year">{edu.start_year} - {edu.end_year}</span>
                                        <h3>{edu.degree} {edu.field ? `in ${edu.field}` : ''}</h3>
                                        <p className="mb-2">{edu.institution}</p>
                                        {edu.description && <p>{edu.description}</p>}
                                    </article>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <SkillsSection skills={skills} />
        </>
    );
}
