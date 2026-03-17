# Student Grade Management System

A comprehensive PHP-based system for managing student grades and academic records.

## Overview

The Student Grade Management System is a web application designed to streamline the process of recording, tracking, and analyzing student grades. Built with PHP, this system provides educators and administrators with tools to efficiently manage academic data.

## Features

- **Student Management** - Add, update, and manage student records
- **Grade Tracking** - Record and organize student grades across multiple courses
- **Authentication** - Secure login system for authorized access
- **Statistics & Analytics** - View grade statistics and performance analytics
- **Database Integration** - Persistent data storage using SQL database
- **Middleware Support** - Request processing and validation middleware
- **User-Friendly Interface** - Clean and intuitive UI with CSS assets

## Project Structure

```
├── index.php              # Application entry point
├── auth/                  # Authentication system
├── config/                # Configuration files
├── grades/                # Grade management functionality
├── students/              # Student management functionality
├── stats/                 # Statistics and analytics
├── includes/              # Reusable components and utilities
├── middleware/            # Request middleware
├── sql/                   # Database schemas and migrations
└── assets/                # CSS, JavaScript, and static files
```

## Technology Stack

- **Language**: PHP
- **Database**: MySQL/MariaDB
- **Frontend**: HTML, CSS, JavaScript
- **Architecture**: MVC-inspired modular structure

## Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB database server
- Web server (Apache, Nginx, etc.)
- Modern web browser

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/YeuThuyr/Student-Grade-Management-System.git
   cd Student-Grade-Management-System
   ```

2. Set up database:
   - Import the SQL schema from the `sql/` directory into your MySQL database
   - Update database credentials in `config/` files

3. Configure the application:
   - Adjust configuration settings in the `config/` directory as needed
   - Set appropriate file permissions for the application

4. Start your web server and access the application in your browser

## Getting Started for Collaborators

### Step 1: Clone the Repository

```bash
git clone https://github.com/YeuThuyr/Student-Grade-Management-System.git
cd Student-Grade-Management-System
```

### Step 2: Set Up Your Database Configuration

A sample database configuration file is provided as a template.

1. Navigate to the `config/` directory:
   ```bash
   cd config
   ```

2. Create your own `database.php` file by copying the example:
   ```bash
   cp database.example.php database.php
   ```

3. Open `database.php` and update the credentials with your local database settings:
   ```php
   <?php
   $host = "localhost";              // Your database host
   $user = "your_user";              // Your MySQL username
   $password = "your_password";      // Your MySQL password
   $database = "grade_management";   // Your database name
   
   // Connect to MySQL server first without selecting a database
   $conn = new mysqli($host, $user, $password);
   
   if ($conn->connect_error) {
       die("Database connection failed: " . $conn->connect_error);
   }
   
   // Check if database exists and select it
   if (!$conn->select_db($database)) {
       die("Error: Database '$database' does not exist. Please create it and run the SQL in 'sql/schema.sql'.");
   }
   ?>
   ```

### Step 3: Create and Initialize Your Database

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE grade_management;
   ```

2. Import the database schema:
   ```bash
   mysql -u your_user -p grade_management < sql/schema.sql
   ```

### Step 4: Run the Application

1. Navigate back to the project root directory
2. Start your local web server (or use PHP's built-in server):
   ```bash
   php -S localhost:8000
   ```

3. Open your browser and visit: `http://localhost:8000`

### Important Notes for Collaborators

- **Do NOT commit** your `config/database.php` file. It contains sensitive credentials. The `.gitignore` file should already exclude it.
- Always use `database.example.php` as your template when setting up locally
- Each collaborator should create their own `database.php` with their local credentials
- If you add new database tables or columns, update the SQL schema file in the `sql/` directory
- Make sure your MySQL user has the necessary permissions to create databases and tables

### Troubleshooting Database Connection Issues

| Issue | Solution |
|-------|----------|
| "Connection refused" | Check if MySQL server is running and accessible on the specified host |
| "Access denied for user" | Verify your username and password are correct in `database.php` |
| "Database does not exist" | Create the database and run the schema SQL file as shown in Step 3 |
| "Permission denied" | Ensure your MySQL user has CREATE, SELECT, INSERT, UPDATE, DELETE permissions |

## Usage

1. **Login** - Authenticate using the provided credentials
2. **Manage Students** - Navigate to the students section to add or update student records
3. **Record Grades** - Input and manage grades for each student
4. **View Statistics** - Access the stats section to analyze academic performance
5. **Generate Reports** - Export grade data and performance reports

## Development Workflow

1. Create a new branch for your feature: `git checkout -b feature/your-feature-name`
2. Make your changes and commit with clear messages
3. Push to your branch and create a pull request
4. Ensure all changes are tested locally with your own database configuration

## Project Status

This is an active project. Last updated: March 2026

## License

This project is currently unlicensed. Please contact the repository owner for licensing inquiries.

## Contributing

Contributions are welcome! Feel free to fork the repository and submit pull requests with improvements or bug fixes. Please ensure:
- Your code follows the existing project structure
- Database credentials are never committed
- You test changes with a local database before submitting a PR

## Support

For issues, questions, or suggestions, please open an issue on the GitHub repository.

## Author

[YeuThuyr](https://github.com/YeuThuyr)

