USE ssis;

CREATE TABLE IF NOT EXISTS course_schedules (
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

CREATE TABLE IF NOT EXISTS student_courses (
  student_id INT NOT NULL,
  course_id INT NOT NULL,
  semester VARCHAR(30) NOT NULL,
  school_year VARCHAR(20) NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (student_id, course_id, semester, school_year),
  FOREIGN KEY (student_id) REFERENCES students(user_id),
  FOREIGN KEY (course_id) REFERENCES courses(id)
);

INSERT IGNORE INTO course_schedules
  (course_id, semester, school_year, days, start_time, end_time, room, instructor)
SELECT id, '1st Semester', '2026-2027', 'Monday / Wednesday', '08:00:00', '09:30:00', 'ICT Lab 1', 'Maria Santos'
FROM courses WHERE code = 'CSS109';

INSERT IGNORE INTO course_schedules
  (course_id, semester, school_year, days, start_time, end_time, room, instructor)
SELECT id, '1st Semester', '2026-2027', 'Tuesday / Thursday', '09:00:00', '10:30:00', 'Room 204', 'Jose Reyes'
FROM courses WHERE code = 'CSS111';

INSERT IGNORE INTO course_schedules
  (course_id, semester, school_year, days, start_time, end_time, room, instructor)
SELECT id, '1st Semester', '2026-2027', 'Monday / Wednesday', '10:00:00', '11:30:00', 'Room 203', 'Ana Dela Cruz'
FROM courses WHERE code = 'ITP103';

INSERT IGNORE INTO course_schedules
  (course_id, semester, school_year, days, start_time, end_time, room, instructor)
SELECT id, '1st Semester', '2026-2027', 'Tuesday / Thursday', '13:00:00', '14:30:00', 'ICT Lab 2', 'Mark Rivera'
FROM courses WHERE code = 'ITP104';

INSERT IGNORE INTO course_schedules
  (course_id, semester, school_year, days, start_time, end_time, room, instructor)
SELECT id, '1st Semester', '2026-2027', 'Monday / Wednesday', '13:00:00', '14:30:00', 'ICT Lab 1', 'Leah Garcia'
FROM courses WHERE code = 'ITP107';

INSERT IGNORE INTO course_schedules
  (course_id, semester, school_year, days, start_time, end_time, room, instructor)
SELECT id, '1st Semester', '2026-2027', 'Friday', '08:00:00', '11:00:00', 'ICT Lab 3', 'Paolo Cruz'
FROM courses WHERE code = 'ITEW3';

-- Demo assignment for student 260001; adjust registrations in the registrar's Course Registration tab.
INSERT IGNORE INTO student_courses (student_id, course_id, semester, school_year)
SELECT 1, id, '1st Semester', '2026-2027' FROM courses
WHERE EXISTS (SELECT 1 FROM students WHERE user_id = 1);

-- Add school fee charges and office-specific clearance accounts.
ALTER TABLE users
  MODIFY role ENUM('student','registrar','cashier','dept','library','guidance','clinic','affairs','lab','property','admin') NOT NULL;

ALTER TABLE clearances
  MODIFY office VARCHAR(60) NOT NULL;

CREATE TABLE IF NOT EXISTS student_fee_charges (
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
