<?php
$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

header('Content-Type: application/json');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
$allowed_origins = ['http://localhost:3000', 'http://127.0.0.1:3000'];
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dbFile = __DIR__ . '/news.db';
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    description TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    image_url TEXT,
    category_id INTEGER,
    user_id INTEGER NOT NULL,
    published_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

if ($db->query("SELECT COUNT(*) FROM users")->fetchColumn() == 0) {
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (name, email, password) VALUES ('Admin User', 'admin@newsdashboard.com', '$password')");
    
    $categories = [
        ['Technology', 'technology', 'Latest tech news'],
        ['Business', 'business', 'Business and finance'],
        ['Science', 'science', 'Scientific discoveries'],
        ['Health', 'health', 'Health and wellness'],
        ['Sports', 'sports', 'Sports news']
    ];
    
    $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    
    $articles = [
        ['The Future of AI', 'the-future-of-ai', 'Artificial Intelligence is transforming industries...', 'Exploring AI developments', 1],
        ['Market Update', 'market-update', 'Stock markets reach new highs...', 'Financial news update', 2],
        ['Quantum Breakthrough', 'quantum-breakthrough', 'Scientists achieve quantum computing milestone...', 'Science news', 3]
    ];
    
    $stmt = $db->prepare("INSERT INTO articles (title, slug, content, excerpt, category_id, user_id, published_at) VALUES (?, ?, ?, ?, ?, 1, datetime('now'))");
    foreach ($articles as $article) {
        $stmt->execute($article);
    }
}

function getCurrentUser($db) {
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    }
    
    if (!$token) return null;
    
    $stmt = $db->prepare("SELECT u.* FROM users u INNER JOIN tokens t ON u.id = t.user_id WHERE t.token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function requireAuth($db) {
    $user = getCurrentUser($db);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized - Please login']);
        exit;
    }
    return $user;
}

