import React from 'react';

export default function SkillsSection({ skills = [] }) {
    return (
        <section className="section-space" id="skills">
            <div className="container">
                <div className="section-heading centered">
                    <span className="eyebrow">Technology stack</span>
                    <h2>Skills</h2>
                    <p>A practical full-stack toolkit for building and maintaining responsive web applications.</p>
                </div>
                <div className="skills-grid">
                    {skills.map((skill, index) => (
                        <div className="skill-item" key={skill.id || index}>
                            <div className="skill-head">
                                <span>{skill.name}</span>
                                <span>{skill.proficiency}%</span>
                            </div>
                            <div className="skill-track">
                                <div
                                    className="skill-fill"
                                    style={{
                                        '--skill-width': `${skill.proficiency}%`,
                                        width: `${skill.proficiency}%`
                                    }}
                                ></div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
