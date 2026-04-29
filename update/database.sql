CREATE DATABASE IF NOT EXISTS db_update;
USE db_update;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default user (password: password)
-- Password uses PASSWORD_DEFAULT hash (BCRYPT)
-- You can generate new hashes using PHP's password_hash() function
INSERT INTO users (username, password, nama_lengkap) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');
