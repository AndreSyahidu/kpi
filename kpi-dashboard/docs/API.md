# KPI Dashboard API Documentation

Complete REST API documentation for the KPI Dashboard plugin.

**Base URL:** `/wp-json/kpi/v1`

**Authentication:** All protected endpoints require JWT Bearer token in the `Authorization` header:
```
Authorization: Bearer <access_token>
```

---

## Table of Contents

1. [Authentication](#authentication)
2. [Users](#users)
3. [Departments](#departments)
4. [Positions](#positions)
5. [KPIs](#kpis)
6. [Data Entry](#data-entry)
7. [Approvals](#approvals)
8. [Analytics](#analytics)
9. [Reports](#reports)
10. [Notifications](#notifications)
11. [Settings](#settings)
12. [Error Handling](#error-handling)

---

## Authentication

### Login

Authenticate user and receive access token.

**Endpoint:** `POST /auth/login`

**Permission:** Public

**Request Body:**
```json
{
  "username": "admin",
  "password": "admin",
  "remember": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires_in": 900,
    "user": {
      "id": 1,
      "username": "admin",
      "full_name": "Administrator",
      "email": "admin@example.com",
      "role": "super_admin",
      "department_id": null,
      "position_id": null
    }
  }
}
```

**Error Codes:**
- `invalid_credentials` (401): Invalid username or password
- `inactive_user` (403): User account is inactive

---

### Logout

Invalidate current access token.

**Endpoint:** `POST /auth/logout`

**Permission:** Authenticated users

**Headers:**
```
Authorization: Bearer <access_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

### Refresh Token

Get new access token using refresh token.

**Endpoint:** `POST /auth/refresh`

**Permission:** Public

**Request Body:**
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Token refreshed",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires_in": 900
  }
}
```

**Error Codes:**
- `invalid_token` (401): Refresh token is invalid or expired

---

### Get Current User

Get current authenticated user information.

**Endpoint:** `GET /auth/me`

**Permission:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "full_name": "Administrator",
    "email": "admin@example.com",
    "role": "super_admin",
    "department_id": null,
    "position_id": null,
    "department": null,
    "position": null,
    "is_active": true,
    "created_at": "2024-01-01 00:00:00",
    "updated_at": "2024-01-01 00:00:00"
  }
}
```

---

### Change Password

Change password for current user.

**Endpoint:** `POST /auth/change-password`

**Permission:** Authenticated users

**Request Body:**
```json
{
  "old_password": "current_password",
  "new_password": "new_password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

**Error Codes:**
- `invalid_password` (400): Old password is incorrect
- `weak_password` (400): New password doesn't meet requirements

---

### Forgot Password

Request password reset email.

**Endpoint:** `POST /auth/forgot-password`

**Permission:** Public

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset email sent"
}
```

---

### Reset Password

Reset password using token from email.

**Endpoint:** `POST /auth/reset-password`

**Permission:** Public

**Request Body:**
```json
{
  "user_id": 1,
  "token": "reset_token_from_email",
  "new_password": "new_password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset successful"
}
```

**Error Codes:**
- `invalid_token` (400): Reset token is invalid or expired

---

## Users

### List Users

Get paginated list of users.

**Endpoint:** `GET /users`

**Permission:** Super Admin, Department Head (can only see own department)

**Query Parameters:**
- `page` (int, default: 1): Page number
- `per_page` (int, default: 20): Items per page
- `search` (string): Search by username, full name, or email
- `role` (string): Filter by role (super_admin, dept_head, manager, staff)
- `department_id` (int): Filter by department ID
- `is_active` (bool): Filter by active status

**Example:**
```
GET /users?page=1&per_page=20&role=staff&department_id=2
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "username": "john.doe",
      "full_name": "John Doe",
      "email": "john@example.com",
      "role": "staff",
      "department_id": 2,
      "position_id": 5,
      "department": {
        "id": 2,
        "name": "Sales"
      },
      "position": {
        "id": 5,
        "title": "Sales Executive"
      },
      "is_active": true,
      "created_at": "2024-01-01 00:00:00"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 50,
    "total_pages": 3
  }
}
```

---

### Get User

Get single user by ID.

**Endpoint:** `GET /users/{id}`

**Permission:** Super Admin, Department Head (own department), Manager (own team), Self

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "username": "john.doe",
    "full_name": "John Doe",
    "email": "john@example.com",
    "role": "staff",
    "department_id": 2,
    "position_id": 5,
    "department": {
      "id": 2,
      "name": "Sales",
      "parent_id": null
    },
    "position": {
      "id": 5,
      "title": "Sales Executive",
      "level": "staff"
    },
    "is_active": true,
    "created_at": "2024-01-01 00:00:00",
    "updated_at": "2024-01-01 00:00:00"
  }
}
```

---

### Create User

Create new user.

**Endpoint:** `POST /users`

**Permission:** Super Admin, Department Head (for own department)

**Request Body:**
```json
{
  "username": "jane.doe",
  "full_name": "Jane Doe",
  "email": "jane@example.com",
  "password": "secure_password",
  "role": "staff",
  "department_id": 2,
  "position_id": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 3,
    "username": "jane.doe",
    "full_name": "Jane Doe",
    "email": "jane@example.com",
    "role": "staff"
  }
}
```

**Validation Rules:**
- `username`: Required, unique, alphanumeric + underscore/dash, 3-50 chars
- `full_name`: Required, 2-100 chars
- `email`: Required, valid email, unique
- `password`: Required, minimum 8 chars (on creation)
- `role`: Required, one of: super_admin, dept_head, manager, staff
- `department_id`: Required for non-super_admin roles
- `position_id`: Required for manager/staff roles

---

### Update User

Update existing user.

**Endpoint:** `PUT /users/{id}`

**Permission:** Super Admin, Department Head (own department), Self (limited fields)

**Request Body:**
```json
{
  "full_name": "Jane Smith",
  "email": "jane.smith@example.com",
  "role": "manager",
  "position_id": 6,
  "is_active": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "id": 3,
    "username": "jane.doe",
    "full_name": "Jane Smith",
    "email": "jane.smith@example.com"
  }
}
```

---

### Delete User

Soft delete user (set is_active to false).

**Endpoint:** `DELETE /users/{id}`

**Permission:** Super Admin, Department Head (own department)

**Response:**
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

---

## Departments

### List Departments

Get hierarchical list of departments.

**Endpoint:** `GET /departments`

**Permission:** All authenticated users

**Query Parameters:**
- `parent_id` (int): Filter by parent department
- `is_active` (bool): Filter by active status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Corporate",
      "parent_id": null,
      "description": "Corporate office",
      "head_id": 2,
      "head": {
        "id": 2,
        "full_name": "John Doe"
      },
      "is_active": true,
      "created_at": "2024-01-01 00:00:00",
      "children": [
        {
          "id": 3,
          "name": "Finance",
          "parent_id": 1
        }
      ]
    }
  ]
}
```

---

### Get Department

Get single department with statistics.

**Endpoint:** `GET /departments/{id}`

**Permission:** All authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Sales",
    "parent_id": null,
    "description": "Sales department",
    "head_id": 5,
    "head": {
      "id": 5,
      "full_name": "Sarah Johnson",
      "email": "sarah@example.com"
    },
    "stats": {
      "total_users": 15,
      "total_kpis": 8,
      "total_positions": 4
    },
    "is_active": true,
    "created_at": "2024-01-01 00:00:00"
  }
}
```

---

### Create Department

Create new department.

**Endpoint:** `POST /departments`

**Permission:** Super Admin

**Request Body:**
```json
{
  "name": "Marketing",
  "parent_id": null,
  "description": "Marketing department",
  "head_id": null
}
```

**Response:**
```json
{
  "success": true,
  "message": "Department created successfully",
  "data": {
    "id": 7,
    "name": "Marketing",
    "parent_id": null
  }
}
```

---

### Update Department

Update existing department.

**Endpoint:** `PUT /departments/{id}`

**Permission:** Super Admin

**Request Body:**
```json
{
  "name": "Marketing & Communications",
  "description": "Updated description",
  "head_id": 10
}
```

**Response:**
```json
{
  "success": true,
  "message": "Department updated successfully"
}
```

---

### Delete Department

Soft delete department.

**Endpoint:** `DELETE /departments/{id}`

**Permission:** Super Admin

**Response:**
```json
{
  "success": true,
  "message": "Department deleted successfully"
}
```

---

### Get Department Hierarchy

Get full department hierarchy tree.

**Endpoint:** `GET /departments/hierarchy`

**Permission:** All authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "departments": [
      {
        "id": 1,
        "name": "Corporate",
        "level": 0,
        "children": [
          {
            "id": 2,
            "name": "Finance",
            "level": 1,
            "children": []
          }
        ]
      }
    ]
  }
}
```

---

### Assign Department Head

Assign user as department head.

**Endpoint:** `POST /departments/{id}/assign-head`

**Permission:** Super Admin

**Request Body:**
```json
{
  "user_id": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Department head assigned successfully"
}
```

---

## Positions

### List Positions

Get list of positions.

**Endpoint:** `GET /positions`

**Permission:** All authenticated users

**Query Parameters:**
- `department_id` (int): Filter by department
- `level` (string): Filter by level (director, manager, senior, staff)
- `is_active` (bool): Filter by active status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Sales Director",
      "department_id": 2,
      "department": {
        "id": 2,
        "name": "Sales"
      },
      "level": "director",
      "description": "Sales department director",
      "is_active": true,
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

---

### Create Position

Create new position.

**Endpoint:** `POST /positions`

**Permission:** Super Admin, Department Head (for own department)

**Request Body:**
```json
{
  "title": "Senior Sales Executive",
  "department_id": 2,
  "level": "senior",
  "description": "Senior sales position"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Position created successfully",
  "data": {
    "id": 8,
    "title": "Senior Sales Executive"
  }
}
```

---

## KPIs

### List KPIs

Get list of KPI definitions.

**Endpoint:** `GET /kpis`

**Permission:** All authenticated users

**Query Parameters:**
- `department_id` (int): Filter by department
- `type` (string): Filter by type (department, position, personal)
- `measurement_type` (string): Filter by measurement (number, percentage, currency, boolean)
- `is_active` (bool): Filter by active status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Monthly Sales Revenue",
      "description": "Total sales revenue per month",
      "type": "department",
      "measurement_type": "currency",
      "target_value": 1000000000,
      "unit": "IDR",
      "department_id": 2,
      "position_id": null,
      "department": {
        "id": 2,
        "name": "Sales"
      },
      "frequency": "monthly",
      "is_active": true,
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

---

### Get KPI

Get single KPI with assignments.

**Endpoint:** `GET /kpis/{id}`

**Permission:** All authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Monthly Sales Revenue",
    "description": "Total sales revenue per month",
    "type": "department",
    "measurement_type": "currency",
    "target_value": 1000000000,
    "unit": "IDR",
    "frequency": "monthly",
    "department_id": 2,
    "position_id": null,
    "assignments": [
      {
        "user_id": 5,
        "user": {
          "id": 5,
          "full_name": "John Doe"
        },
        "target_value": 50000000
      }
    ],
    "is_active": true
  }
}
```

---

### Create KPI

Create new KPI definition.

**Endpoint:** `POST /kpis`

**Permission:** Super Admin, Department Head (for own department)

**Request Body:**
```json
{
  "name": "Customer Satisfaction Score",
  "description": "Monthly customer satisfaction rating",
  "type": "department",
  "measurement_type": "percentage",
  "target_value": 85,
  "unit": "%",
  "department_id": 2,
  "position_id": null,
  "frequency": "monthly"
}
```

**Response:**
```json
{
  "success": true,
  "message": "KPI created successfully",
  "data": {
    "id": 10,
    "name": "Customer Satisfaction Score"
  }
}
```

**Validation:**
- `type`: department, position, personal
- `measurement_type`: number, percentage, currency, boolean
- `frequency`: daily, weekly, monthly, quarterly, yearly

---

### Update KPI

Update KPI definition.

**Endpoint:** `PUT /kpis/{id}`

**Permission:** Super Admin, Department Head (for own department)

**Request Body:**
```json
{
  "target_value": 90,
  "description": "Updated target to 90%"
}
```

**Response:**
```json
{
  "success": true,
  "message": "KPI updated successfully"
}
```

---

### Assign KPI to Users

Assign KPI to specific users with custom targets.

**Endpoint:** `POST /kpis/{id}/assign`

**Permission:** Super Admin, Department Head, Manager

**Request Body:**
```json
{
  "assignments": [
    {
      "user_id": 5,
      "target_value": 50000000
    },
    {
      "user_id": 6,
      "target_value": 45000000
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "KPI assigned to 2 users successfully"
}
```

---

## Data Entry

### List Data Entries

Get list of KPI data entries.

**Endpoint:** `GET /data`

**Permission:** All authenticated users (filtered by role)

**Query Parameters:**
- `kpi_id` (int): Filter by KPI
- `user_id` (int): Filter by user
- `department_id` (int): Filter by department
- `status` (string): Filter by status (draft, pending, approved, rejected)
- `date_from` (string): Filter from date (YYYY-MM-DD)
- `date_to` (string): Filter to date (YYYY-MM-DD)
- `page` (int): Page number
- `per_page` (int): Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "kpi_id": 1,
      "kpi": {
        "id": 1,
        "name": "Monthly Sales Revenue",
        "measurement_type": "currency",
        "unit": "IDR"
      },
      "user_id": 5,
      "user": {
        "id": 5,
        "full_name": "John Doe"
      },
      "period_start": "2024-01-01",
      "period_end": "2024-01-31",
      "value": 55000000,
      "target_value": 50000000,
      "achievement_percentage": 110,
      "status": "approved",
      "notes": "Exceeded target by 10%",
      "created_at": "2024-02-01 10:00:00",
      "approved_by": 2,
      "approved_at": "2024-02-01 14:00:00"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

---

### Create Data Entry

Create new KPI data entry.

**Endpoint:** `POST /data`

**Permission:** All authenticated users (for assigned KPIs)

**Request Body:**
```json
{
  "kpi_id": 1,
  "period_start": "2024-02-01",
  "period_end": "2024-02-29",
  "value": 52000000,
  "notes": "February sales data",
  "status": "pending"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Data entry created successfully",
  "data": {
    "id": 15,
    "kpi_id": 1,
    "value": 52000000,
    "status": "pending"
  }
}
```

**Validation:**
- User must have KPI assigned
- Period dates must be valid
- Value must match KPI measurement type
- Status can be 'draft' or 'pending'

---

### Update Data Entry

Update existing data entry.

**Endpoint:** `PUT /data/{id}`

**Permission:** Owner (if draft/pending), Super Admin, Department Head

**Request Body:**
```json
{
  "value": 53000000,
  "notes": "Corrected value"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Data entry updated successfully"
}
```

**Note:** Only draft and pending entries can be edited.

---

### Delete Data Entry

Delete data entry.

**Endpoint:** `DELETE /data/{id}`

**Permission:** Owner (if draft), Super Admin, Department Head

**Response:**
```json
{
  "success": true,
  "message": "Data entry deleted successfully"
}
```

---

### Bulk Create Data Entries

Create multiple data entries at once.

**Endpoint:** `POST /data/bulk`

**Permission:** All authenticated users

**Request Body:**
```json
{
  "entries": [
    {
      "kpi_id": 1,
      "period_start": "2024-02-01",
      "period_end": "2024-02-29",
      "value": 52000000
    },
    {
      "kpi_id": 2,
      "period_start": "2024-02-01",
      "period_end": "2024-02-29",
      "value": 15.5
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Created 2 data entries successfully",
  "data": {
    "created": 2,
    "failed": 0
  }
}
```

---

## Approvals

### Get Pending Approvals

Get list of data entries pending approval.

**Endpoint:** `GET /approvals/pending`

**Permission:** Super Admin, Department Head, Manager

**Query Parameters:**
- `department_id` (int): Filter by department
- `kpi_id` (int): Filter by KPI
- `page` (int): Page number
- `per_page` (int): Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 20,
      "kpi": {
        "id": 1,
        "name": "Monthly Sales Revenue"
      },
      "user": {
        "id": 5,
        "full_name": "John Doe",
        "department": "Sales"
      },
      "period_start": "2024-02-01",
      "period_end": "2024-02-29",
      "value": 52000000,
      "target_value": 50000000,
      "achievement_percentage": 104,
      "submitted_at": "2024-03-01 10:00:00"
    }
  ],
  "pagination": {
    "page": 1,
    "total": 15
  }
}
```

---

### Approve Data Entry

Approve pending data entry.

**Endpoint:** `POST /approvals/{id}/approve`

**Permission:** Super Admin, Department Head, Manager

**Request Body:**
```json
{
  "notes": "Approved - good performance"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Data entry approved successfully"
}
```

---

### Reject Data Entry

Reject pending data entry.

**Endpoint:** `POST /approvals/{id}/reject`

**Permission:** Super Admin, Department Head, Manager

**Request Body:**
```json
{
  "reason": "Incorrect data, please verify and resubmit"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Data entry rejected successfully"
}
```

---

### Bulk Approve

Approve multiple entries at once.

**Endpoint:** `POST /approvals/bulk-approve`

**Permission:** Super Admin, Department Head, Manager

**Request Body:**
```json
{
  "entry_ids": [20, 21, 22],
  "notes": "Batch approval"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Approved 3 entries successfully",
  "data": {
    "approved": 3,
    "failed": 0
  }
}
```

---

## Analytics

### Get Dashboard Overview

Get overview statistics for dashboard.

**Endpoint:** `GET /analytics/overview`

**Permission:** All authenticated users (filtered by role)

**Query Parameters:**
- `period` (string): time_period (month, quarter, year)
- `department_id` (int): Filter by department

**Response:**
```json
{
  "success": true,
  "data": {
    "total_kpis": 25,
    "active_users": 45,
    "pending_approvals": 8,
    "avg_achievement": 95.5,
    "top_performers": [
      {
        "user_id": 5,
        "full_name": "John Doe",
        "avg_achievement": 112.5,
        "department": "Sales"
      }
    ],
    "recent_trends": {
      "this_month": 98.2,
      "last_month": 95.1,
      "change": 3.1
    }
  }
}
```

---

### Get Best Performers

Get list of top performing users.

**Endpoint:** `GET /analytics/best-performers`

**Permission:** Super Admin, Department Head

**Query Parameters:**
- `department_id` (int): Filter by department
- `period_start` (string): From date
- `period_end` (string): To date
- `limit` (int, default: 10): Number of results

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "user_id": 5,
      "full_name": "John Doe",
      "department": "Sales",
      "position": "Sales Manager",
      "avg_achievement": 112.5,
      "total_kpis": 5,
      "kpis_above_target": 5
    }
  ]
}
```

