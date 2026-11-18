import React, { useState, useEffect } from 'react';
import { fetchPostDetail } from '../api/client';

export default function PostDetailPage({ slug, navigate }) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);
        fetchPostDetail(slug)
            .then((res) => {
                if (isMounted) {
                    setData(res.post);
                    setLoading(false);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            })
            .catch(() => {
                if (isMounted) setLoading(false);
            });
        return () => { isMounted = false; };
    }, [slug]);

    if (loading) {
        return (
            <div className="container py-5 text-center" style={{ minHeight: '60vh', paddingTop: '150px' }}>
                <div className="spinner-border text-accent" role="status">
                    <span className="visually-hidden">Loading article...</span>
                </div>
            </div>
        );
    }

    if (!data) {
        return (
            <div className="container py-5 text-center" style={{ minHeight: '60vh', paddingTop: '150px' }}>
                <h2>Article Not Found</h2>
                <p className="text-muted-custom">The requested post could not be found or has not been published yet.</p>
                <button type="button" className="btn btn-accent mt-3" onClick={() => navigate('blog')}>
                    Back to Blog
                </button>
            </div>
        );
    }

    return (
        <section className="section-space" style={{ paddingTop: 'calc(var(--header-height) + 80px)' }}>
            <div className="container">
                <article className="article-wrap">
                    <div className="breadcrumbs">
                        <a href="#home" onClick={(e) => { e.preventDefault(); navigate('home'); }}>Home</a>
                        <span>/</span>
                        <a href="#blog" onClick={(e) => { e.preventDefault(); navigate('blog'); }}>Blog</a>
                        <span>/</span>
                        <span className="text-truncate" style={{ maxWidth: '250px' }}>{data.title}</span>
                    </div>

                    <header className="article-header">
                        <span className="blog-date">{data.published_at_full || data.published_at}</span>
                        <h1 className="mt-3">{data.title}</h1>
                        <p className="hero-lead">{data.excerpt}</p>
                    </header>

                    {data.image_url && (
                        <div className="article-image my-4">
                            <img src={data.image_url} alt={data.title} style={{ width: '100%', borderRadius: '12px' }} />
                        </div>
                    )}

                    <div className="article-content">
                        {data.content && data.content.split('\n').map((paragraph, idx) => (
                            paragraph.trim() ? <p key={idx}>{paragraph}</p> : null
                        ))}
                    </div>

                    <div className="cta-panel mt-5">
                        <span className="eyebrow">Have a project?</span>
                        <h2 className="h2">Let's discuss your web application.</h2>
                        <button
                            type="button"
                            className="btn btn-accent mt-3"
                            onClick={() => navigate('contact')}
                        >
                            Get in touch
                        </button>
                    </div>
                </article>
            </div>
        </section>
    );
}
