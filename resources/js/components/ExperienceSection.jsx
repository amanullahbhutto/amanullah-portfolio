import React from 'react';

export default function ExperienceSection({ experiences = [], navigate }) {
    return (
        <section className="section-space section-alt" id="experience">
            <div className="container">
                <div className="row g-5">
                    <div className="col-lg-7">
                        <div className="section-heading">
                            <span className="eyebrow">Career journey</span>
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
                            <span className="eyebrow">Let's connect</span>
                            <h2>Ready to build something useful?</h2>
                            <p>Feel free to reach out for Laravel development, PHP backend work, responsive interfaces, maintenance, or deployment support.</p>
                        </div>
                        <button
                            type="button"
                            className="btn btn-accent"
                            onClick={() => navigate('contact')}
                        >
                            Start a conversation <i className="bi bi-arrow-up-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    );
}
