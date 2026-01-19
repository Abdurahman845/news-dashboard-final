import api from '../config/api';

export const login = (email, password) => api.post('/login', { email, password });

export const register = (payload) => api.post('/register', payload);

export const getCurrentUser = () => api.get('/user');

export const logout = () => api.post('/logout');

export const getArticles = (params) => api.get('/articles', { params });

export const getArticle = (id) => api.get(`/articles/${id}`);

export const createArticle = (payload) => api.post('/articles', payload);

export const updateArticle = (id, payload) => api.put(`/articles/${id}`, payload);

export const deleteArticle = (id) => api.delete(`/articles/${id}`);

export const getCategories = () => api.get('/categories');

export const autoFetchNews = () => api.get('/auto-fetch-news');
