import React, { useState } from 'react';
import Alert from './Alert';
import LoadingSpinner from './LoadingSpinner';
import './AuthForm.css';

const AuthForm = ({ onSubmit, isLogin }) => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    passwordConfirm: '',
  });
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
    setError('');
    setFieldErrors((prev) => ({ ...prev, [e.target.name]: '' }));
  };

  const validate = () => {
    const nextErrors = {};
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!isLogin && !formData.name.trim()) {
      nextErrors.name = 'Name is required';
    }

    if (!formData.email.trim()) {
      nextErrors.email = 'Email is required';
    } else if (!emailRegex.test(formData.email.trim())) {
      nextErrors.email = 'Enter a valid email address';
    }

    if (!formData.password) {
      nextErrors.password = 'Password is required';
    } else if (formData.password.length < 6) {
      nextErrors.password = 'Password must be at least 6 characters';
    }

    if (!isLogin) {
      if (!formData.passwordConfirm) {
        nextErrors.passwordConfirm = 'Confirm your password';
      } else if (formData.password !== formData.passwordConfirm) {
        nextErrors.passwordConfirm = 'Passwords do not match';
      }
    }

    return nextErrors;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setFieldErrors({});
    setLoading(true);

    const validationErrors = validate();
    if (Object.keys(validationErrors).length > 0) {
      setFieldErrors(validationErrors);
      setError('Please fix the highlighted fields.');
      setLoading(false);
      return;
    }

    const result = await onSubmit(formData);
    setLoading(false);

    if (!result.success) {
      if (result.fieldErrors && typeof result.fieldErrors === 'object') {
        const mapped = {};
        Object.entries(result.fieldErrors).forEach(([key, messages]) => {
          const message = Array.isArray(messages) ? messages[0] : messages;
          const formKey = key === 'password_confirmation' ? 'passwordConfirm' : key;
          mapped[formKey] = message;
        });
        setFieldErrors(mapped);
      }
      setError(result.error || 'An error occurred');
    }
  };

  return (
    <form onSubmit={handleSubmit} className="auth-form">
      {error && <Alert type="error">{error}</Alert>}

      {!isLogin && (
        <div className="form-group">
          <label htmlFor="name">Name</label>
          <input
            type="text"
            id="name"
            name="name"
            className={`input ${fieldErrors.name ? 'input-error' : ''}`}
            value={formData.name}
            onChange={handleChange}
            required
          />
          {fieldErrors.name && <span className="error-text">{fieldErrors.name}</span>}
        </div>
      )}

      <div className="form-group">
        <label htmlFor="email">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          className={`input ${fieldErrors.email ? 'input-error' : ''}`}
          value={formData.email}
          onChange={handleChange}
          required
        />
        {fieldErrors.email && <span className="error-text">{fieldErrors.email}</span>}
      </div>

      <div className="form-group">
        <label htmlFor="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          className={`input ${fieldErrors.password ? 'input-error' : ''}`}
          value={formData.password}
          onChange={handleChange}
          required
          minLength={6}
        />
        {fieldErrors.password && <span className="error-text">{fieldErrors.password}</span>}
      </div>

      {!isLogin && (
        <div className="form-group">
          <label htmlFor="passwordConfirm">Confirm Password</label>
          <input
            type="password"
            id="passwordConfirm"
            name="passwordConfirm"
            className={`input ${fieldErrors.passwordConfirm ? 'input-error' : ''}`}
            value={formData.passwordConfirm}
            onChange={handleChange}
            required
            minLength={8}
          />
          {fieldErrors.passwordConfirm && <span className="error-text">{fieldErrors.passwordConfirm}</span>}
        </div>
      )}

      <button
        type="submit"
        className="btn btn-primary btn-block"
        disabled={loading}
      >
        {loading ? 'Processing...' : isLogin ? 'Login' : 'Register'}
      </button>
      {loading && <LoadingSpinner text="Submitting..." />}
    </form>
  );
};

export default AuthForm;



