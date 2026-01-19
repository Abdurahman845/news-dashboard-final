import React, { useState } from 'react';
import './ArticleEditor.css';

const ArticleEditor = ({ onSubmit, categories, loading, initialData }) => {
  const [formData, setFormData] = useState({
    title: initialData?.title || '',
    content: initialData?.content || '',
    excerpt: initialData?.excerpt || '',
    image_url: initialData?.image_url || '',
    category_id: initialData?.category_id || '',
    published_at: initialData?.published_at
      ? new Date(initialData.published_at).toISOString().split('T')[0]
      : '',
  });

  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors({
        ...errors,
        [name]: null,
      });
    }
  };

  const validate = () => {
    const nextErrors = {};
    const trimmedTitle = formData.title.trim();
    const trimmedContent = formData.content.trim();
    const trimmedExcerpt = formData.excerpt.trim();

    if (!trimmedTitle) {
      nextErrors.title = 'Title is required';
    } else if (trimmedTitle.length < 3) {
      nextErrors.title = 'Title must be at least 3 characters';
    }

    if (!trimmedContent) {
      nextErrors.content = 'Content is required';
    } else if (trimmedContent.length < 10) {
      nextErrors.content = 'Content must be at least 10 characters';
    }

    if (!trimmedExcerpt) {
      nextErrors.excerpt = 'Summary is required';
    } else if (trimmedExcerpt.length < 10) {
      nextErrors.excerpt = 'Summary must be at least 10 characters';
    }

    if (!formData.category_id) {
      nextErrors.category_id = 'Category is required';
    }

    if (formData.image_url && !/^https?:\/\/.+/i.test(formData.image_url)) {
      nextErrors.image_url = 'Image URL must start with http or https';
    }

    return nextErrors;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (loading) return;

    const newErrors = validate();
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    const articleData = {
      ...formData,
      published_at: formData.published_at || null,
    };

    const result = await onSubmit(articleData);
    if (!result?.success) {
      if (result.fieldErrors && typeof result.fieldErrors === 'object') {
        const serverErrors = {};
        Object.entries(result.fieldErrors).forEach(([key, messages]) => {
          const message = Array.isArray(messages) ? messages[0] : messages;
          serverErrors[key] = message;
        });
        setErrors((prev) => ({ ...prev, ...serverErrors }));
      } else {
        setErrors({ submit: result?.error || 'Failed to save article' });
      }
    } else {
      setErrors({});
    }
  };

  return (
    <div className="article-editor">
      <form onSubmit={handleSubmit} className="editor-form">
        <div className="form-group">
          <label htmlFor="title">Title <span className="required">*</span></label>
          <input
            type="text"
            id="title"
            name="title"
            className={`input ${errors.title ? 'input-error' : ''}`}
            value={formData.title}
            onChange={handleChange}
            placeholder="Enter article title"
          />
          {errors.title && <span className="error-text">{errors.title}</span>}
        </div>

        <div className="form-group">
          <label htmlFor="excerpt">Excerpt <span className="required">*</span></label>
          <textarea
            id="excerpt"
            name="excerpt"
            className={`textarea ${errors.excerpt ? 'input-error' : ''}`}
            rows="3"
            value={formData.excerpt}
            onChange={handleChange}
            placeholder="Brief summary of the article (min 10 characters)"
          />
          {errors.excerpt && <span className="error-text">{errors.excerpt}</span>}
        </div>

        <div className="form-group">
          <label htmlFor="content">Content <span className="required">*</span></label>
          <textarea
            id="content"
            name="content"
            className="textarea"
            rows="12"
            value={formData.content}
            onChange={handleChange}
            placeholder="Write your article content here..."
            required
          />
          {errors.content && (
            <span className="error-text">{errors.content}</span>
          )}
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="category_id">Category <span className="required">*</span></label>
            <select
              id="category_id"
              name="category_id"
              className={`input ${errors.category_id ? 'input-error' : ''}`}
              value={formData.category_id}
              onChange={handleChange}
              required
            >
              <option value="">Select a category</option>
              {categories.map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
            {errors.category_id && (
              <span className="error-text">{errors.category_id}</span>
            )}
          </div>

          <div className="form-group">
            <label htmlFor="image_url">Image URL</label>
            <input
              type="url"
              id="image_url"
              name="image_url"
              className={`input ${errors.image_url ? 'input-error' : ''}`}
              value={formData.image_url}
              onChange={handleChange}
              placeholder="https://example.com/image.jpg"
            />
            {errors.image_url && (
              <span className="error-text">{errors.image_url}</span>
            )}
          </div>

          <div className="form-group">
            <label htmlFor="published_at">Publish Date</label>
            <input
              type="date"
              id="published_at"
              name="published_at"
              className="input"
              value={formData.published_at}
              onChange={handleChange}
            />
            <small className="help-text">
              Leave empty to save as draft
            </small>
          </div>
        </div>

        {errors.submit && (
          <div className="error">{errors.submit}</div>
        )}

        <div className="form-actions">
          <button
            type="button"
            className="btn btn-secondary"
            onClick={() => window.history.back()}
          >
            Cancel
          </button>
          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading}
          >
            {loading ? 'Saving...' : 'Save Article'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ArticleEditor;



