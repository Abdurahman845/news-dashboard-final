# News Dashboard Application

A full-stack news dashboard application built with Laravel (Backend) and React (Frontend).

## Project Status

✅ **Project Complete** - All major features implemented and ready for setup!

See [PROJECT_SUMMARY.md](./PROJECT_SUMMARY.md) for detailed completion status and [QUICK_START.md](./QUICK_START.md) for setup instructions.

## Project Structure

```
News dashboard application/
├── client/          # React frontend application
├── server/          # Laravel backend API
├── .github/         # GitHub templates and milestones
├── README.md        # This file
├── PROJECT_SUMMARY.md  # Complete project status
├── QUICK_START.md     # Quick setup guide
└── TEST_CASES.md      # Test documentation
```

## Features

- 📰 Article Management (CRUD operations)
- 🔐 User Authentication with Laravel Sanctum
- 📂 Category Management
- 🔍 Search and Filter functionality
- 📄 Pagination
- 📱 Responsive Design
- 🎨 Modern UI/UX

## Tech Stack

### Backend
- **Framework**: Laravel
- **Database**: PostgreSQL
- **Authentication**: Laravel Sanctum
- **API**: RESTful API

### Frontend
- **Framework**: React
- **Routing**: React Router DOM
- **HTTP Client**: Axios
- **State Management**: Context API

## Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 18.x
- npm or yarn
- PostgreSQL >= 13
- Git

## Getting Started

### Backend Setup

1. Navigate to server directory:
```bash
cd server
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure database in `.env` file

6. Run migrations and seed:
```bash
php artisan migrate --seed
```

7. Start the server:
```bash
php artisan serve
```

Backend will run on `http://localhost:8000`

### Frontend Setup

1. Navigate to client directory:
```bash
cd client
```

2. Install dependencies:
```bash
npm install
```

3. Start development server:
```bash
npm start
```

Frontend will run on `http://localhost:3000`

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user
- `GET /api/user` - Get authenticated user

### Articles
- `GET /api/articles` - Get all articles (with pagination, search, filter)
- `GET /api/articles/{id}` - Get single article
- `POST /api/articles` - Create article (authenticated)
- `PUT /api/articles/{id}` - Update article (authenticated)
- `DELETE /api/articles/{id}` - Delete article (authenticated)

### Categories
- `GET /api/categories` - Get all categories
- `GET /api/categories/{id}` - Get single category with articles

## Development

### Testing
- Backend tests: `php artisan test`
- Frontend tests: `npm test`

## Contributing

1. Create a feature branch
2. Make your changes
3. Submit a pull request

## License

This project is open source and available under the MIT License.

