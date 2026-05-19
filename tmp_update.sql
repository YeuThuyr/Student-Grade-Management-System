USE grade_management;
UPDATE users SET password = '$2y$10$KSNc.Kn2V37gWSMEROiicuHFt4R9UxJOa7vD/U7icAet/VLBxaVxO' WHERE username = 'admin';
SELECT id, username, role, is_active FROM users WHERE username = 'admin';
