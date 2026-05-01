<?php

const ROLE_ADMIN ='admin';
const ROLE_INSTRUCTOR ='instructor';
const ROLE_STUDENT ='student';
const ROLES =['admin','instructor','student'];

const USER_STATUS_ACTIVE = 'active';
const USER_STATUS_DISABLED = 'disabled';
const COURSE_STATUS_ACTIVE = 'active';
const COURSE_STATUS_INACTIVE = 'inactive';
const ENROLLMENT_STATUS_REQUESTED = 'requested';
const ENROLLMENT_STATUS_ACTIVE = 'active';
const ENROLLMENT_STATUS_REJECTED = 'rejected';
const ENROLLMENT_STATUS_CANCELED = 'canceled';
const ENROLLMENT_STATUS_COMPLETED = 'completed';
const ENROLLMENT_STATUS_COURSE_INACTIVE = 'course_inactive';
const ENROLLMENT_STATUS_WITHDRAWN = 'withdrawn';

const MSG_UNAUTHORIZED = 'Unauthorized';
const MSG_UNEXPECTED_ERROR = 'Unexpected error';
const MSG_USER_NOT_FOUND = 'User not found';
const MSG_USER_CREATED_SUCCESSFULLY = 'User created successfully';
const MSG_EMAIL_ALREADY_EXISTS = 'Email already exists';
const MSG_FAILED_TO_CREATE_USER = 'Failed to create user';
const MSG_NO_CHANGES_MADE = 'No changes made';
const MSG_USER_UPDATED_SUCCESSFULLY = 'User updated successfully';
const MSG_USER_DEACTIVATED_SUCCESSFULLY = 'User deactivated successfully';
const MSG_FAILED_TO_DEACTIVATE_USER = 'Failed to deactivate user';

const DASHBOARD_STUDENT_ROUTE = '/public/dashboards/student_dashboard.php';
const DASHBOARD_INSTRUCTOR_ROUTE = '/public/dashboards/instructor_dashboard.php';
const DASHBOARD_ADMIN_ROUTE = '/public/dashboards/admin_dashboard.php';

const LIST_STUDENTS_ROUTE = '../public/listStudents.php';
const LIST_INSTRUCTORS_ROUTE = '../public/listInstructors.php';
const LIST_ADMINS_ROUTE = '../public/listAdmins.php';
