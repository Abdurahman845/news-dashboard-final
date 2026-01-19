import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import Header from './components/Header';
import Footer from './components/Footer';
import Home from './pages/Home';
import ArticlePage from './pages/ArticlePage';
import Login from './pages/Login';
import CreateArticle from './pages/CreateArticle';
import EditArticle from './pages/EditArticle';
import './App.css';

function App() {
  return (
    <AuthProvider>
      <Router>
        <div className="App">
          <Header />
          <main>
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/article/:id" element={<ArticlePage />} />
              <Route path="/login" element={<Login />} />
              <Route path="/create" element={<CreateArticle />} />
              <Route path="/edit/:id" element={<EditArticle />} />
            </Routes>
          </main>
          <Footer />
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;



