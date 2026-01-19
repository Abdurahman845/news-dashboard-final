import React, { createContext, useState, useContext, useEffect } from 'react';
import {
  login as apiLogin,
  register as apiRegister,
  getCurrentUser,
  logout as apiLogout,
} from '../services/api';

const AuthContext = createContext();

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Check if user is logged in on mount
    const token = localStorage.getItem('token');
    const savedUser = localStorage.getItem('user');
    
    if (token && savedUser) {
      setUser(JSON.parse(savedUser));
      // Verify token is still valid
      getCurrentUser()
        .then(response => {
          setUser(response.data);
          localStorage.setItem('user', JSON.stringify(response.data));
        })
        .catch(() => {
          // Token is invalid, clear storage
          logout();
        })
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  const login = async (email, password) => {
    try {
      const response = await apiLogin(email, password);
      const { user: userData, token } = response.data;
      
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(userData));
      setUser(userData);
      
      return { success: true };
    } catch (error) {
      const fieldErrors = error.response?.data?.errors || {};
      return {
        success: false,
        error: error.response?.data?.message || 'Login failed',
        fieldErrors,
      };
    }
  };

  const register = async (name, email, password, passwordConfirm) => {
    try {
      const response = await apiRegister({
        name,
        email,
        password,
        password_confirmation: passwordConfirm,
      });
      const { user: userData, token } = response.data;
      
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(userData));
      setUser(userData);
      
      return { success: true };
    } catch (error) {
      const fieldErrors = error.response?.data?.errors || {};
      return {
        success: false,
        error: error.response?.data?.message || 'Registration failed',
        fieldErrors,
      };
    }
  };

  const logout = async () => {
    try {
      await apiLogout();
    } catch (error) {
    } finally {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      setUser(null);
    }
  };

  const value = {
    user,
    loading,
    login,
    register,
    logout,
    isAuthenticated: !!user,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};



