import React, { useState, useEffect } from 'react';
import { fetchPosts } from '../api/client';

export default function BlogPage({ initialPosts = [], navigate }) {
    const [posts, setPosts] = useState(initialPosts);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);
        fetchPosts()
            .then((data) => {
                if (isMounted) {
                    setPosts(data);
                    setLoading(false);
                }
            })
            .catch(() => {
                if (isMounted) setLoading(false);
            });
        return () => { isMounted = false; };
    }, []);

    return (
        <>
            <section className="page-hero">
                <div className="container">
                    <span className="eyebrow">Development notes</span>
                    <h1>Blog Posts</h1>
                    <p>Practical thoughts about Laravel development, responsive interfaces, deployment, and maintainable web applications.</p>
                </div>
            </section>

            <section className="section-space">
                <div className="container">
                    {loading ? (
                        <div className="text-center py-5">
                            <div className="spinner-border text-accent" role="status">
                                <span className="visually-hidden">Loading blog posts...</span>
                            </div>
                        </div>
                    ) : posts.length === 0 ? (
                        <div className="col-12">
                            <div className="info-card p-5 text-center">
                                <h2>Posts are being prepared</h2>
                                <p className="text-muted-custom mb-0">Please check again soon.</p>
                            </div>
                        </div>
                    ) : (
                        <div className="row g-4">
                            {posts.map((post, index) => (
                                <div className="col-md-6 col-xl-4" key={post.id || index}>
                                    <article className="blog-card">
                                        <div className="blog-body">
                                            <span className="blog-date">{post.published_at}</span>
                                            <h3 className="mt-3">
                                                <a
                                                    href={`#post-${post.slug}`}
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        navigate('post-detail', null, { slug: post.slug });
                                                    }}
                                                >
                                                    {post.title}
                                                </a>
                                            </h3>
                                            <p>{post.excerpt}</p>
                                            <button
                                                type="button"
                                                className="card-arrow bg-transparent border-0 p-0 text-start"
                                                onClick={() => navigate('post-detail', null, { slug: post.slug })}
                                            >
                                                Read article <i className="bi bi-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </article>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </>
    );
}
