CREATE DATABASE IF NOT EXISTS student_db;
USE student_db;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    course VARCHAR(50) NOT NULL,
    year_semester VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial demonstration records
INSERT INTO students (student_id, full_name, email, phone, course, year_semester) VALUES
('TE-COMP-01', 'Rahul Sharma', 'rahul.s@example.com', '9876543210', 'Computer Engineering', 'TE - Sem 6'),
('BE-COMP-02', 'Priya Patil', 'priya.p@example.com', '9123456780', 'Computer Engineering', 'BE - Sem 7');