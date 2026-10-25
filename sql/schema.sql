CREATE DATABASE IF NOT EXISTS ssis CHARACTER SET utf8mb4;
USE ssis;
DROP TABLE IF EXISTS clearances, student_fee_charges, payments, document_requests, document_types, grades, enrollments, student_courses, course_schedules, courses, students, users;

CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(30) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(80) NOT NULL, role ENUM('student','registrar','cashier','dept','library','guidance','clinic','affairs','lab','property','admin') NOT NULL, active TINYINT(1) NOT NULL DEFAULT 1);
CREATE TABLE students (user_id INT PRIMARY KEY, program VARCHAR(80) NOT NULL, year_level VARCHAR(20) NOT NULL, FOREIGN KEY (user_id) REFERENCES users(id));
CREATE TABLE courses (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(10) NOT NULL UNIQUE, title VARCHAR(80) NOT NULL, units INT NOT NULL DEFAULT 3);
CREATE TABLE course_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  semester VARCHAR(30) NOT NULL,
  school_year VARCHAR(20) NOT NULL,
  days VARCHAR(30) NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  room VARCHAR(40) NOT NULL,
  instructor VARCHAR(80) NOT NULL,
  UNIQUE KEY uq_course_term (course_id, semester, school_year),
  FOREIGN KEY (course_id) REFERENCES courses(id)
);
CREATE TABLE student_courses (
  student_id INT NOT NULL,
  course_id INT NOT NULL,
  semester VARCHAR(30) NOT NULL,
  school_year VARCHAR(20) NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (student_id, course_id, semester, school_year),
  FOREIGN KEY (student_id) REFERENCES students(user_id),
  FOREIGN KEY (course_id) REFERENCES courses(id)
);
CREATE TABLE enrollments (student_id INT PRIMARY KEY, status ENUM('Pending','Enrolled') NOT NULL DEFAULT 'Pending', FOREIGN KEY (student_id) REFERENCES students(user_id));
CREATE TABLE grades (student_id INT, course_id INT, grade DECIMAL(3,2) NOT NULL, PRIMARY KEY (student_id, course_id),
  FOREIGN KEY (student_id) REFERENCES students(user_id), FOREIGN KEY (course_id) REFERENCES courses(id));
CREATE TABLE document_types (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(60) NOT NULL, fee INT NOT NULL, days INT NOT NULL);
CREATE TABLE document_requests (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, type_id INT NOT NULL, purpose VARCHAR(200) NOT NULL,
  status ENUM('Pending','Processing','Ready','Completed','Rejected') NOT NULL DEFAULT 'Pending', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(user_id), FOREIGN KEY (type_id) REFERENCES document_types(id));
CREATE TABLE payments (id INT AUTO_INCREMENT PRIMARY KEY, request_id INT NOT NULL UNIQUE, amount INT NOT NULL, receipt_no VARCHAR(12) UNIQUE,
  paid_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (request_id) REFERENCES document_requests(id));
