import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import ArticleList from '../components/ArticleList';
import LoadingSpinner from '../components/LoadingSpinner';
import Alert from '../components/Alert';
import { getArticles, getCategories, autoFetchNews as fetchAutoNews } from '../services/api';
import './Home.css';

const Home = () => {
  const { isAuthenticated } = useAuth();
  const [articles, setArticles] = useState([]);
  const [allArticles, setAllArticles] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [categoriesLoading, setCategoriesLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [error, setError] = useState(null);
  const [categoryError, setCategoryError] = useState(null);
  const [statusMessage, setStatusMessage] = useState(null);
  const [resultCount, setResultCount] = useState(0);
  const perPage = 9;

  useEffect(() => {
    fetchCategories();
    autoFetchNews();
  }, []);

  useEffect(() => {
    fetchArticles();
  }, [search, selectedCategory, currentPage]);

  const autoFetchNews = async () => {
    try {
      const response = await fetchAutoNews();
      if (response.data && (response.data.imported > 0 || response.data.skipped > 0)) {
        setTimeout(() => {
          fetchArticles();
        }, 1000);
        setStatusMessage('Fetching latest articles...');
      }
    } catch (error) {
      setStatusMessage(null);
    }
  };

  const fetchCategories = async () => {
    try {
      setCategoriesLoading(true);
      setCategoryError(null);
      const response = await getCategories();
      setCategories(response.data);
    } catch (error) {
      setCategoryError('Could not load categories.');
      setCategories([]);
    } finally {
      setCategoriesLoading(false);
    }
  };

  const fetchArticles = async () => {
    setLoading(true);
    setError(null);
    try {
      const params = {};
      if (search) params.search = search;
      if (selectedCategory) params.category = selectedCategory;

      const response = await getArticles(params);
      const articlesData = Array.isArray(response.data) ? response.data : [];
      setAllArticles(articlesData);
      setResultCount(articlesData.length);

      const totalPages = Math.max(1, Math.ceil(articlesData.length / perPage));
      setLastPage(totalPages);

      const start = (currentPage - 1) * perPage;
      const end = start + perPage;
      setArticles(articlesData.slice(start, end));
    } catch (error) {
      setError('Failed to load articles. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    setCurrentPage(1);
    fetchArticles();
  };

  const handleClearFilters = () => {
    setSearch('');
    setSelectedCategory('');
    setCurrentPage(1);
    setStatusMessage(null);
    setError(null);
  };

  const handleCategoryChange = (e) => {
    setSelectedCategory(e.target.value);
    setCurrentPage(1);
  };

  const handlePageChange = (page) => {
    const nextPage = Math.max(1, Math.min(page, lastPage));
    setCurrentPage(nextPage);
    const start = (nextPage - 1) * perPage;
    const end = start + perPage;
    setArticles(allArticles.slice(start, end));
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="home">
      <div className="container">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
          <h1 className="page-title">Latest News</h1>
        </div>

        <div className="filters-card">
          <form onSubmit={handleSearch} className="search-form">
            <div className="filters-grid">
              <input
                type="text"
                className="input"
                placeholder="Search articles..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
              <select
                className="input"
                value={selectedCategory}
                onChange={handleCategoryChange}
                disabled={categoriesLoading}
              >
                <option value="">All Categories</option>
                {categories.map((category) => (
                  <option key={category.id} value={category.slug}>
                    {category.name}
                  </option>
                ))}
              </select>
              <button type="submit" className="btn btn-primary">Search</button>
              <button type="button" className="btn btn-secondary" onClick={handleClearFilters} disabled={!search && !selectedCategory}>
                Clear filters
              </button>
            </div>
          </form>
          {categoriesLoading && <LoadingSpinner text="Loading categories..." />}
          {categoryError && <Alert type="error" className="small-error">{categoryError}</Alert>}
        </div>

        {error && <Alert type="error">{error}</Alert>}
        {statusMessage && !error && <Alert type="info">{statusMessage}</Alert>}
        {!loading && !error && resultCount !== null && (
          <div className="status-text results-count">
            Showing {resultCount} {resultCount === 1 ? 'article' : 'articles'}
          </div>
        )}

        <ArticleList articles={articles} loading={loading} />

        {!loading && lastPage > 1 && (
          <div className="pagination">
            <button
              className="btn btn-secondary"
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage === 1}
            >
              Previous
            </button>
            <span className="page-info">
              Page {currentPage} of {lastPage}
            </span>
            <button
              className="btn btn-primary"
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage === lastPage}
            >
              Next
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

export default Home;



