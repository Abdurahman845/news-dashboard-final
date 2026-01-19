# Frontend Setup Instructions

## Prerequisites
- Node.js >= 18.x
- npm or yarn

## Installation Steps

1. **Install Dependencies**
   ```bash
   cd client
   npm install
   ```

2. **Configure API URL**
   
   Edit `src/config/api.js` if your backend is running on a different port:
   ```javascript
   baseURL: 'http://localhost:8000/api'
   ```

3. **Start Development Server**
   ```bash
   npm start
   ```

The application will open at `http://localhost:3000`

## Available Scripts

- `npm start` - Start development server
- `npm build` - Build for production
- `npm test` - Run tests

## Features

- **Home Page**: Browse articles with search, filter, and pagination
- **Article Page**: View full article details
- **Login/Register**: User authentication
- **Create Article**: Create new articles (authenticated users)
- **Edit Article**: Edit your own articles (authenticated users)

## Environment Variables

For production, you may want to use environment variables for the API URL:

Create `.env` file:
```
REACT_APP_API_URL=http://localhost:8000/api
```

Then update `src/config/api.js`:
```javascript
baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api'
```



