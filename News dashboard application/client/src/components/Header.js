import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import './Header.css';

const Header = () => {
  const { user, logout, isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    navigate('/');
    setMenuOpen(false);
  };

  const closeMenu = () => setMenuOpen(false);

  const toggleMenu = () => {
    setMenuOpen((prev) => !prev);
  };

  return (
    <header className="header">
      <div className="container header-container">
        <Link to="/" className="logo" onClick={closeMenu}>
          <h1>News Dashboard</h1>
        </Link>

        <button
          className="nav-toggle"
          aria-label="Toggle menu"
          onClick={toggleMenu}
        >
          ☰
        </button>

        <nav className={`nav ${menuOpen ? 'nav-open' : ''}`}>
          <Link to="/" className="nav-link" onClick={closeMenu}>Home</Link>
          {isAuthenticated ? (
            <>
              <Link to="/create" className="nav-link" onClick={closeMenu}>Create Article</Link>
              <span className="user-info">Welcome, {user?.name}</span>
              <button onClick={handleLogout} className="btn btn-secondary nav-btn">
                Logout
              </button>
            </>
          ) : (
            <Link to="/login" className="btn btn-primary nav-btn" onClick={closeMenu}>Login</Link>
          )}
        </nav>
      </div>
    </header>
  );
};

export default Header;



