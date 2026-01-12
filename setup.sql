-- Create the database
CREATE DATABASE IF NOT EXISTS tunetolight;
USE tunetolight;

-- Table for storing Presets
-- 'data' column stores the JSON configuration
CREATE TABLE IF NOT EXISTS presets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'AOM', 'LIS', 'DIFFCAM', etc.
    data JSON NOT NULL,        
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for Music
CREATE TABLE IF NOT EXISTS music (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Music Data (You can run this after manually moving files if needed)
-- INSERT INTO music (filename, filepath) VALUES ('DemoSong.mp3', 'music/DemoSong.mp3');
