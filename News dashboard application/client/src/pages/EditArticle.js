import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import ArticleEditor from '../components/ArticleEditor';
import Alert from '../components/Alert';
import LoadingSpinner from '../components/LoadingSpinner';
import { getArticle, getCategories, updateArticle } from '../services/api';
import './EditArticle.css';

const EditArticle = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  const [article, setArticle] = useState(null);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [categoriesLoading, setCategoriesLoading] = useState(true);
  const [categoriesError, setCategoriesError] = useState(null);

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }
    fetchArticle();
    fetchCategories();
  }, [id, isAuthenticated, navigate]);

  const fetchArticle = async () => {
    try {
      const response = await getArticle(id);
      const articleData = response.data;
      
      if (user && articleData.user_id !== user.id) {
        navigate('/');
        return;
      }
      
      setArticle(articleData);
    } catch (error) {
      setError('Article not found');
    } finally {
      setLoading(false);
    }
  };

  const fetchCategories = async () => {
    try {
      setCategoriesLoading(true);
      setCategoriesError(null);
      const response = await getCategories();
      setCategories(response.data);
    } catch (error) {
      setCategories([]);
      setCategoriesError('Unable to load categories.');
    } finally {
      setCategoriesLoading(false);
    }
  };

  const handleSubmit = async (articleData) => {
    setSaving(true);
    setError(null);
    try {
      await updateArticle(id, articleData);
      navigate(`/article/${id}`);
    } catch (error) {
      const serverErrors = error.response?.data?.errors;
      const message =
        error.response?.data?.message ||
        (Array.isArray(serverErrors) ? serverErrors.join(', ') : null) ||
        'Failed to update article';
      setError(message);
      return { success: false, error: message, fieldErrors: serverErrors };
    } finally {
      setSaving(false);
    }
    return { success: true };
  };

  if (loading) {
    return (
      <div className="container">
        <LoadingSpinner text="Loading article..." />
      </div>
    );
  }

  if (error && !article) {
    return (
      <div className="container">
        <Alert type="error">{error}</Alert>
        <button onClick={() => navigate('/')} className="btn btn-primary">
          Back to Home
        </button>
      </div>
    );
  }

  return (
    <div className="edit-article">
      <div className="container">
        <h1 className="page-title">Edit Article</h1>
        {error && <Alert type="error">{error}</Alert>}
        {categoriesError && <Alert type="error">{categoriesError}</Alert>}
        {categoriesLoading && <LoadingSpinner text="Loading categories..." />}
        <ArticleEditor
          onSubmit={handleSubmit}
          categories={categories}
          loading={saving}
          initialData={article}
        />
      </div>
    </div>
  );
};

export default EditArticle;



