create database enrollment_db;

use enrollment_db;

create table users(
	id int auto_increment primary key,
    role enum("student","instructor","admin") not null,
    email varchar(255) unique not null,
    password varchar(255) not null,
    status enum("active","disabled") default "active",
    created_at datetime default current_timestamp,
    updated_at datetime default current_timestamp on update current_timestamp,
    is_active ENUM('active','inactive') DEFAULT 'active'
);

insert into users (role, email, password) values
('student', 'rahul@example.com', 'pass123'),
('student', 'priya@example.com', 'pass123'),
('student', 'amit@example.com', 'pass123'),
('instructor', 'john@example.com', 'pass123'),
('instructor', 'sara@example.com', 'pass123'),
('admin', 'admin@example.com', 'admin123');

create table students(
	user_id int primary key,
    name varchar(100) not null,
    phone varchar(20),
    enrolled_on date,
    created_at datetime default current_timestamp,
    updated_at datetime default current_timestamp on update current_timestamp,
    is_active ENUM('active','inactive') DEFAULT 'active',
    
    foreign key(user_id) references users(id) on delete cascade
);

insert into students (user_id, name, phone, enrolled_on) values
(1, 'Rahul Sharma', '9876543210', '2024-01-10'),
(2, 'Priya Patel', '9123456780', '2024-02-15'),
(3, 'Amit Verma', '9988776655', '2024-03-01');

create table instructors(
	user_id int primary key,
    name varchar(100) not null,
    salary int not null,
    phone varchar(20),
    created_at datetime default current_timestamp,
    updated_at datetime default current_timestamp on update current_timestamp,
    is_active ENUM('active','inactive') DEFAULT 'active',
    
    foreign key(user_id) references users(id) on delete cascade
);

insert into instructors (user_id, name, salary, phone) values
(4, 'John Doe', 50000, '9000000001'),
(5, 'Sara Smith', 60000, '9000000002');

create table courses(
	id int auto_increment primary key,
    name varchar(255) not null,
    duration_weeks int, 
    max_seats int,
    created_at datetime default current_timestamp,
    updated_at datetime default current_timestamp on update current_timestamp,
    is_active ENUM('active','inactive') DEFAULT 'active'
);

insert into courses (name, duration_weeks, max_seats) values
('PHP for Beginners', 6, 30),
('Advanced PHP', 8, 25),
('Database Design', 5, 40);

create table courses_instructors(
	course_id int,
    instructor_id int,
    created_at datetime default current_timestamp,

    primary key(instructor_id, course_id),
    foreign key(course_id) references courses(id) on delete cascade,
    foreign key(instructor_id) references instructors(user_id) on delete cascade
);

insert into courses_instructors (course_id, instructor_id) values
(1, 4),
(2, 4),
(3, 5);

create table enrollments(
	id int auto_increment primary key, 
    student_id int not null, 
    course_id int not null,
    instructor_id int not null,
    enrolled_date date,
    status enum("active","canceled","completed") default "active",
    created_at datetime default current_timestamp,
    updated_at datetime default current_timestamp on update current_timestamp,
    
    unique(student_id, course_id),
    foreign key(student_id) references students(user_id) on delete cascade,
    foreign key(course_id, instructor_id) references courses_instructors(course_id, instructor_id) on delete cascade on update cascade,
);

insert into enrollments (student_id, course_id, instructor_id, enrolled_date, status) values
(1, 1, 4, '2024-04-01', 'active'),
(1, 2, 4, '2024-04-05', 'completed'),
(2, 1, 4, '2024-04-03', 'active'),
(2, 3, 5, '2024-04-07', 'active'),
(3, 3, 5, '2024-04-10', 'canceled');

create index idx_student on enrollments(student_id);
create index idx_course on enrollments(course_id);
create index idx_instructor on enrollments(instructor_id);
create index idx_course_instructor on courses_instructors(course_id, instructor_id);