# User Manual — CLT4BP

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Access](#2-system-access)
3. [User Roles](#3-user-roles)
4. [Courses Module](#4-courses-module)
5. [Assessments Module](#5-assessments-module)
6. [Learning Profiles](#6-learning-profiles)
7. [CLT Effects](#7-clt-effects)
8. [AI Material Generation](#8-ai-material-generation)
9. [Grading](#9-grading)
10. [Reports and Data Analysis](#10-reports-and-data-analysis)
11. [Student View](#11-student-view)
12. [System Administration](#12-system-administration)
13. [Glossary](#13-glossary)

---

## 1. Introduction

**CLT4BP** (*Cognitive Load Theory for Best Practice*) is an intelligent educational platform that combines **Cognitive Load Theory (CLT)** with the **4C/ID** instructional design model (Four-Component Instructional Design) to generate AI-assisted personalized learning materials.

### What does the system do?

The system allows instructors to:

- Create and manage courses with structured learning objectives.
- Apply standardized assessments to measure students' cognitive load, motivation, and learning strategies.
- Automatically generate individual and group learning profiles.
- Select appropriate CLT effects for the course context.
- Generate cognitively optimized instructional materials using AI (Claude by Anthropic).
- Grade responses and obtain performance reports.

For students, it allows them to:

- Access courses and personalized materials.
- Complete formative and summative assessments.
- View their progress and learning profile.

---

## 2. System Access

### 2.1 Login

1. Open your browser and navigate to the system URL.
2. Enter your **email address** and **password**.
3. Click **Sign In**.

The system automatically redirects to the main dashboard based on your role (Administrator, Instructor, or Student).

### 2.2 Logout

Click on your username in the upper-right corner and select **Sign Out**.

### 2.3 Account Recovery

If you have forgotten your password, contact the system administrator to reset your credentials.

---

## 3. User Roles

| Role | Description |
|------|-------------|
| **Administrator** | Manages users, has full system access, and can generate global reports. |
| **Instructor** | Creates and manages courses, evaluates students, generates materials with AI, and views reports. |
| **Student** | Accesses enrolled courses, completes assessments, and views materials and progress. |

---

## 4. Courses Module

> Available to: **Administrator**, **Instructor**

### 4.1 Create a Course

1. In the side menu, select **Courses** → **New Course**.
2. Fill in the form fields:
   - **Title:** Course name.
   - **Description:** Content summary.
   - **Learning Objectives:** List the objectives the course aims to achieve.
   - **Start and end date** (optional).
3. Click **Save**.

The course is created in **Draft** status. You can activate it when it is ready for students.

### 4.2 Course Statuses

| Status | Description |
|--------|-------------|
| `draft` | Draft — not visible to students. |
| `active` | Active and accessible to enrolled students. |
| `inactive` | Temporarily deactivated. |
| `completed` | Course has ended. |

### 4.3 Edit or Delete a Course

- In the course list, click the **edit** icon (pencil) next to the course.
- To delete, click the **delete** icon (trash). This action is irreversible.

### 4.4 Enroll Students

1. Open the desired course.
2. Go to the **Students** tab.
3. Click **Enroll Student**.
4. Search for the student by name or email and confirm the enrollment.

To **unenroll** a student, click the remove button next to their name in the list.

---

## 5. Assessments Module

> Available to: **Instructor** (create/manage), **Student** (complete)

### 5.1 Available Assessment Types

| Type | Description |
|------|-------------|
| **Recall** | Memory and content comprehension questions. |
| **Comprehension** | Conceptual understanding evaluation. |
| **MSLQ** | Motivated Strategies for Learning Questionnaire — measures motivation and learning strategies. |
| **CLS** | Cognitive Load Scale — measures perceived cognitive load. |
| **CIS** | Course Interest Scale — measures course interest. |
| **IMMS** | Instructional Materials Motivation Scale — evaluates motivation toward materials. |

### 5.2 Create an Assessment from a Template

1. Inside the course, go to the **Assessments** tab.
2. Click **New Assessment** → **From Template**.
3. Select the template type (MSLQ, CLS, etc.).
4. The assessment is created with predefined standardized questions.
5. Configure additional options if needed (time limit, instructions).

### 5.3 Create a Custom Assessment

1. Select **New Assessment** → **Custom**.
2. Define the title and instructions.
3. Add questions of the following types:
   - **Multiple choice**
   - **Free text**
   - **Likert scale**
   - **Numeric scale**
4. Save the assessment.

### 5.4 Activate / Deactivate an Assessment

Assessments are created in **inactive** status by default. For students to be able to complete it:

1. Locate the assessment in the list.
2. Click the **Activate** button.

To close the assessment, click **Deactivate** again.

### 5.5 View Student Responses

1. Open the assessment.
2. Go to the **Responses** tab.
3. View the list of students with their status (pending, auto-graded, graded).

---

## 6. Learning Profiles

> Available to: **Instructor**

Learning profiles are automatically generated from student responses to standardized assessments (MSLQ, CLS, CIS, IMMS).

### 6.1 Generate an Individual Profile

1. Inside the course, go to **Profiles** → **Students**.
2. Select the student.
3. Click **Generate Profile**.

The system analyzes the responses and creates a profile with dimensions such as:
- **Intrinsic / extrinsic motivation**
- **Learning strategies** (organization, elaboration, rehearsal)
- **Perceived cognitive load** (intrinsic, extraneous, germane)
- **Self-efficacy**
- **Interest in the course and materials**

### 6.2 Generate Profiles in Bulk

1. Go to **Profiles** → **Generate All**.
2. The system processes all students with available responses.

### 6.3 Group Profile

1. Go to **Profiles** → **Group**.
2. Click **Generate Group Profile**.

The group profile aggregates metrics from all students in the course and serves as the basis for material generation.

---

## 7. CLT Effects

> Available to: **Instructor**

**CLT Effects** are instructional strategies derived from Cognitive Load Theory. Selecting the correct effects determines how learning materials will be generated.

### 7.1 View Available Effects

1. Inside the course, go to **CLT Effects**.
2. The system displays effects organized into two categories:
   - **For new knowledge** (e.g., worked example effect, reduced redundancy effect)
   - **For prior knowledge** (e.g., expertise reversal effect, split-attention effect)

### 7.2 Select Effects for the Course

1. Check the effects you want to apply.
2. The system may offer **recommendations** based on the group learning profile.
3. Click **Save Selection**.

The selection is saved and will be used in the next material generation.

---

## 8. AI Material Generation

> Available to: **Instructor**

This is the core functionality of the system. Materials are generated using the **Claude (Anthropic)** AI model and are optimized according to Cognitive Load Theory and the 4C/ID model.

### 8.1 Material Types (4C/ID Model)

| Type | Description |
|------|-------------|
| **Learning Task** | Main learning task the student must complete. |
| **Support Info** | Conceptual and contextual support information. |
| **Procedural Info** | Concrete steps and procedures (how to do something). |
| **Verbal Protocol** | Verbal script or step-by-step explanation. |
| **Examples** | Worked examples and case studies. |

### 8.2 Generate a Material

1. Inside the course, go to **Materials** → **Generate Material**.
2. Configure the generation parameters:
   - **Material type** (Learning Task, Support Info, etc.)
   - **Target student or group**
   - **Learning objectives** covered by the material
   - **CLT effects** to apply (loaded automatically from the previous selection)
3. Click **Generate**.

The system sends the request to the AI service and returns the material within seconds. The material is created in **inactive** status.

### 8.3 Review and Activate a Material

1. In the materials list, click on the generated material to review it.
2. If the content is appropriate, click **Activate**.
3. The material becomes available to the course students.

### 8.4 Configure a Timer

For materials with a time limit:

1. Open the material.
2. Click **Configure Timer**.
3. Set the duration in minutes.
4. Save. The student will see a countdown when accessing the material.

### 8.5 Access Logs

The system automatically records:
- When the student accesses the material.
- When they complete it.
- Total time spent.

You can view these records in the **Access Logs** tab of the material.

---

## 9. Grading

> Available to: **Instructor**

### 9.1 Automatic Grading

Multiple-choice and scale questions are automatically graded at submission. The system assigns scores and updates the response status to `auto-graded`.

### 9.2 Manual Grading

Free-text questions require instructor review:

1. Go to **Grading** → **Pending** (or from the assessment → Responses tab).
2. Select the pending response.
3. Read the student's response.
4. Assign a numeric score and optionally a comment.
5. Click **Save Grade**.

### 9.3 Revert a Grade

If you need to correct an already assigned grade:

1. Open the graded response.
2. Click **Revert Grade**.
3. The response returns to pending status for re-grading.

### 9.4 Pending Grades Summary

The **Pending Grading** panel in the course shows a summary of all assessments with ungraded responses, grouped by assessment.

---

## 10. Reports and Data Analysis

> Available to: **Instructor**, **Administrator**

### 10.1 Instructor Report

Access from **Reports** → **Course Report**. Includes:

- Total enrolled students.
- Assessment completion rates.
- Average scores per assessment.
- Aggregated cognitive load data.
- Material engagement statistics.

### 10.2 Pre/Post Comparison

Useful for measuring the impact of the instructional intervention:

1. Go to **Data Collection** → **Pre/Post Comparison**.
2. Select the pre-test and post-test assessments to compare.
3. The system calculates score differences and cognitive load changes.

### 10.3 Export Data (CSV)

For external analysis or research:

1. Go to **Data Collection** → **Export**.
2. Click **Download CSV**.

The file includes response data, scores, profiles, and material access logs.

### 10.4 Data by Student

1. Go to **Data Collection** → select a student.
2. View the complete history: assessments, responses, scores, material access logs, and profile evolution.

---

## 11. Student View

> Available to: **Student**

### 11.1 Main Dashboard

Upon logging in, the student sees their enrolled courses with status and overall progress.

### 11.2 Access a Course

1. Click on the course name.
2. View the available (active) assessments and materials.

### 11.3 Complete an Assessment

1. Click on the available assessment.
2. Read the instructions carefully.
3. If the assessment has a time limit, the countdown will start automatically when you begin.
4. Answer all questions and click **Submit**.

> **Important:** Once submitted, you will not be able to modify your responses.

### 11.4 Access Learning Materials

1. In the course, go to the **Materials** tab.
2. Click on the material to open it.
3. If it has a timer, you will see a countdown.
4. When finished, click **Complete** to record your progress.

### 11.5 View My Personal Report

1. Go to **My Report** within the course.
2. View your assessment scores, learning profile, and completed materials.

### 11.6 View My Learning Profile

The learning profile (generated by the instructor) shows:
- Motivation level and predominant strategies.
- Reported cognitive load.
- Study recommendations.

---

## 12. System Administration

> Available to: **Administrator**

### 12.1 User Management

1. Go to **Administration** → **Users**.
2. You can:
   - **Create** new instructors or students.
   - **Edit** an existing user's data.
   - **Deactivate** an account (the user will not be able to log in).

### 12.2 Role Assignment

When creating or editing a user, select the corresponding role:
- `admin` — Administrator
- `instructor` — Instructor
- `student` — Student

### 12.3 Global Reports

From the administration panel you can access global system metrics:
- Total courses, users, and active enrollments.
- Recent activity from instructors and students.

---

## 13. Glossary

| Term | Definition |
|------|------------|
| **CLT** | Cognitive Load Theory — theoretical framework describing how the human mind processes and stores information. |
| **4C/ID** | Four-Component Instructional Design — instructional design model that structures materials into four components: learning tasks, support information, procedural information, and verbal protocols. |
| **Intrinsic Cognitive Load** | Mental effort associated with the inherent complexity of the content. |
| **Extraneous Cognitive Load** | Mental effort generated by poor instructional design. |
| **Germane Cognitive Load** | Mental effort dedicated to building and automating knowledge schemas. |
| **MSLQ** | Motivated Strategies for Learning Questionnaire — instrument for measuring motivation and learning strategies. |
| **CLS** | Cognitive Load Scale — scale for measuring perceived cognitive load. |
| **CIS** | Course Interest Scale — scale for measuring interest in the course. |
| **IMMS** | Instructional Materials Motivation Scale — scale for measuring motivation toward instructional materials. |
| **Learning Profile** | Set of metrics describing a student's cognitive and motivational characteristics. |
| **CLT Effects** | Instructional strategies derived from CLT research (e.g., worked example effect, redundancy effect, expertise reversal effect). |
| **4C/ID Material** | Educational content generated according to the four components of the 4C/ID model. |
| **Sanctum** | Token-based authentication system used by the backend. |
| **Active/Inactive Status** | Indicates whether an assessment or material is available to students. |

---