---

### Get Low Performers

Get list of underperforming users.

**Endpoint:** `GET /analytics/low-performers`

**Permission:** Super Admin, Department Head

**Query Parameters:**
- `department_id` (int): Filter by department
- `period_start` (string): From date
- `period_end` (string): To date
- `threshold` (int, default: 80): Achievement threshold
- `limit` (int, default: 10): Number of results

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "user_id": 12,
      "full_name": "Jane Smith",
      "department": "Marketing",
      "avg_achievement": 72.5,
      "total_kpis": 4,
      "kpis_below_target": 3
    }
  ]
}
```

---

### Get Performance Trends

Get performance trends over time.

**Endpoint:** `GET /analytics/trends`

**Permission:** All authenticated users

**Query Parameters:**
- `kpi_id` (int): Specific KPI
- `user_id` (int): Specific user
- `department_id` (int): Department
- `period_start` (string): From date
- `period_end` (string): To date
- `group_by` (string): Group by (day, week, month, quarter)

**Response:**
```json
{
  "success": true,
  "data": {
    "periods": [
      {
        "period": "2024-01",
        "avg_achievement": 95.5,
        "total_entries": 45,
        "above_target": 30,
        "below_target": 15
      },
      {
        "period": "2024-02",
        "avg_achievement": 98.2,
        "total_entries": 48,
        "above_target": 35,
        "below_target": 13
      }
    ]
  }
}
```

---

### Get Department Comparison

Compare performance across departments.

**Endpoint:** `GET /analytics/department-comparison`

**Permission:** Super Admin

**Query Parameters:**
- `period_start` (string): From date
- `period_end` (string): To date

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "department_id": 2,
      "department_name": "Sales",
      "avg_achievement": 105.2,
      "total_kpis": 8,
      "total_users": 15,
      "data_entries": 120
    },
    {
      "department_id": 3,
      "department_name": "Marketing",
      "avg_achievement": 92.5,
      "total_kpis": 6,
      "total_users": 10,
      "data_entries": 60
    }
  ]
}
```