function validateArticle($data, $isUpdate = false) {
    $errors = [];
    
    if (!$isUpdate || isset($data['title'])) {
        if (empty($data['title']) || strlen($data['title']) < 3) {
            $errors[] = 'Title must be at least 3 characters';
        }
        if (strlen($data['title']) > 255) {
            $errors[] = 'Title must be less than 255 characters';
        }
    }
    
    if (!$isUpdate || isset($data['content'])) {
        if (empty($data['content']) || strlen($data['content']) < 10) {
            $errors[] = 'Content must be at least 10 characters';
        }
    }
    
    if (isset($data['category_id']) && !is_numeric($data['category_id'])) {
        $errors[] = 'Category ID must be a number';
    }
    
    return $errors;
}

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$path = preg_replace('#^api#', '', $path);
$path = trim($path, '/');
$parts = !empty($path) ? explode('/', $path) : [];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if (empty($parts) || $parts[0] === '') {
        echo json_encode(['message' => 'News Dashboard API', 'version' => '1.0']);
    }
    
    elseif ($parts[0] === 'categories') {
        if ($method === 'GET') {
            if (isset($parts[1])) {
                $id = (int)$parts[1];
                $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$category) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Category not found']);
                } else {
                    $stmt = $db->prepare("SELECT a.*, c.name as category_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.category_id = ? AND a.published_at IS NOT NULL ORDER BY a.published_at DESC");
                    $stmt->execute([$id]);
                    $category['articles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($category);
                }
            } else {
                $stmt = $db->query("SELECT c.*, (SELECT COUNT(*) FROM articles WHERE category_id = c.id AND published_at IS NOT NULL) as article_count FROM categories c ORDER BY c.name");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    elseif ($parts[0] === 'articles') {
        if ($method === 'GET') {
            if (isset($parts[1])) {
                $id = (int)$parts[1];
                $stmt = $db->prepare("SELECT a.*, u.name as user_name, c.name as category_name, c.slug as category_slug FROM articles a LEFT JOIN users u ON a.user_id = u.id LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = ?");
                $stmt->execute([$id]);
                $article = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$article) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Article not found']);
                } else {
                    echo json_encode($article);
                }
            } else {
                $query = "SELECT a.*, u.name as user_name, c.name as category_name, c.slug as category_slug FROM articles a LEFT JOIN users u ON a.user_id = u.id LEFT JOIN categories c ON a.category_id = c.id WHERE a.published_at IS NOT NULL";
                $params = [];
                
                if (isset($_GET['search'])) {
                    $query .= " AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
                    $search = '%' . $_GET['search'] . '%';
                    $params = [$search, $search, $search];
                }
                
                if (isset($_GET['category'])) {
                    $query .= " AND c.slug = ?";
                    $params[] = $_GET['category'];
                }
                
                $query .= " ORDER BY a.published_at DESC";
                
                if (isset($_GET['per_page'])) {
                    $perPage = (int)$_GET['per_page'];
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $perPage;
                    $query .= " LIMIT $perPage OFFSET $offset";
                }
                
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        }
        
        elseif ($method === 'POST') {
            $user = requireAuth($db);
            $data = json_decode(file_get_contents('php://input'), true);
            $errors = validateArticle($data);
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['error' => 'Validation failed', 'errors' => $errors]);
                exit;
            }
            
            $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
            $stmt->execute([$data['category_id']]);
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Category not found']);
                exit;
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $data['title']));
            $originalSlug = $slug;
            $counter = 1;
            while ($db->query("SELECT id FROM articles WHERE slug = '$slug'")->fetch()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $stmt = $db->prepare("INSERT INTO articles (title, slug, content, excerpt, image_url, category_id, user_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))");
            $stmt->execute([
                $data['title'],
                $slug,
                $data['content'],
                $data['excerpt'] ?? '',
                $data['image_url'] ?? '',
                $data['category_id'],
                $user['id']
            ]);
            
            $articleId = $db->lastInsertId();
            $stmt = $db->prepare("SELECT a.*, u.name as user_name, c.name as category_name FROM articles a LEFT JOIN users u ON a.user_id = u.id LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = ?");
            $stmt->execute([$articleId]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
            
            http_response_code(201);
            echo json_encode(['message' => 'Article created successfully', 'article' => $article]);
        }
        
        elseif ($method === 'PUT') {
            $user = requireAuth($db);
            
            if (!isset($parts[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Article ID required']);
                exit;
            }
            
            $id = (int)$parts[1];
            $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$article) {
                http_response_code(404);
                echo json_encode(['error' => 'Article not found']);
                exit;
            }
            
            if ($article['user_id'] != $user['id']) {
                http_response_code(403);
                echo json_encode(['error' => 'You do not have permission to edit this article']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $errors = validateArticle($data, true);
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['error' => 'Validation failed', 'errors' => $errors]);
                exit;
            }
            
            $updates = [];
            $params = [];
            
            if (isset($data['title'])) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $data['title']));
                $originalSlug = $slug;
                $counter = 1;
                while ($db->query("SELECT id FROM articles WHERE slug = '$slug' AND id != $id")->fetch()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $updates[] = "title = ?";
                $updates[] = "slug = ?";
                $params[] = $data['title'];
                $params[] = $slug;
            }
            if (isset($data['content'])) {
                $updates[] = "content = ?";
                $params[] = $data['content'];
            }
            if (isset($data['excerpt'])) {
                $updates[] = "excerpt = ?";
                $params[] = $data['excerpt'];
            }
            if (isset($data['image_url'])) {
                $updates[] = "image_url = ?";
                $params[] = $data['image_url'];
            }
            if (isset($data['category_id'])) {
                $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
                $stmt->execute([$data['category_id']]);
                if (!$stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Category not found']);
                    exit;
                }
                $updates[] = "category_id = ?";
                $params[] = $data['category_id'];
            }
            
            if (!empty($updates)) {
                $params[] = $id;
                $query = "UPDATE articles SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute($params);
            }
            
            $stmt = $db->prepare("SELECT a.*, u.name as user_name, c.name as category_name FROM articles a LEFT JOIN users u ON a.user_id = u.id LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['message' => 'Article updated successfully', 'article' => $article]);
        }
        
        elseif ($method === 'DELETE') {
            $user = requireAuth($db);
            
            if (!isset($parts[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Article ID required']);
                exit;
            }
            
            $id = (int)$parts[1];
            $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$article) {
                http_response_code(404);
                echo json_encode(['error' => 'Article not found']);
                exit;
            }
            
            if ($article['user_id'] != $user['id']) {
                http_response_code(403);
                echo json_encode(['error' => 'You do not have permission to delete this article']);
                exit;
            }
            
            $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['message' => 'Article deleted successfully']);
        }
        
        else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    elseif ($parts[0] === 'login' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            exit;
        }
        
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
            exit;
        }
        
        $token = bin2hex(random_bytes(32));
        $stmt = $db->prepare("INSERT INTO tokens (user_id, token) VALUES (?, ?)");
        $stmt->execute([$user['id'], $token]);
        
        unset($user['password']);
        echo json_encode([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }
    
    elseif ($parts[0] === 'register' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name, email, and password are required']);
            exit;
        }
        
        if (strlen($data['password']) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 8 characters']);
            exit;
        }
        
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Email already registered']);
            exit;
        }
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        
        $userId = $db->lastInsertId();
        $token = bin2hex(random_bytes(32));
        $stmt = $db->prepare("INSERT INTO tokens (user_id, token) VALUES (?, ?)");
        $stmt->execute([$userId, $token]);
        
        $user = $db->query("SELECT id, name, email FROM users WHERE id = $userId")->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(201);
        echo json_encode([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ]);
    }
    
    elseif ($parts[0] === 'user' && $method === 'GET') {
        $user = requireAuth($db);
        unset($user['password']);
        echo json_encode($user);
    }
    
    elseif ($parts[0] === 'logout' && $method === 'POST') {
        $user = requireAuth($db);
        $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $stmt = $db->prepare("DELETE FROM tokens WHERE token = ?");
        $stmt->execute([$token]);
        echo json_encode(['message' => 'Logged out successfully']);
    }
    
    elseif ($parts[0] === 'auto-fetch-news' && $method === 'GET') {
        $apiKey = defined('NEWS_API_KEY') && NEWS_API_KEY !== 'YOUR_API_KEY_HERE' 
            ? NEWS_API_KEY 
            : ($_GET['api_key'] ?? '');
        
        if (empty($apiKey)) {
            http_response_code(400);
            echo json_encode(['error' => 'API key not configured', 'message' => 'Please configure NEWS_API_KEY in config.php']);
            exit;
        }
        
        $category = defined('AUTO_FETCH_CATEGORY') ? AUTO_FETCH_CATEGORY : ($_GET['category'] ?? 'technology');
        $count = defined('AUTO_FETCH_COUNT') ? AUTO_FETCH_COUNT : (isset($_GET['count']) ? min((int)$_GET['count'], 20) : 10);
        
        $stmt = $db->prepare("SELECT * FROM users WHERE email = 'admin@newsdashboard.com' LIMIT 1");
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $password = password_hash('password123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES ('System User', 'admin@newsdashboard.com', ?)");
            $stmt->execute([$password]);
            $userId = $db->lastInsertId();
            $user = ['id' => $userId, 'name' => 'System User', 'email' => 'admin@newsdashboard.com'];
        }
        
        $categoryMap = [
            'technology' => 'technology',
            'business' => 'business',
            'science' => 'science',
            'health' => 'health',
            'sports' => 'sports'
        ];
        
        $newsCategory = $categoryMap[$category] ?? 'technology';
        $url = "https://newsapi.org/v2/top-headlines?category={$newsCategory}&pageSize={$count}&language=en";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $apiKey,
            'User-Agent: News Dashboard App'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            http_response_code(500);
            echo json_encode(['error' => 'CURL error', 'details' => $curlError]);
            exit;
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to fetch news from NewsAPI',
                'http_code' => $httpCode,
                'details' => $errorData['message'] ?? $response
            ]);
            exit;
        }
        
        $newsData = json_decode($response, true);
        
        if (!isset($newsData['articles']) || empty($newsData['articles'])) {
            http_response_code(404);
            echo json_encode(['error' => 'No articles found']);
            exit;
        }
        
        // Get our category ID
        $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$category]);
        $categoryRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categoryRow) {
            http_response_code(400);
            echo json_encode(['error' => 'Category not found']);
            exit;
        }
        
        $categoryId = $categoryRow['id'];
        $imported = 0;
        $skipped = 0;
        
        foreach ($newsData['articles'] as $article) {
            if (empty($article['title']) || (empty($article['content']) && empty($article['description']))) {
                $skipped++;
                continue;
            }
            
            $stmt = $db->prepare("SELECT id FROM articles WHERE title = ?");
            $stmt->execute([$article['title']]);
            if ($stmt->fetch()) {
                $skipped++;
                continue;
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $article['title']));
            $originalSlug = $slug;
            $counter = 1;
            while ($db->query("SELECT id FROM articles WHERE slug = '$slug'")->fetch()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $content = $article['content'] ?? $article['description'] ?? '';
            $excerpt = $article['description'] ?? substr($content, 0, 150) . '...';
            $imageUrl = $article['urlToImage'] ?? null;
            
            $stmt = $db->prepare("INSERT INTO articles (title, slug, content, excerpt, image_url, category_id, user_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))");
            $stmt->execute([
                $article['title'],
                $slug,
                $content,
                $excerpt,
                $imageUrl,
                $categoryId,
                $user['id']
            ]);
            
            $imported++;
        }
        
        echo json_encode([
            'message' => 'News articles imported successfully',
            'imported' => $imported,
            'skipped' => $skipped,
            'total' => count($newsData['articles'])
        ]);
    }
    
    elseif ($parts[0] === 'fetch-news' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $category = $data['category'] ?? 'technology';
        $count = isset($data['count']) ? min((int)$data['count'], 20) : 10;
        $apiKey = $data['api_key'] ?? '';
        
        $user = getCurrentUser($db);
        
        if (!$user && empty($apiKey)) {
            http_response_code(401);
            echo json_encode(['error' => 'API key required', 'message' => 'Please provide a NewsAPI key or log in first']);
            exit;
        }
        
        if (!$user) {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = 'admin@newsdashboard.com' LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $password = password_hash('password123', PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES ('System User', 'admin@newsdashboard.com', ?)");
                $stmt->execute([$password]);
                $userId = $db->lastInsertId();
                $user = ['id' => $userId, 'name' => 'System User', 'email' => 'admin@newsdashboard.com'];
            }
        }
        
        $categoryMap = [
            'technology' => 'technology',
            'business' => 'business',
            'science' => 'science',
            'health' => 'health',
            'sports' => 'sports'
        ];
        
        $newsCategory = $categoryMap[$category] ?? 'technology';
        
        if (empty($apiKey)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'API key required',
                'message' => 'To fetch real news articles, you need a NewsAPI key. Get one free at https://newsapi.org/register',
                'alternative' => 'You can also use the /api/articles POST endpoint to create articles manually'
            ]);
            exit;
        }
        
        $url = "https://newsapi.org/v2/top-headlines?category={$newsCategory}&pageSize={$count}&language=en";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $apiKey,
            'User-Agent: News Dashboard App'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            http_response_code(500);
            echo json_encode(['error' => 'CURL error', 'details' => $curlError]);
            exit;
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to fetch news from NewsAPI',
                'http_code' => $httpCode,
                'details' => $errorData['message'] ?? $response
            ]);
            exit;
        }
        
        $newsData = json_decode($response, true);
        
        if (!isset($newsData['articles']) || empty($newsData['articles'])) {
            http_response_code(404);
            echo json_encode(['error' => 'No articles found']);
            exit;
        }
        
        // Get our category ID
        $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$category]);
        $categoryRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categoryRow) {
            http_response_code(400);
            echo json_encode(['error' => 'Category not found']);
            exit;
        }
        
        $categoryId = $categoryRow['id'];
        $imported = 0;
        $skipped = 0;
        
        foreach ($newsData['articles'] as $article) {
            if (empty($article['title']) || empty($article['content']) && empty($article['description'])) {
                $skipped++;
                continue;
            }
            
            $stmt = $db->prepare("SELECT id FROM articles WHERE title = ?");
            $stmt->execute([$article['title']]);
            if ($stmt->fetch()) {
                $skipped++;
                continue;
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $article['title']));
            $originalSlug = $slug;
            $counter = 1;
            while ($db->query("SELECT id FROM articles WHERE slug = '$slug'")->fetch()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $content = $article['content'] ?? $article['description'] ?? '';
            $excerpt = $article['description'] ?? substr($content, 0, 150) . '...';
            $imageUrl = $article['urlToImage'] ?? null;
            
            $stmt = $db->prepare("INSERT INTO articles (title, slug, content, excerpt, image_url, category_id, user_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))");
            $stmt->execute([
                $article['title'],
                $slug,
                $content,
                $excerpt,
                $imageUrl,
                $categoryId,
                $user['id']
            ]);
            
            $imported++;
        }
        
        echo json_encode([
            'message' => 'News articles imported successfully',
            'imported' => $imported,
            'skipped' => $skipped,
            'total' => count($newsData['articles'])
        ]);
    }
    
    else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => 'An error occurred while processing your request']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}

