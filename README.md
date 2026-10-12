# School Management System

## 1. Overview

The School Management System is a role-based educational management backend developed using Laravel. It provides a JSON API that supports administrative operations, academic management, teacher workflows, student and parent access, attendance tracking, grading, announcements, alerts, and library administration.

The system supports six main user roles: Super Admin, Supervisor, Teacher, Student, Parent, and Librarian. Each role has access to functionality appropriate to its responsibilities, with authorization rules applied throughout the application.

The system aims to organize core school operations within a structured application, maintain relationships between academic data, and enforce access restrictions based on user roles and business rules.

## 2. Problem Statement

School administration involves several interconnected processes, including academic-year management, student enrollment, timetable preparation, attendance tracking, grade management, communication, and library recordkeeping.

Managing these processes requires consistent data organization, validation, and access control. Conflicts may arise when scheduling lessons, academic records may become inconsistent without appropriate constraints, and sensitive student information must only be accessible to authorized users.

The School Management System addresses these challenges by providing structured API functionality for school operations.

The main problems addressed by the system include:

- **Timetable conflicts:** Preventing a teacher or class section from being assigned to overlapping lessons.
- **Academic data organization:** Associating enrollments, grades, schedules, and examinations with the appropriate academic year and term.
- **Access control:** Restricting administrative operations and student-related information according to user roles and permissions.
- **Attendance management:** Maintaining attendance records and supporting parent-submitted absence justifications.
- **School communication:** Managing announcements and targeting them to relevant audiences.
- **Library recordkeeping:** Organizing book distribution and return records for students.

These challenges describe the operational areas addressed by the implemented functionality; they do not represent measured outcomes from a separate school-wide evaluation.

## 3. Objectives

The main objectives of the School Management System are to:

1. Organize essential school operations within a unified backend application.
2. Support academic-year and academic-term management.
3. Provide timetable and examination schedule management.
4. Organize academic information related to grades, subjects, sections, teachers, and student enrollments.
5. Support attendance tracking and absence-justification workflows.
6. Provide access to student grades and academic results.
7. Manage announcements and student alerts.
8. Maintain library book distribution and return records.
9. Enforce role-based authorization and protect parent-child data access.
10. Provide a maintainable architecture that separates business logic from HTTP request handling.

## 4. Key Features

The following features are implemented in the current project.

### 4.1 Authentication and Authorization

- Separate authentication flows for administrators and mobile users.
- Laravel Sanctum-based API authentication.
- Role-based access checks.
- Middleware and policy-based authorization.
- Parent-child context validation to ensure that parents can access only their own children through protected endpoints.

### 4.2 Academic Management

- Academic-year creation, updating, activation, and deactivation.
- Academic-term management.
- Browsing grades, subjects, and sections.
- Teacher assignment management.
- Student enrollment relationships and academic-year constraints.
- Validation of academic-year dates and overlapping periods.

### 4.3 Timetable and Examination Management

- Class timetable management.
- Weekly timetable retrieval and saving.
- Teacher availability management.
- Examination schedule management.
- Teacher and section scheduling-conflict validation.
- Schedule retrieval for relevant user roles.

### 4.4 Profiles and User Information

- Role-specific profile retrieval.
- Support for teacher, student, parent, and supervisor profiles.
- Relationships between users and their corresponding role-specific records.

### 4.5 Attendance and Absence Justifications

- Student attendance records and attendance history.
- Supervisor attendance-submission workflows.
- Parent-submitted absence justifications.
- Optional attachment support in the justification workflow.
- Validation that a student belongs to the requesting parent.
- Prevention of duplicate justifications for the same absence.
- Review statuses for absence justifications.

### 4.6 Grades and Academic Results

- Grade storage and retrieval.
- Student result retrieval within the appropriate academic context.
- Validation of student, academic year, academic term, and subject relationships.
- Database constraints to prevent duplicate grade records for the same student, year, term, and subject.

### 4.7 Announcements and Alerts

- Announcement creation, updating, and deletion.
- Announcement pinning.
- Audience-based announcement retrieval.
- Targeting by audience, grade, and section where applicable.
- Student alert records and retrieval workflows.

### 4.8 Library Management

- Book distribution record management.
- Book return record management.
- Association of library records with students and academic years.
- Validation of student and section membership within the relevant academic year.

## 5. System Architecture

The backend follows a service-oriented Laravel architecture. Responsibilities are divided among routes, middleware, controllers, request validation, service classes, Eloquent models, database operations, and API resources.

### 5.1 Request Processing Flow

The general request-processing flow is:

1. **HTTP Request:** A client sends a request to an API endpoint.
2. **Routes:** The request is directed to the appropriate domain-specific route.
3. **Middleware:** Authentication and access restrictions are applied.
4. **Controllers:** The controller receives the request and coordinates the operation.
5. **Form Requests:** Applicable request classes validate incoming data.
6. **Services:** Service classes execute business logic and enforce domain rules.
7. **Models and Database:** Eloquent models and database operations retrieve or modify records.
8. **Resources and JSON Responses:** Response resources format data for the client when applicable.

