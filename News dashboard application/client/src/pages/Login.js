import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import AuthForm from '../components/AuthForm';
import './Login.css';

const Login = () => {
  const [isLogin, setIsLogin] = useState(true);
  const { login, register, isAuthenticated } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (isAuthenticated) {
      navigate('/');
    }
  }, [isAuthenticated, navigate]);

  const handleSubmit = async (formData) => {
    if (isLogin) {
      const result = await login(formData.email, formData.password);
      if (result.success) {
        navigate('/');
      }
      return result;
    } else {
      const result = await register(
        formData.name,
        formData.email,
        formData.password,
        formData.passwordConfirm
      );
      if (result.success) {
        navigate('/');
      }
      return result;
    }
  };

  return (
    <div className="login-page">
      <div className="container">
        <div className="auth-container">
          <h1>{isLogin ? 'Login' : 'Register'}</h1>
          <AuthForm onSubmit={handleSubmit} isLogin={isLogin} />
          <div className="auth-switch">
            <p>
              {isLogin ? "Don't have an account? " : 'Already have an account? '}
              <button
                onClick={() => setIsLogin(!isLogin)}
                className="link-button"
              >
                {isLogin ? 'Register' : 'Login'}
              </button>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Login;



