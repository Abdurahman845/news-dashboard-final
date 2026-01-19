import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import ArticleEditor from '../components/ArticleEditor';
import Alert from '../components/Alert';
import LoadingSpinner from '../components/LoadingSpinner';
import { getCategories, createArticle } from '../services/api';
import './CreateArticle.css';

const CreateArticle = () => {
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [categoriesLoading, setCategoriesLoading] = useState(true);
  const [categoriesError, setCategoriesError] = useState(null);

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }
    fetchCategories();
  }, [isAuthenticated, navigate]);

  const fetchCategories = async () => {
    try {
      setCategoriesLoading(true);
      setCategoriesError(null);
      const response = await getCategories();
      setCategories(response.data);
    } catch (error) {
      setCategories([]);
      setCategoriesError('Unable to load categories. Try again later.');
    } finally {
      setCategoriesLoading(false);
    }
  };

  const handleSubmit = async (articleData) => {
    setLoading(true);
    setError(null);
    try {
      const response = await createArticle(articleData);
      navigate(`/article/${response.data.article.id}`);
    } catch (error) {
      const serverErrors = error.response?.data?.errors;
      const message =
        error.response?.data?.message ||
        (Array.isArray(serverErrors) ? serverErrors.join(', ') : null) ||
        'Failed to create article';
      setError(message);
      return {
        success: false,
        error: message,
        fieldErrors: serverErrors,
      };
    } finally {
      setLoading(false);
    }
    return { success: true };
  };

  return (
    <div className="create-article">
      <div className="container">
        <h1 className="page-title">Create New Article</h1>
        {error && <Alert type="error">{error}</Alert>}
        {categoriesError && <Alert type="error">{categoriesError}</Alert>}
        {categoriesLoading && <LoadingSpinner text="Loading categories..." />}
        <ArticleEditor
          onSubmit={handleSubmit}
          categories={categories}
          loading={loading}
        />
      </div>
    </div>
  );
};

export default CreateArticle;



