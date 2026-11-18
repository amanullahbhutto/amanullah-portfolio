import React from 'react';

export default function ServicesSection({ services = [], navigate }) {
    return (
        <section className="section-space section-alt" id="services">
            <div className="container">
                <div className="section-heading centered">
                    <span className="eyebrow">What I do</span>
                    <h2>Web solutions built for real business needs</h2>
                    <p>From Laravel backends to responsive Bootstrap interfaces, every solution is designed for performance, maintainability, and growth.</p>
                </div>
                <div className="row g-4">
                    {services.map((service, index) => (
                        <div className="col-md-6 col-xl-4" key={service.id || index}>
                            <article className="service-card">
                                <div className="service-icon">
                                    <i className={`bi ${service.icon || 'bi-gear'}`}></i>
                                </div>
                                <h3>{service.title}</h3>
                                <p>{service.short_description}</p>
                                <button
                                    type="button"
                                    className="card-arrow bg-transparent border-0 p-0 text-start"
                                    onClick={() => navigate('contact', null, { service: service.slug })}
                                >
                                    Discuss a project <i className="bi bi-arrow-up-right ms-2"></i>
                                </button>
                            </article>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