### 5.2 Backend Components

**Routes**

API routes are organized into domain-specific files, including authentication, administration, academic management, schedules, attendance, grades, announcements, alerts, profiles, and library operations.

**Controllers**

Controllers handle incoming requests and delegate most business operations to service classes.

**Service Layer**

Service classes contain the principal business rules and operations. Examples include:

- `AcademicYearService`
- `ClassScheduleService`
- `TeacherAvailabilityService`
- `ParentJustificationService`
- `AnnouncementService`
- `LibraryService`
- `StudentResultService`
- `SupervisorGradeService`
- `ProfileService`

**Models**

Eloquent models represent the main entities and relationships, including users, roles, students, teachers, parents, supervisors, academic years, terms, sections, enrollments, attendance records, grades, announcements, and library transactions.

**Request Validation**

Form Request classes define validation rules for relevant operations, helping ensure that invalid input is rejected before business logic is executed.

**Resources**

API Resource classes format response data into structured JSON payloads, including nested academic and user relationships where applicable.

**Middleware and Policies**

Middleware and policies enforce authorization boundaries. The system uses role helper methods, administrative middleware, parent-child context validation, and domain-specific policies.

The project does not use a Repository layer as a primary architectural component. Instead, business logic is concentrated in service classes.

### 5.3 Database Design

The database is organized around related functional domains:

- Identity and roles
- Academic years and terms
- Grades, subjects, sections, and enrollments
- Teacher assignments and availability
- Class and examination schedules
- Student and teacher attendance
- Absence justifications
- Student grades
- Announcements and alerts
- Library distribution and return records

Database constraints complement application-level validation. For example, timetable constraints prevent duplicate section time slots and teacher double-booking, while enrollment and grade constraints prevent specified duplicate records.

## 6. Technology Stack

| Technology | Purpose |
|---|---|
| PHP 8.3 or later | Backend programming language |
| Laravel 13 | Backend application framework |
| MySQL | Intended relational database for the application |
| Laravel Sanctum | API authentication and token-based access |
| Eloquent ORM | Database interaction and relationship management |
| JSON APIs | Communication between the backend and clients |
| Pest and PHPUnit | Automated application testing |

The test configuration supports in-memory SQLite for testing. The exact production database configuration and deployment environment must be confirmed from the relevant environment and deployment files.

## 7. Getting Started

The following commands represent a typical Laravel setup. They are provided as general guidance and must be checked against the project's existing setup instructions and environment configuration.

### 7.1 Prerequisites

- PHP 8.3 or later.
- Composer.
- A database supported by the application's configuration.
- Node.js and npm if frontend assets must be installed or built.

### 7.2 Installation

Clone or open the project repository, then install the PHP dependencies:

```bash
composer install
```

Create the environment configuration file if it does not already exist:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the database connection in the `.env` file before running migrations.

Run the database migrations when the database is ready:

```bash
php artisan migrate
```

Start the Laravel development server:

```bash
php artisan serve
```

### 7.3 Frontend Setup

If the frontend is part of the same development workflow, install its dependencies and start the development tooling from the frontend directory or project root, as appropriate:

```bash
npm install
npm run dev
```

The exact frontend commands depend on the project's `package.json` and existing development setup.

### 7.4 Configuration Notes

- Use the project's existing environment configuration where available.
- Confirm database credentials before running migrations.
- Configure authentication and API settings according to the application's environment.
- Use the existing project instructions for creating test accounts or seed data.
- Run the relevant automated tests when validating changes.

These instructions should not replace any existing project-specific setup steps.

## 8. Future Improvements

The following items are potential future improvements rather than claims about implemented functionality. They should be included in the official roadmap only if they align with the team's actual plans.

### 8.1 Enhanced Notification Mechanisms

Evaluate additional notification mechanisms for relevant school events, with clearly defined triggering rules and recipient groups.

### 8.2 Reporting and Analytics

Consider adding consolidated reports for attendance, academic results, and administrative operations to support school-level monitoring.

### 8.3 API Response Standardization

Improve consistency in response structures and validation conventions across different API domains to simplify frontend integration and future maintenance.

### 8.4 Expanded Automated Testing

Extend feature and integration tests for role permissions, parent-child access restrictions, scheduling constraints, academic-year rules, and grade validation.

### 8.5 Architectural Consistency

Gradually improve consistency in service interfaces, response resources, validation patterns, and authorization practices while preserving existing business rules.

All proposed improvements remain separate from the current implementation until they are explicitly developed, tested, and integrated into the project.

---

## Conclusion

The School Management System is a role-based Laravel backend that supports core educational and administrative operations through structured APIs. Its architecture emphasizes service-layer business logic, role-aware authorization, relational data modeling, and validation of critical academic constraints.

The existing implementation provides functionality across academic management, scheduling, attendance, grading, announcements, alerts, profiles, and library administration. The proposed future improvements focus on maintainability, testing, reporting, and possible extensions without misrepresenting planned functionality as completed work.
