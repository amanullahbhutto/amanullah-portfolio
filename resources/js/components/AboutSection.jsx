import React from 'react';

export default function AboutSection({ profile, navigate }) {
    const languages = Array.isArray(profile?.languages)
        ? profile.languages.join(', ')
        : (profile?.languages || '');

    return (
        <section className="section-space section-alt" id="about">
            <div className="container">
                <div className="row align-items-center g-5">
                    <div className="col-lg-5">
                        <div className={`about-photo ${profile?.profile_image_url ? 'has-image' : ''}`}>
                            {profile?.profile_image_url && (
                                <img
                                    src={profile.profile_image_url}
                                    alt={`${profile.full_name || 'Amanullah'} profile`}
                                    loading="lazy"
                                />
                            )}
                        </div>
                    </div>

                    <div className="col-lg-7">
                        <div className="about-copy">
                            <span className="eyebrow">About me</span>
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
                                    <span>
                                        <strong>City:</strong> {profile?.city || 'Karachi'}, {profile?.country || 'Pakistan'}
                                    </span>
                                </div>
                                <div className="detail-item">
                                    <i className="bi bi-chevron-right"></i>
                                    <span>
                                        <strong>Languages:</strong> {languages}
                                    </span>
                                </div>
                            </div>
                            <button
                                type="button"
                                className="btn btn-soft"
                                onClick={() => navigate('about')}
                            >
                                More about me <i className="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
