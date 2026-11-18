import React from 'react';
import Hero from '../components/Hero';
import ServicesSection from '../components/ServicesSection';
import ProjectsSection from '../components/ProjectsSection';
import ExperienceSection from '../components/ExperienceSection';
import SkillsSection from '../components/SkillsSection';
import AboutSection from '../components/AboutSection';
import BlogSection from '../components/BlogSection';
import CtaSection from '../components/CtaSection';

export default function HomePage({ data, navigate }) {
    return (
        <>
            <Hero
                profile={data?.profile}
                experienceStat={data?.experience_stat}
                navigate={navigate}
            />

            <ServicesSection
                services={data?.services || []}
                navigate={navigate}
            />

            <ProjectsSection
                projects={data?.projects || []}
                navigate={navigate}
                showViewAll={true}
            />

            <ExperienceSection
                experiences={data?.experiences || []}
                navigate={navigate}
            />

            <SkillsSection
                skills={data?.skills || []}
            />

            <AboutSection
                profile={data?.profile}
                navigate={navigate}
            />

            <BlogSection
                posts={data?.posts || []}
                navigate={navigate}
                showViewAll={true}
            />

            <CtaSection
                navigate={navigate}
            />
        </>
    );
}
