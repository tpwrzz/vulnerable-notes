CREATE DATABASE IF NOT EXISTS vulnerable_notes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vulnerable_notes;

DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_notes_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

-- Passwords hashed with password_hash()
-- admin123 -> $2y$10$...
-- student123 -> $2y$10$...
INSERT INTO users (name, email, password, role) VALUES
('Администратор', 'admin@example.local', '$2a$10$y9.sC/KQsD4VGQIgcdMBI.nmO0.S5hkpJ2SRKIy6bFMG3kow8i9D2', 'admin'), 
('Анна', 'anna@example.local', '$2a$10$UBymXAmGkD59rmlq2la/tuUWGidU6umJUrLfqUbX7C2zMYaNoaDhK', 'user'),
('Иван', 'ivan@example.local', '$2y$10$N8Vz5T9J2K4R7S1Q6P3L8M0U.hX7V3N5F2W.8Y9C1A4D6E8H0J2K4', 'user'); 

INSERT INTO notes (user_id, title, content) VALUES
(1, 'Системная заметка', 'Проверить права доступа к административной странице.'),
(2, 'Личная заметка Анны', 'Эту запись не должен изменять другой пользователь.'),
(3, 'Личная заметка Ивана', 'Проверить защиту от IDOR и XSS.');