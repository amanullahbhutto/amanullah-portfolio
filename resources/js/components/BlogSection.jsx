import React from 'react';

export default function BlogSection({ posts = [], navigate, showViewAll = true }) {
    if (!posts || posts.length === 0) {
        return null;
    }

    return (
        <section className="section-space" id="blog">
            <div className="container">
                <div className="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-5">
                    <div className="section-heading mb-0">
                        <span className="eyebrow">Development notes</span>
                        <h2>Latest blog posts</h2>
                        <p>Practical ideas about Laravel, responsive interfaces, and safer deployment.</p>
                    </div>
                    {showViewAll && (
                        <button
                            type="button"
                            className="btn btn-outline-theme"
                            onClick={() => navigate('blog')}
                        >
                            Browse all posts <i className="bi bi-arrow-right ms-2"></i>
                        </button>
                    )}
                </div>

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
            </div>
        </section>
    );
}
