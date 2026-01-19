import React from 'react';
import ArticleCard from './ArticleCard';
import LoadingSpinner from './LoadingSpinner';
import Alert from './Alert';
import { Link } from 'react-router-dom';
import './ArticleList.css';

const ArticleList = ({ articles, loading }) => {
  if (loading) {
    return <LoadingSpinner text="Loading articles..." />;
  }

  if (!articles || articles.length === 0) {
    return (
      <div className="empty-state">
        <div className="empty-icon" aria-hidden="true">📰</div>
        <p>No articles found. Try adjusting your search or picking another category.</p>
        <Link to="/create" className="btn btn-primary">Create article</Link>
      </div>
    );
  }

  return (
    <div className="article-list">
      {articles.map((article) => (
        <ArticleCard key={article.id} article={article} />
      ))}
    </div>
  );
};

export default ArticleList;



