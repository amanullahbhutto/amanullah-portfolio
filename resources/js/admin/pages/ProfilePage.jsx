import React, { useState, useEffect } from 'react';
import axios from 'axios';

export default function ProfilePage({ getApiBaseUrl }) {
    const [profile, setProfile] = useState({});
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [alert, setAlert] = useState(null);

    useEffect(() => {
        let isMounted = true;
        axios.get(`${getApiBaseUrl()}/admin/profile`)
            .then((res) => {
                if (isMounted) {
                    setProfile(res.data.profile || {});
                    setLoading(false);
                }
            })
            .catch(() => {
                if (isMounted) setLoading(false);
            });
        return () => { isMounted = false; };
    }, [getApiBaseUrl]);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setProfile((prev) => ({
            ...prev,
            [name]: type === 'checkbox' ? (checked ? 1 : 0) : value,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setAlert(null);

        try {
            await axios.put(`${getApiBaseUrl()}/admin/profile`, profile);
            setAlert({ type: 'success', text: 'Profile details updated successfully!' });
        } catch {
            setAlert({ type: 'danger', text: 'Failed to update profile.' });
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="text-center py-5">
                <div className="spinner-border text-accent" role="status">
                    <span className="visually-hidden">Loading profile...</span>
                </div>
            </div>
        );
    }

    return (
        <div className="admin-profile-view">
            <section className="admin-card">
                <div className="admin-card-head">
                    <div>
                        <h2>Portfolio Profile Settings</h2>
                        <p className="text-muted-custom small mb-0">Update your public biography, contact channels, and skills display</p>
                    </div>
                </div>

                <div className="p-4">
                    {alert && (
                        <div className={`alert alert-${alert.type} alert-dismissible fade show mb-4`} role="alert">
                            {alert.text}
                            <button type="button" className="btn-close" onClick={() => setAlert(null)}></button>
                        </div>
                    )}

                    <form onSubmit={handleSubmit}>
                        <div className="row g-4">
                            <div className="col-md-6">
                                <label className="form-label">Full Name *</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    name="full_name"
                                    value={profile.full_name || ''}
                                    onChange={handleChange}
                                    required
                                />
                            </div>

                            <div className="col-md-6">
                                <label className="form-label">Headline *</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    name="headline"
                                    value={profile.headline || ''}
                                    onChange={handleChange}
                                    required
                                />
                            </div>

                            <div className="col-md-6">
                                <label className="form-label">Email Address *</label>
                                <input
                                    type="email"
                                    className="form-control"
                                    name="email"
                                    value={profile.email || ''}
                                    onChange={handleChange}
                                    required
                                />
                            </div>

                            <div className="col-md-6">
                                <label className="form-label">Phone / WhatsApp</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    name="phone"
                                    value={profile.phone || ''}
                                    onChange={handleChange}
                                />
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">City</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    name="city"
                                    value={profile.city || ''}
                                    onChange={handleChange}
                                />
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">Country</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    name="country"
                                    value={profile.country || ''}
                                    onChange={handleChange}
                                />
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">Availability Status</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    name="availability"
                                    value={profile.availability || ''}
                                    onChange={handleChange}
                                    placeholder="Available for work"
                                />
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">Years of Experience (Manual)</label>
                                <input
                                    type="number"
                                    className="form-control"
                                    name="years_experience"
                                    value={profile.years_experience || 2}
                                    onChange={handleChange}
                                />
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">Projects Count</label>
                                <input
                                    type="number"
                                    className="form-control"
                                    name="project_count"
                                    value={profile.project_count || 12}
                                    onChange={handleChange}
                                />
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">Happy Clients</label>
                                <input
                                    type="number"
                                    className="form-control"
                                    name="happy_clients"
                                    value={profile.happy_clients || 8}
                                    onChange={handleChange}
                                />
                            </div>

                            <div className="col-12">
                                <label className="form-label">Short Bio (Hero section)</label>
                                <textarea
                                    className="form-control"
                                    rows="2"
                                    name="short_bio"
                                    value={profile.short_bio || ''}
                                    onChange={handleChange}
                                ></textarea>
                            </div>

                            <div className="col-12">
                                <label className="form-label">Long Bio (About Me section)</label>
                                <textarea
                                    className="form-control"
                                    rows="5"
                                    name="long_bio"
                                    value={profile.long_bio || ''}
                                    onChange={handleChange}
                                ></textarea>
                            </div>

                            <div className="col-md-6">
                                <label className="form-label">GitHub URL</label>
                                <input
                                    type="url"
                                    className="form-control"
                                    name="github_url"
                                    value={profile.github_url || ''}
                                    onChange={handleChange}
                                    placeholder="https://github.com/..."
                                />
                            </div>

                            <div className="col-md-6">
                                <label className="form-label">LinkedIn URL</label>
                                <input
                                    type="url"
                                    className="form-control"
                                    name="linkedin_url"
                                    value={profile.linkedin_url || ''}
                                    onChange={handleChange}
                                    placeholder="https://linkedin.com/in/..."
                                />
                            </div>

                            <div className="col-12">
                                <div className="form-check form-switch">
                                    <input
                                        className="form-check-input"
                                        type="checkbox"
                                        id="use_experience_dates"
                                        name="use_experience_dates"
                                        checked={!!profile.use_experience_dates}
                                        onChange={handleChange}
                                    />
                                    <label className="form-check-label" htmlFor="use_experience_dates">
                                        Calculate total experience automatically from career dates (instead of manual number)
                                    </label>
                                </div>
                            </div>

                            <div className="col-12 pt-3 border-top">
                                <button type="submit" className="btn btn-accent px-4" disabled={saving}>
                                    {saving ? 'Saving...' : 'Save Profile Changes'}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    );
}
