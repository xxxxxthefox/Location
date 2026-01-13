<?php
class Database {
    private $db;
    
    public function __construct() {
        $this->db = new SQLite3(__DIR__ . '/../minehub.db');
        $this->initTables();
    }
    
    private function initTables() {
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                email TEXT,
                is_premium INTEGER DEFAULT 0,
                premium_expires TEXT,
                is_admin INTEGER DEFAULT 0,
                max_servers INTEGER DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ');
        
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS servers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                version TEXT NOT NULL,
                type TEXT NOT NULL,
                ram INTEGER DEFAULT 1024,
                port INTEGER UNIQUE,
                status TEXT DEFAULT "stopped",
                auto_restart INTEGER DEFAULT 1,
                auto_shutdown INTEGER DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ');
        
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS server_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                server_id INTEGER NOT NULL,
                log_text TEXT,
                timestamp TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (server_id) REFERENCES servers(id)
            )
        ');
        
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS players (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                server_id INTEGER NOT NULL,
                username TEXT NOT NULL,
                is_banned INTEGER DEFAULT 0,
                is_whitelisted INTEGER DEFAULT 0,
                is_op INTEGER DEFAULT 0,
                last_seen TEXT,
                FOREIGN KEY (server_id) REFERENCES servers(id)
            )
        ');
        
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                payment_method TEXT,
                transaction_id TEXT,
                status TEXT DEFAULT "pending",
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ');
        
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS marketplace_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                version TEXT,
                description TEXT,
                price REAL DEFAULT 0,
                file_path TEXT,
                downloads INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ');
        
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS proxy_nodes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                host TEXT NOT NULL,
                port INTEGER NOT NULL,
                status TEXT DEFAULT "active",
                load_score INTEGER DEFAULT 0
            )
        ');
        
        $admin = $this->db->querySingle("SELECT id FROM users WHERE username='admin'");
        if (!$admin) {
            $stmt = $this->db->prepare('INSERT INTO users (username, password, is_admin, max_servers) VALUES (?, ?, 1, 999)');
            $stmt->bindValue(1, 'admin');
            $stmt->bindValue(2, password_hash('admin123', PASSWORD_BCRYPT));
            $stmt->execute();
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
}
