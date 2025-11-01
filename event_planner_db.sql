CREATE DATABASE event_planner_db;

USE event_planner_db;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(255) UNIQUE,
    title VARCHAR(255),
    category VARCHAR(100),
    start_time DATETIME,
    end_time DATETIME,
    location VARCHAR(255),
    latitude DOUBLE,
    longitude DOUBLE,
    rank INT,
    link TEXT
);
