import React, { useState } from 'react';
import { submitContactForm } from '../api/client';

export default function ContactPage({ profile, initialParams = {} }) {
    const defaultSubject = initialParams?.hire
        ? 'Hiring inquiry'
        : (initialParams?.service ? `Project inquiry: ${initialParams.service.replace('-', ' ')}` : '');

    const [form, setForm] = useState({
        name: '',
        email: '',
        phone: '',
        subject: defaultSubject,
        message: '',
        website: '' // honeypot
    });

    const [loading, setLoading] = useState(false);
    const [successMessage, setSuccessMessage] = useState(null);
    const [errorMessage, setErrorMessage] = useState(null);
    const [errors, setErrors] = useState({});

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({ ...prev, [name]: value }));
        if (errors[name]) {
            setErrors((prev) => {
                const updated = { ...prev };
                delete updated[name];
                return updated;
            });
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setSuccessMessage(null);
        setErrorMessage(null);
        setErrors({});

        try {
            const res = await submitContactForm(form);
            if (res.success) {
                setSuccessMessage(res.message || 'Thank you! Your message has been sent successfully.');
                setForm({
                    name: '',
                    email: '',
                    phone: '',
                    subject: '',
                    message: '',
                    website: ''
                });
            } else {
                setErrorMessage(res.message || 'Failed to send message. Please try again.');
            }
        } catch (err) {
            if (err.response?.status === 422 && err.response?.data?.errors) {
                setErrors(err.response.data.errors);
            } else {
                setErrorMessage(err.response?.data?.message || 'Something went wrong. Please check your network or try again.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <section className="section-space" style={{ paddingTop: 'calc(var(--header-height) + 70px)' }}>
            <div className="container">
                <div className="contact-panel">
                    <div className="row g-0">
                        <div className="col-lg-5">
                            <div className="contact-copy">
                                <span className="eyebrow">Get in touch</span>
                                <h1>Let's work together</h1>
                                <p>Share your project goals, existing technology, and preferred timeline. I will review the details and get back to you.</p>
                                <div className="contact-list">
                                    <div className="contact-row">
                                        <i className="bi bi-envelope"></i>
                                        {profile?.email && (
                                            <a
                                                className="gmail-link"
                                                href={profile.gmail_compose_url || `mailto:${profile.email}`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                {profile.email}
                                            </a>
                                        )}
                                    </div>
                                    <div className="contact-row">
                                        <i className="bi bi-whatsapp"></i>
                                        <div>
                                            {profile?.phone && (
                                                <a
                                                    className="whatsapp-link"
                                                    href={profile.whatsapp_url || `tel:${profile.phone}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    {profile.phone}
                                                </a>
                                            )}
                                        </div>
                                    </div>
                                    <div className="contact-row">
                                        <i className="bi bi-geo-alt"></i>
                                        <span>{profile?.address || 'Karachi'}, {profile?.city || 'Pakistan'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="col-lg-7">
                            <div className="contact-form-wrap">
                                {successMessage && (
                                    <div className="alert alert-success d-flex align-items-start gap-2 mb-4" role="alert" style={{ borderRadius: '14px', background: 'rgba(34, 197, 94, 0.15)', border: '1px solid rgba(34, 197, 94, 0.4)', color: '#86efac' }}>
                                        <i className="bi bi-check-circle-fill fs-5 flex-shrink-0 text-success mt-1"></i>
                                        <div>
                                            <strong className="d-block mb-1">Message Received!</strong>
                                            <div className="small">{successMessage}</div>
                                        </div>
                                    </div>
                                )}

                                {errorMessage && (
                                    <div className="alert alert-danger d-flex align-items-start gap-2 mb-4" role="alert" style={{ borderRadius: '14px', background: 'rgba(239, 68, 68, 0.15)', border: '1px solid rgba(239, 68, 68, 0.4)', color: '#fca5a5' }}>
                                        <i className="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 text-danger mt-1"></i>
                                        <div>
                                            <strong className="d-block mb-1">Error</strong>
                                            <div className="small">{errorMessage}</div>
                                        </div>
                                    </div>
                                )}

                                <form onSubmit={handleSubmit}>
                                    <div style={{ display: 'none' }}>
                                        <input
                                            type="text"
                                            name="website"
                                            value={form.website}
                                            onChange={handleChange}
                                            tabIndex="-1"
                                            autoComplete="off"
                                        />
                                    </div>

                                    <div className="row g-3">
                                        <div className="col-md-6">
                                            <label className="form-label" htmlFor="name">Name *</label>
                                            <input
                                                className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                                id="name"
                                                name="name"
                                                value={form.name}
                                                onChange={handleChange}
                                                required
                                                maxLength="100"
                                                autoComplete="name"
                                                placeholder="Your name"
                                            />
                                            {errors.name && <div className="invalid-feedback">{errors.name[0]}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label" htmlFor="email">Email *</label>
                                            <input
                                                className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                                type="email"
                                                id="email"
                                                name="email"
                                                value={form.email}
                                                onChange={handleChange}
                                                required
                                                maxLength="150"
                                                autoComplete="email"
                                                placeholder="you@company.com"
                                            />
                                            {errors.email && <div className="invalid-feedback">{errors.email[0]}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label" htmlFor="phone">Phone</label>
                                            <input
                                                className={`form-control ${errors.phone ? 'is-invalid' : ''}`}
                                                id="phone"
                                                name="phone"
                                                value={form.phone}
                                                onChange={handleChange}
                                                maxLength="30"
                                                autoComplete="tel"
                                                placeholder="Your phone number"
                                            />
                                            {errors.phone && <div className="invalid-feedback">{errors.phone[0]}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label" htmlFor="subject">Subject *</label>
                                            <input
                                                className={`form-control ${errors.subject ? 'is-invalid' : ''}`}
                                                id="subject"
                                                name="subject"
                                                value={form.subject}
                                                onChange={handleChange}
                                                required
                                                maxLength="150"
                                                placeholder="Project inquiry"
                                            />
                                            {errors.subject && <div className="invalid-feedback">{errors.subject[0]}</div>}
                                        </div>

                                        <div className="col-12">
                                            <label className="form-label" htmlFor="message">Message *</label>
                                            <textarea
                                                className={`form-control ${errors.message ? 'is-invalid' : ''}`}
                                                id="message"
                                                name="message"
                                                rows="5"
                                                value={form.message}
                                                onChange={handleChange}
                                                required
                                                minLength="10"
                                                maxLength="5000"
                                                placeholder="Tell me about your project, timeline, and goals..."
                                            ></textarea>
                                            {errors.message && <div className="invalid-feedback">{errors.message[0]}</div>}
                                        </div>

                                        <div className="col-12">
                                            <button
                                                className="btn btn-accent w-100"
                                                type="submit"
                                                disabled={loading}
                                            >
                                                {loading ? (
                                                    <>
                                                        <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                        Sending...
                                                    </>
                                                ) : (
                                                    <>
                                                        Send message <i className="bi bi-arrow-right ms-2"></i>
                                                    </>
                                                )}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
