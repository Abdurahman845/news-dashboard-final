import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { getArticle, deleteArticle } from '../services/api';
import LoadingSpinner from '../components/LoadingSpinner';
import Alert from '../components/Alert';
import './ArticlePage.css';

const ArticlePage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user } = useAuth();
  const [article, setArticle] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [deleting, setDeleting] = useState(false);

  useEffect(() => {
    fetchArticle();
  }, [id]);

  const fetchArticle = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await getArticle(id);
      setArticle(response.data);
    } catch (error) {
      setError('Article not found');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to delete this article?')) {
      return;
    }

    setDeleting(true);
    try {
      await deleteArticle(id);
      navigate('/');
    } catch (error) {
      alert('Failed to delete article');
    } finally {
      setDeleting(false);
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  if (loading) {
    return (
      <div className="container">
        <LoadingSpinner text="Loading article..." />
      </div>
    );
  }

  if (error || !article) {
    return (
      <div className="container">
        <Alert type="error">{error || 'Article not found'}</Alert>
        <Link to="/" className="btn btn-primary">Back to Home</Link>
      </div>
    );
  }

  const isOwner = user && article.user_id === user.id;

  return (
    <div className="article-page">
      <div className="container">
        <div className="article-breadcrumb">
          <Link to="/">← Back to Home</Link>
        </div>

        <div className="article-hero">
          <div className="article-hero-main">
            <div className="article-meta">
              <span className="category">{article.category?.name}</span>
              <span className="date">
                {formatDate(article.published_at || article.created_at)}
              </span>
            </div>
            <h1 className="article-title">{article.title}</h1>
            <div className="article-author">
              By <strong>{article.user?.name}</strong>
            </div>
          </div>
          {isOwner && (
            <div className="article-actions">
              <Link to={`/edit/${article.id}`} className="btn btn-primary">
                Edit
              </Link>
              <button
                onClick={handleDelete}
                disabled={deleting}
                className="btn btn-danger"
              >
                {deleting ? 'Deleting...' : 'Delete'}
              </button>
            </div>
          )}
        </div>

        <article className="article-full">
          {article.image_url && (
            <div className="article-image-full">
              <img src={article.image_url} alt={article.title} />
            </div>
          )}

          <div
            className="article-content-full"
            dangerouslySetInnerHTML={{ __html: article.content.replace(/\n/g, '<br />') }}
          />
        </article>
      </div>
    </div>
  );
};

export default ArticlePage;