---

### Get AI Insights

Get AI-generated insights and recommendations.

**Endpoint:** `GET /analytics/insights`

**Permission:** Super Admin, Department Head

**Query Parameters:**
- `department_id` (int): Filter by department

**Response:**
```json
{
  "success": true,
  "data": {
    "insights": [
      {
        "type": "trend",
        "severity": "positive",
        "title": "Improving Performance",
        "description": "Sales department shows 15% improvement over last quarter",
        "affected_users": [5, 6, 7]
      },
      {
        "type": "alert",
        "severity": "warning",
        "title": "Declining KPI",
        "description": "Customer satisfaction has dropped 5% in the last month",
        "kpi_id": 3
      }
    ],
    "recommendations": [
      "Consider increasing sales targets for high performers",
      "Provide additional training for underperforming team members"
    ]
  }
}
```

---

## Reports

### Generate Department Report

Generate comprehensive department report.

**Endpoint:** `POST /reports/department`

**Permission:** Super Admin, Department Head

**Request Body:**
```json
{
  "department_id": 2,
  "period_start": "2024-01-01",
  "period_end": "2024-03-31",
  "format": "csv"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Report generated successfully",
  "data": {
    "report_id": 15,
    "download_url": "/wp-content/uploads/kpi-reports/dept-2-2024-Q1.csv",
    "expires_at": "2024-04-01 00:00:00"
  }
}
```