CREATE TABLE student_fee_charges (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  category ENUM('Enrollment','Laboratory','Other') NOT NULL,
  description VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  due_date DATE NULL,
  semester VARCHAR(30) NOT NULL,
  school_year VARCHAR(20) NOT NULL,
  status ENUM('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid',
  receipt_no VARCHAR(24) UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at TIMESTAMP NULL,
  created_by INT NOT NULL,
  paid_by INT NULL,
  FOREIGN KEY (student_id) REFERENCES students(user_id),
  FOREIGN KEY (created_by) REFERENCES users(id),
  FOREIGN KEY (paid_by) REFERENCES users(id)
);
CREATE TABLE clearances (student_id INT, office VARCHAR(60) NOT NULL, status ENUM('Pending','Cleared') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (student_id, office), FOREIGN KEY (student_id) REFERENCES students(user_id));

-- Demo data. Every demo password is: password
INSERT INTO users (id,username,password_hash,name,role) VALUES
 (1,'260001','$2b$10$tt6IC7XRb54RTaF56KfLo.ACgBeLKFuPCFNOZGS3d1mtEBS2.WxtS','Chyna Agase','student'),(2,'260002','$2b$10$tt6IC7XRb54RTaF56KfLo.ACgBeLKFuPCFNOZGS3d1mtEBS2.WxtS','Juan Dela Cruz','student'),
 (3,'registrar','$2b$10$tt6IC7XRb54RTaF56KfLo.ACgBeLKFuPCFNOZGS3d1mtEBS2.WxtS','Registrar Staff','registrar'),(4,'cashier','$2b$10$tt6IC7XRb54RTaF56KfLo.ACgBeLKFuPCFNOZGS3d1mtEBS2.WxtS','Cashier Staff','cashier'),
 (5,'dept','$2b$10$tt6IC7XRb54RTaF56KfLo.ACgBeLKFuPCFNOZGS3d1mtEBS2.WxtS','Department Personnel','dept'),(6,'admin','$2b$10$tt6IC7XRb54RTaF56KfLo.ACgBeLKFuPCFNOZGS3d1mtEBS2.WxtS','System Admin','admin');
INSERT INTO students VALUES (1,'BS Information Technology','3rd Year'),(2,'BS Information Technology','3rd Year');
INSERT INTO courses (code,title) VALUES ('CSS109','System Analysis and Design'),('CSS111','Networking and Communication 1'),('ITP103','System Integration and Architecture'),
 ('ITP104','Information Management 2'),('ITP107','Mobile Application Development'),('ITEW3','Mobile Programming');
INSERT INTO course_schedules (course_id, semester, school_year, days, start_time, end_time, room, instructor)
SELECT id, '1st Semester', '2026-2027', 'Monday / Wednesday', '08:00:00', '09:30:00', 'ICT Lab 1', 'Maria Santos' FROM courses WHERE code = 'CSS109'
UNION ALL SELECT id, '1st Semester', '2026-2027', 'Tuesday / Thursday', '09:00:00', '10:30:00', 'Room 204', 'Jose Reyes' FROM courses WHERE code = 'CSS111'
UNION ALL SELECT id, '1st Semester', '2026-2027', 'Monday / Wednesday', '10:00:00', '11:30:00', 'Room 203', 'Ana Dela Cruz' FROM courses WHERE code = 'ITP103'
UNION ALL SELECT id, '1st Semester', '2026-2027', 'Tuesday / Thursday', '13:00:00', '14:30:00', 'ICT Lab 2', 'Mark Rivera' FROM courses WHERE code = 'ITP104'
UNION ALL SELECT id, '1st Semester', '2026-2027', 'Monday / Wednesday', '13:00:00', '14:30:00', 'ICT Lab 1', 'Leah Garcia' FROM courses WHERE code = 'ITP107'
UNION ALL SELECT id, '1st Semester', '2026-2027', 'Friday', '08:00:00', '11:00:00', 'ICT Lab 3', 'Paolo Cruz' FROM courses WHERE code = 'ITEW3';
INSERT INTO enrollments VALUES (1,'Enrolled'),(2,'Pending');
INSERT INTO student_courses (student_id,course_id,semester,school_year)
SELECT 1,id,'1st Semester','2026-2027' FROM courses;
INSERT INTO grades VALUES (1,1,1.50),(1,3,1.75);
INSERT INTO document_types (name,fee,days) VALUES ('Transcript of Records',200,2),('Certificate of Registration',100,2),('Good Moral Certificate',100,1);
INSERT INTO document_requests (id,student_id,type_id,purpose,status,created_at) VALUES
 (1,1,1,'For Employment','Ready','2026-10-08 09:00:00'),(2,1,2,'For Scholarship','Processing','2026-10-09 09:00:00'),(3,1,3,'For Employment','Pending','2026-10-10 09:00:00');
INSERT INTO payments (request_id,amount,receipt_no,paid_at) VALUES (1,200,'OR-1000','2026-10-08 10:00:00'),(2,100,'OR-1001','2026-10-09 10:00:00');
INSERT INTO student_fee_charges (student_id,category,description,amount,due_date,semester,school_year,status,receipt_no,paid_at,created_by,paid_by) VALUES
 (1,'Enrollment','First semester enrollment fee',12500.00,'2026-10-30','1st Semester','2026-2027','Unpaid',NULL,NULL,4,NULL),
 (1,'Laboratory','Information technology laboratory fee',1850.00,'2026-10-30','1st Semester','2026-2027','Paid','STF-000002','2026-10-10 10:30:00',4,4),
 (1,'Other','Student ID replacement',350.00,NULL,'1st Semester','2026-2027','Unpaid',NULL,NULL,4,NULL);
INSERT INTO clearances VALUES (1,'Library','Cleared'),(1,'IT Department','Cleared'),(1,'Registrar Office','Pending'),(1,'Cashier','Pending'),(1,'Guidance Office','Pending'),(1,'Clinic','Pending'),(1,'Student Affairs','Pending'),(1,'Laboratory','Pending'),(1,'Property Office','Pending');
