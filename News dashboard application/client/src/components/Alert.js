import React from 'react';

const Alert = ({ type = 'info', children, className = '' }) => {
  if (!children) return null;

  return (
    <div className={`alert alert-${type} ${className}`}>
      {children}
    </div>
  );
};

export default Alert;