**Formats:** `csv`, `json`

---

### Generate Executive Report

Generate executive summary report.

**Endpoint:** `POST /reports/executive`

**Permission:** Super Admin

**Request Body:**
```json
{
  "period_start": "2024-01-01",
  "period_end": "2024-03-31",
  "include_departments": [2, 3, 4],
  "format": "csv"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Executive report generated successfully",
  "data": {
    "report_id": 16,
    "download_url": "/wp-content/uploads/kpi-reports/executive-2024-Q1.csv"
  }
}
```

---

### Generate KPI Report

Generate report for specific KPI.

**Endpoint:** `POST /reports/kpi`

**Permission:** Super Admin, Department Head, Manager

**Request Body:**
```json
{
  "kpi_id": 1,
  "period_start": "2024-01-01",
  "period_end": "2024-03-31",
  "format": "csv"
}
```

**Response:**
```json
{
  "success": true,
  "message": "KPI report generated successfully",
  "data": {
    "report_id": 17,
    "download_url": "/wp-content/uploads/kpi-reports/kpi-1-2024-Q1.csv"
  }
}
```

---

### Get Report History

Get list of previously generated reports.

**Endpoint:** `GET /reports/history`

**Permission:** Super Admin, Department Head

**Query Parameters:**
- `page` (int): Page number
- `per_page` (int): Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 15,
      "type": "department",
      "department_id": 2,
      "period_start": "2024-01-01",
      "period_end": "2024-03-31",
      "format": "csv",
      "generated_by": 1,
      "generated_at": "2024-04-01 10:00:00",
      "download_url": "/wp-content/uploads/kpi-reports/dept-2-2024-Q1.csv"
    }
  ]
}
```

---

## Notifications

### Get Notifications

Get list of notifications for current user.

**Endpoint:** `GET /notifications`

**Permission:** Authenticated users

**Query Parameters:**
- `type` (string): Filter by type
- `is_read` (bool): Filter by read status
- `page` (int): Page number
- `per_page` (int): Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "approval_request",
      "title": "New KPI Data Pending Approval",
      "message": "John Doe submitted sales data for approval",
      "data": {
        "entry_id": 20,
        "user_id": 5
      },
      "is_read": false,
      "created_at": "2024-03-01 10:00:00"
    }
  ],
  "pagination": {
    "page": 1,
    "total": 15,
    "unread_count": 5
  }
}
```

