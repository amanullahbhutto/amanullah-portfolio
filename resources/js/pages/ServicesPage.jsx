import React, { useState, useEffect } from 'react';
import { fetchServices } from '../api/client';
import CtaSection from '../components/CtaSection';

export default function ServicesPage({ initialServices = [], navigate }) {
    const [services, setServices] = useState(initialServices);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!initialServices || initialServices.length === 0) {
            let isMounted = true;
            setLoading(true);
            fetchServices()
                .then((data) => {
                    if (isMounted) {
                        setServices(data);
                        setLoading(false);
                    }
                })
                .catch(() => {
                    if (isMounted) setLoading(false);
                });
            return () => { isMounted = false; };
        }
    }, [initialServices]);

    return (
        <>
            <section className="page-hero">
                <div className="container">
                    <span className="eyebrow">Web development</span>
                    <h1>Services</h1>
                    <p>Practical development support for new applications, existing PHP systems, responsive interfaces, and production deployments.</p>
                </div>
            </section>

            <section className="section-space">
                <div className="container">
                    {loading ? (
                        <div className="text-center py-5">
                            <div className="spinner-border text-accent" role="status">
                                <span className="visually-hidden">Loading services...</span>
                            </div>
                        </div>
                    ) : (
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
                                            Request this service <i className="bi bi-arrow-up-right ms-2"></i>
                                        </button>
                                    </article>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </section>

            <CtaSection navigate={navigate} />
        </>
    );
}
