import React from 'react';
import { Link } from 'react-router-dom';
import './ArticleCard.css';

const ArticleCard = ({ article }) => {
  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  return (
    <article className="article-card">
      <div className={`article-image ${article.image_url ? '' : 'no-image'}`}>
        {article.image_url && (
          <img src={article.image_url} alt={article.title} />
        )}
      </div>
      <div className="article-content">
        <div className="article-meta">
          <span className="category">{article.category_name || article.category?.name}</span>
          <span className="date">
            {formatDate(article.published_at || article.created_at)}
          </span>
        </div>
        <h2 className="article-title">
          <Link to={`/article/${article.id}`}>{article.title}</Link>
        </h2>
        {article.excerpt && (
          <p className="article-excerpt">{article.excerpt}</p>
        )}
        <div className="article-footer">
          <span className="author">By {article.user_name || article.user?.name || 'Unknown'}</span>
          <Link to={`/article/${article.id}`} className="read-more">
            Read More →
          </Link>
        </div>
      </div>
    </article>
  );
};

export default ArticleCard;



