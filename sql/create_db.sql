CREATE DATABASE IF NOT EXISTS 88327;

USE 88327;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO tasks (title, description, completed) VALUES
    ('Complete GitHub Actions lab', 'Set up CI/CD pipeline with automatic deployment', 0),
    ('Configure Apache VirtualHost', 'Update DocumentRoot to point to correct directory', 0),
    ('Test database connection', 'Verify PHP can connect to MySQL database', 1);