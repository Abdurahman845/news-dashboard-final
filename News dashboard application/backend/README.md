# Simple News Dashboard Backend

Single-file PHP backend with SQLite database.

## Quick Start

**Option 1: Double-click `start.bat`**

**Option 2: Command line**
```cmd
cd "C:\Users\good\Desktop\News dashboard application\backend"
"C:\Users\good\Downloads\php-8.3.4\php.exe" -S localhost:8000 -t . api.php
```

## Features

✅ Store news articles in database  
✅ CRUD operations (Create, Read, Update, Delete)  
✅ User login and authentication  
✅ Permission checks (only owners can edit/delete)  
✅ Input validation  
✅ Clean JSON responses  
✅ Error handling  

## API Endpoints

### Public
- `GET /api/categories` - List all categories
- `GET /api/categories/{id}` - Get category with articles
- `GET /api/articles` - List articles (supports ?search=, ?category=, ?page=, ?per_page=)
- `GET /api/articles/{id}` - Get single article
- `POST /api/login` - Login user
- `POST /api/register` - Register new user

### Protected (requires Bearer token)
- `GET /api/user` - Get current user
- `POST /api/logout` - Logout
- `POST /api/articles` - Create article
- `PUT /api/articles/{id}` - Update article (owner only)
- `DELETE /api/articles/{id}` - Delete article (owner only)

## Default Login

- Email: `admin@newsdashboard.com`
- Password: `password123`

## Database

Auto-creates `news.db` SQLite file on first run with sample data.

