import React from 'react';

export default function CtaSection({ navigate }) {
    return (
        <section className="section-space-sm">
            <div className="container">
                <div className="cta-panel">
                    <span className="eyebrow">Get in touch</span>
                    <h2>Let's build a fast, responsive, and maintainable web application.</h2>
                    <p className="text-muted-custom mt-3 mb-4">
                        Tell me what you need, and I will respond with a practical way forward.
                    </p>
                    <button
                        type="button"
                        className="btn btn-accent"
                        onClick={() => navigate('contact')}
                    >
                        Discuss your project <i className="bi bi-arrow-up-right ms-2"></i>
                    </button>
                </div>
            </div>
        </section>
    );
}
