SET NAMES utf8mb4;

ALTER TABLE classes
    ADD COLUMN major VARCHAR(100) DEFAULT NULL AFTER class_name,
    ADD COLUMN teacher VARCHAR(100) DEFAULT NULL AFTER major,
    ADD COLUMN status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active' AFTER teacher;

