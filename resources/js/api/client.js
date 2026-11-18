import axios from 'axios';

// Automatically detect base URL so it works on localhost/amanullah-portfolio/amanullah/public, artisan serve, or live server
const getApiBaseUrl = () => {
    const path = window.location.pathname;
    const match = path.match(/^(.*\/public)/);
    const prefix = match ? match[1] : '';
    return `${prefix}/api`;
};

const api = axios.create({
    baseURL: getApiBaseUrl(),
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

export const fetchPortfolioData = async () => {
    const response = await api.get('/portfolio');
    return response.data;
};

export const fetchServices = async () => {
    const response = await api.get('/services');
    return response.data.services;
};

export const fetchProjects = async (type = '') => {
    const response = await api.get('/projects', { params: type ? { type } : {} });
    return response.data.projects;
};

export const fetchProjectDetail = async (slug) => {
    const response = await api.get(`/projects/${slug}`);
    return response.data;
};

export const fetchPosts = async () => {
    const response = await api.get('/posts');
    return response.data.posts;
};

export const fetchPostDetail = async (slug) => {
    const response = await api.get(`/posts/${slug}`);
    return response.data;
};

export const submitContactForm = async (formData) => {
    const response = await api.post('/contact', formData);
    return response.data;
};

export default api;
