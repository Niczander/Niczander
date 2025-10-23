CREATE DATABASE IF NOT EXISTS university_database;
USE university_database;

-- Step 1: Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 2: Department table
CREATE TABLE IF NOT EXISTS department_data (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(255) NOT NULL
);

-- Step 3: Program table (each program belongs to a department)
CREATE TABLE IF NOT EXISTS program_data (
    program_id INT PRIMARY KEY AUTO_INCREMENT,
    program_name VARCHAR(255) NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES department_data(department_id)
);

-- Step 4: Students table (each student is enrolled in a program)
CREATE TABLE IF NOT EXISTS students_data (
    students_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(255) NOT NULL UNIQUE,
    program_id INT,
    FOREIGN KEY (program_id) REFERENCES program_data(program_id)
);

-- Add unique constraint on combination of name, email, and phone
ALTER TABLE students_data 
ADD CONSTRAINT unique_student 
UNIQUE (name, email, phone);

-- Step 5: Staff table (each staff member can belong to a department)
CREATE TABLE IF NOT EXISTS staff (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    `position` VARCHAR(50) NOT NULL,
    hire_date DATE NOT NULL,
    salary DECIMAL(10, 2),
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES department_data(department_id)
);

-- Step 6: Course Units table (each course unit belongs to a program)
CREATE TABLE IF NOT EXISTS course_units (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(255) NOT NULL,
    course_description TEXT,
    credits INT NOT NULL DEFAULT 3,
    semester VARCHAR(20) NOT NULL,
    year_level INT NOT NULL DEFAULT 1,
    program_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES program_data(program_id) ON DELETE CASCADE,
    INDEX idx_program_id (program_id),
    INDEX idx_course_code (course_code)
);

-- Insert sample data into the staff table
-- Note: Ensure that departments with these IDs exist in the department_data table.
INSERT INTO staff (first_name, last_name, email, phone_number, `position`, hire_date, salary, department_id) VALUES
('John', 'Smith', 'john.smith@university.edu', '123-456-7890', 'Professor', '2018-08-15', 75000.00, 1),
('Jane', 'Doe', 'jane.doe@university.edu', '098-765-4321', 'Administrator', '2020-01-20', 55000.00, 2),
('Peter', 'Jones', 'peter.jones@university.edu', '111-222-3333', 'Librarian', '2019-05-30', 48000.00, 2),
('Mary', 'Williams', 'mary.williams@university.edu', '444-555-6666', 'IT Support', '2021-11-01', 62000.00, 1),
('David', 'Brown', 'david.brown@university.edu', '777-888-9999', 'Professor', '2017-09-01', 82000.00, 1);

