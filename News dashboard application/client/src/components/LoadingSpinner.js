import React from 'react';

const LoadingSpinner = ({ text = 'Loading...' }) => (
  <div className="loading">
    <div className="spinner" />
    <p>{text}</p>
  </div>
);

export default LoadingSpinner;
