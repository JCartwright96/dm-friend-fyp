DROP DATABASE IF EXISTS DM_Friend_database;
CREATE DATABASE DM_Friend_Database;
USE DM_Friend_Database;

DROP TABLE IF EXISTS `appearances`;
DROP TABLE IF EXISTS `encounters`;
DROP TABLE IF EXISTS `actions`;
DROP TABLE IF EXISTS `enemies`;
DROP TABLE IF EXISTS `users`;

-- Table structure for `user` table --
CREATE TABLE `users` (
    `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `username` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    UNIQUE(`username`),
    UNIQUE (`email`),
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `enemy` table --
CREATE TABLE `enemies` (
    `enemy_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `enemy_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `enemy_ac` int(2) COLLATE utf8mb4_unicode_ci NOT NULL,
    `enemy_hp` int(5) COLLATE utf8mb4_unicode_ci NOT NULL,
    `enemy_speed` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `strength` int(2) COLLATE utf8mb4_unicode_ci,
    `dexterity` int(2) COLLATE utf8mb4_unicode_ci,
    `constitution` int(2) COLLATE utf8mb4_unicode_ci,
    `intelligence` int(2) COLLATE utf8mb4_unicode_ci,
    `wisdom` int(2) COLLATE utf8mb4_unicode_ci ,
    `charisma` int(2) COLLATE utf8mb4_unicode_ci,
    `username` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`enemy_id`),
    FOREIGN KEY (`username`) REFERENCES users(`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `actions table` --
CREATE TABLE `actions` (
    `action_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `action_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `hit_chance` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `reach` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `area` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `damage` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `modifier` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `enemy_id` int(10) unsigned NOT NULL,
    `username` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`action_id`),
    FOREIGN KEY (`enemy_id`) REFERENCES enemies(`enemy_id`),
    FOREIGN KEY (`username`) REFERENCES users(`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `encounters` table --
CREATE TABLE `encounters` (
    `encounter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `encounter_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `location` varchar(256) COLLATE utf8mb4_unicode_ci,
    `description` varchar(256) COLLATE utf8mb4_unicode_ci,
    `notes` TEXT COLLATE utf8mb4_unicode_ci,
    `username` varchar(256) COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`encounter_id`),
    FOREIGN KEY (`username`) REFERENCES users(`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Structure for `appearances` table --
CREATE TABLE `appearances` (
    `appearance_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `encounter_id` int(10) unsigned NOT NULL,
    `enemy_id` int(10) unsigned NOT NULL,
    `enemy_quantity` tinyint(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `username` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`appearance_id`),
    FOREIGN KEY (`username`) REFERENCES users(`username`),
    FOREIGN KEY (`enemy_id`) REFERENCES enemies(`enemy_id`),
    FOREIGN KEY (`encounter_id`) REFERENCES encounters(`encounter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

FLUSH PRIVILEGES;

-- Insert test data into databases --
INSERT INTO users(username, email, password) values("test", "test@gmail.com", "$2y$10$0miKcd9K0OgxohHAi6V/8ufTqj236lSSf7zQ2HSGPSJxXdFyM/I4C");

