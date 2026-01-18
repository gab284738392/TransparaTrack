# TransparaTrack Navigation Flow

## 1. Entry Points
- **Homepage** (`/PHP/HOMEPAGE/homepage.php`)
- **Login Page** (`/PHP/Log-in_page/login.php`)
- **Project Listing** (`/PHP/project.php`)
- **Archive** (`/PHP/archive.php`)
- **History** (`/PHP/history.php`)
- **About Us** (`/PHP/about_us.php`)

## 2. Authentication Flow

### Unauthenticated Users
```
Homepage → Login Page
Homepage → Project Listing
Homepage → Archive
Homepage → History
Homepage → About Us
Login Page → Signup Page
Login Page → Forgot Password
Forgot Password → Reset Password
```

### Authentication Process
```
Login Page → [Valid Credentials] → Admin Dashboard
Login Page → [Invalid Credentials] → Login Page (with error)
```

## 3. Main Navigation (Available on Most Pages)

All main pages have a consistent navigation bar with these options:
- **Home** → Homepage (`/PHP/HOMEPAGE/homepage.php`)
- **Projects** → Project Listing (`/PHP/project.php`)
- **Archive** → Archive Page (`/PHP/archive.php`)
- **History** → History Page (`/PHP/history.php`)
- **About Us** → About Us Page (`/PHP/about_us.php`)

## 4. Authenticated User Flow

### Admin Dashboard (`/PHP/ADMIN PROFILE/adminprofile.php`)
```
Admin Dashboard
├── Profile Management
│   ├── View Profile
│   └── Edit Profile → Profile Edit Modal
├── Project Management
│   ├── Add New Project → Add Project Modal
│   └── My Projects → Edit/Delete Project Modals
└── Navigation Bar (same as above)
```

### Project Management Flow
```
Project Listing → Project Detail Page
Project Detail Page → Back to Project Listing
Admin Dashboard → Add New Project
Admin Dashboard → Edit Existing Project
```

## 5. Legal Pages
- **Terms and Conditions** (`/PHP/Log-in_page/terms-conditions.php`)
- **Privacy Policy** (`/PHP/Log-in_page/privacy-policy.php`)

Accessible from:
- Footer on most pages
- During signup process

## 6. Session Management
```
Any Page → Logout → Login Page
Profile Edit → Update Profile → Admin Dashboard
Add Project → Submit Form → Admin Dashboard
Edit Project → Submit Form → Admin Dashboard
```

## 7. Detailed Page Flows

### Homepage Flow
```
Homepage
├── Header Navigation (to all main sections)
├── Dashboard Statistics
├── Category Breakdown
├── Department Breakdown
└── Weekly Project Counts
```

### Project Listing Flow
```
Project Listing
├── Filter Options
│   ├── Year Filter
│   ├── Budget Filter
│   ├── Status Filter
│   ├── Category Filter
│   └── Department Filter
├── Search Projects
├── Sort Options (Newest/Oldest)
├── Project Cards
│   └── View Project → Project Detail Page
└── Pagination
```

### Project Detail Flow
```
Project Detail Page
├── Project Information
│   ├── Title
│   ├── Date
│   ├── Status
│   ├── Budget
│   ├── Category
│   ├── Department
│   └── Author
├── Description
├── Image Gallery
├── Attachments
└── Back to Projects
```

### Archive Flow
```
Archive Page
├── Year-based Project Groups
│   └── View Completed Projects (for specific year)
└── No Projects Message (if no completed projects)
```

### History Flow
```
History Page
├── Project Action History
│   ├── Action Type
│   ├── Project Name
│   ├── User
│   ├── Date/Time
│   └── Details
└── Pagination
```

### About Us Flow
```
About Us Page
├── Introduction
├── Vision
├── Mission
├── Core Values
└── Our Commitment
```

## 8. Footer Navigation (Available on Most Pages)

### First Column
- Home
- Projects
- Archive
- History
- About Us

### Second Column
- Profile (authenticated users only)
- Terms and Conditions
- Privacy Policy
- Report a Problem
- Log out (authenticated users only)

## 9. Modal Flows

### Profile Editing
```
Admin Dashboard → Edit Profile Button → Profile Edit Modal
Profile Edit Modal → Save Changes → Admin Dashboard
Profile Edit Modal → Cancel → Admin Dashboard
```

### Project Management
```
Admin Dashboard → Add Project Button → Add Project Modal
Add Project Modal → Submit → Admin Dashboard
Add Project Modal → Cancel → Admin Dashboard

Admin Dashboard → Edit Project → Edit Project Modal
Edit Project Modal → Save Changes → Admin Dashboard
Edit Project Modal → Cancel → Admin Dashboard
```