**Notification Types:**
- `approval_request`: New data entry needs approval
- `data_approved`: Your data entry was approved
- `data_rejected`: Your data entry was rejected
- `kpi_assigned`: New KPI assigned to you
- `target_alert`: Performance below target
- `achievement_alert`: Achieved target
- `system`: System notifications

---

### Mark as Read

Mark notification as read.

**Endpoint:** `POST /notifications/{id}/read`

**Permission:** Authenticated users (own notifications)

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

---

### Mark All as Read

Mark all notifications as read.

**Endpoint:** `POST /notifications/read-all`

**Permission:** Authenticated users

**Response:**
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

---

### Get Notification Preferences

Get user's notification preferences.

**Endpoint:** `GET /notifications/preferences`

**Permission:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "email_enabled": true,
    "notification_types": {
      "approval_request": {
        "email": true,
        "in_app": true
      },
      "data_approved": {
        "email": true,
        "in_app": true
      },
      "target_alert": {
        "email": false,
        "in_app": true
      }
    }
  }
}
```

---

### Update Notification Preferences

Update notification preferences.

**Endpoint:** `PUT /notifications/preferences`

**Permission:** Authenticated users

**Request Body:**
```json
{
  "email_enabled": true,
  "notification_types": {
    "approval_request": {
      "email": true,
      "in_app": true
    },
    "target_alert": {
      "email": false,
      "in_app": true
    }
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Notification preferences updated successfully"
}
```

---

## Settings

### Get Settings

Get system settings.

**Endpoint:** `GET /settings`

**Permission:** Super Admin

**Response:**
```json
{
  "success": true,
  "data": {
    "company_name": "MBD Corp",
    "company_logo": "https://example.com/logo.png",
    "primary_color": "#1565C0",
    "secondary_color": "#FF6B35",
    "data_retention_years": 3,
    "require_approval": true,
    "auto_assign_kpis": false
  }
}
```

---

### Update Settings

Update system settings.

**Endpoint:** `PUT /settings`

**Permission:** Super Admin

**Request Body:**
```json
{
  "company_name": "MBD Corporation",
  "data_retention_years": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Settings updated successfully"
}
```

---

## Error Handling

All API errors follow a consistent format:

```json
{
  "success": false,
  "code": "error_code",
  "message": "Human-readable error message",
  "data": {
    "field": "Additional error context if applicable"
  }
}
```

### Common Error Codes

**Authentication Errors (401):**
- `not_authenticated`: No valid token provided
- `invalid_token`: Token is invalid or expired
- `invalid_credentials`: Wrong username/password

**Authorization Errors (403):**
- `insufficient_permissions`: User doesn't have required permissions
- `inactive_user`: User account is inactive

**Validation Errors (400):**
- `validation_failed`: Request validation failed
- `invalid_input`: Invalid input data
- `duplicate_entry`: Duplicate record exists

**Not Found Errors (404):**
- `not_found`: Resource not found
- `user_not_found`: User doesn't exist
- `kpi_not_found`: KPI doesn't exist

**Server Errors (500):**
- `internal_error`: Unexpected server error
- `database_error`: Database operation failed

### Example Error Response

```json
{
  "success": false,
  "code": "validation_failed",
  "message": "Validation failed",
  "data": {
    "errors": {
      "email": "Email is already in use",
      "username": "Username must be at least 3 characters"
    }
  }
}
```

---

## Rate Limiting

The API implements basic rate limiting:
- **Anonymous requests:** 60 requests per hour
- **Authenticated requests:** 300 requests per hour

Rate limit headers are included in all responses:
```
X-RateLimit-Limit: 300
X-RateLimit-Remaining: 295
X-RateLimit-Reset: 1704110400
```

---

## Pagination

List endpoints support pagination with these parameters:
- `page` (int, default: 1): Current page number
- `per_page` (int, default: 20, max: 100): Items per page

Pagination info is returned in responses:
```json
{
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 150,
    "total_pages": 8
  }
}
```

---

## Date Formats

All dates should be in ISO 8601 format:
- Date: `YYYY-MM-DD` (e.g., `2024-03-01`)
- DateTime: `YYYY-MM-DD HH:MM:SS` (e.g., `2024-03-01 14:30:00`)

All timestamps are in UTC timezone.

---

## Testing

You can test the API using tools like:
- **Postman**: Import the endpoints and test
- **cURL**: Command line testing
- **JavaScript fetch/axios**: Frontend integration

Example cURL request:
```bash
# Login
curl -X POST http://yoursite.com/wp-json/kpi/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}'

# Get users (with token)
curl -X GET http://yoursite.com/wp-json/kpi/v1/users \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

---

## Changelog

**Version 1.0.0** (Initial Release)
- Complete REST API implementation
- JWT authentication
- 12 endpoint groups
- Full CRUD operations
- Role-based permissions
- Analytics and reporting
- Notification system
