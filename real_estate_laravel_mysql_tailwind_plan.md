# Real Estate Marketplace — Laravel + MySQL + Tailwind CSS

## 1. Project Overview

Build a full real-estate marketplace using:

- **Laravel PHP** for the backend
- **MySQL 8+** for the database
- **Blade** for server-rendered frontend views
- **Pure Tailwind CSS** for all UI styling
- Laravel session authentication instead of NextAuth/JWT
- Eloquent ORM for database access
- Laravel Policies/Gates and middleware for authorization

The platform supports three main roles:

- **Buyer** — search listings, post property requirements, and view matches.
- **Seller** — create and manage property listings and view buyer matches.
- **Admin** — manage and monitor users, listings, requirements, and matches.

---

# 2. Core User Roles

## Buyer

Buyers can:

- Register an account
- Login using email or phone
- Browse published properties
- Search and filter listings
- View property details
- Post property requirements
- View matched properties
- View match scores
- Manage their profile

## Seller

Sellers can:

- Register an account
- Login using email or phone
- Create property listings
- Save listings as drafts
- Submit listings for verification
- Manage existing listings
- View listing status
- View buyer requirements/matches
- Manage their profile

## Admin

Admins can:

- View dashboard statistics
- View users
- View listings
- View buyer requirements
- View matches
- Review listing status
- Monitor recent users and listings
- Access elevated management functionality

---

# 3. Authentication & Registration

## Registration

Registration is public and does not require an existing session.

Required fields:

- Name
- Email
- Phone number
- Password
- Password confirmation
- Role

Phone number requirements:

- Mandatory
- Minimum 10 digits
- Should be validated and normalized before storage

Available public registration roles:

- Buyer
- Seller

Staff and Admin accounts must NOT be publicly selectable during registration.

Staff accounts should be created by Admin.

Admin accounts should be created through a seeder or controlled administrative process.

Staff should have a separate authenticated staff/admin interface for creating Buyer and Seller accounts.

## Login

Users can login using either:

- Email + password
- Phone + password

Use Laravel's native session-based authentication.

### Authentication architecture

Do NOT use:

- NextAuth
- NextAuth v5
- JWT authentication for normal web sessions

Use:

- Laravel authentication
- Secure server-side sessions
- Authentication middleware
- CSRF protection
- Password hashing

---

# 4. Authorization

Use role-based middleware and Laravel Policies.

Example roles:

```text
BUYER
SELLER
ADMIN
```

Authorization should ensure:

- Buyers cannot manage another user's requirements.
- Sellers cannot edit another seller's listings.
- Users cannot access admin pages unless authorized.
- Admins can access administrative functionality.
- Sellers can only manage their own listings.

Recommended approach:

```text
Authentication
    ↓
Role Middleware
    ↓
Policy / Ownership Check
    ↓
Controller Action
```

---

# 5. Property Listings

Sellers can create property listings.

## Property Fields

Each property should support:

- Title
- Description
- Price
- Property type
- BHK
- Area in square feet
- City
- Area/locality
- Possession status
- Listing status
- Views
- Seller/user ID
- Created date
- Updated date

## Property Types

Support:

```text
Flat
Apartment
Villa
Plot
Building
Commercial Space
Land
```

## Listing Status

Use the following workflow:

```text
DRAFT
   ↓
PENDING_VERIFICATION
   ↓
PUBLISHED
   ↓
SOLD
EXPIRED
REMOVED
```

Only `PUBLISHED` listings should appear on the public property grid.

---

# 6. Property Images

Create a separate `property_images` table.

Each property can have multiple images.

Fields:

- ID
- Property ID
- Image URL/path
- Sort order
- Created date
- Updated date

Initially, Unsplash image URLs can be used for demonstration/homepage content.

The architecture should allow local/cloud image uploads to be added later.

---

# 7. Public Property Search

The public website should display published properties.

Users should be able to filter by:

- City
- Property type
- Search keyword

Optional filters:

- Minimum price
- Maximum price
- BHK
- Area
- Possession status

Search should use Eloquent query scopes or a dedicated query/service layer.

Only properties with:

```text
status = PUBLISHED
```

should appear in public search results.

---

# 8. Property Detail Page

Each property detail page should show:

- Property title
- Property images
- Price
- Property type
- BHK
- Area
- City
- Locality/area
- Possession status
- Listing status
- Number of views
- Seller information where appropriate
- Description

Every valid property view should increment the `views` counter.

---

# 9. Buyer Requirements

Buyers can create property requirements.

## Requirement Fields

- Property type
- BHK
- Budget minimum
- Budget maximum
- Possession status
- City
- Description
- Buyer/user ID
- Status
- Created date
- Updated date

## Requirement Status

```text
ACTIVE
MATCHED
CLOSED
EXPIRED
```

Only active requirements should normally participate in new matching operations.

---

# 10. Matching System

Build a dedicated Laravel service:

```text
app/Services/MatchingService.php
```

The matching service compares buyer requirements against published property listings.

## Matching Criteria

Recommended scoring:

| Criterion | Score |
|---|---:|
| City | 25 |
| Property Type | 20 |
| BHK | 15 |
| Budget | 25 |
| Possession Status | 15 |
| **Total** | **100** |

The exact scoring rules should be implemented in one service so they can be changed later without modifying controllers.

Example:

```text
Buyer Requirement
        ↓
MatchingService
        ↓
Compare against published listings
        ↓
Calculate score
        ↓
Store matches
        ↓
Sort by highest score
```

Example result:

```text
Property A → 95%
Property B → 88%
Property C → 76%
```

The score should be stored numerically in the `matches` table.

---

# 11. Match Database

Create:

```text
matches
```

Recommended fields:

- ID
- Requirement ID
- Property ID
- Score
- Created date
- Updated date

Add a unique constraint so the same requirement/property pair is not duplicated unnecessarily.

Recommended relationship:

```text
Requirement
    hasMany Matches

Property
    hasMany Matches

Match
    belongsTo Requirement
    belongsTo Property
```

---

# 12. Buyer Dashboard

After login, buyers should see a dashboard containing:

- Total active requirements
- New matches
- Quick link to post requirement
- Recent requirements
- Top property matches

Example:

```text
Dashboard

Active Requirements     3
New Matches             12

[Post Requirement]

Recent Requirements

Requirement #1
2 BHK | Guwahati | ₹40L–₹60L

Top Matches

Property A     95%
Property B     89%
Property C     82%
```

---

# 13. Seller Dashboard

Sellers should see:

- Total listings
- Published listings
- Pending listings
- Sold listings
- New buyer matches
- Quick link to create listing

Example:

```text
Dashboard

Total Listings          12
Published                7
Pending Verification    3
Sold                     2

[New Listing]

Recent Listings
```

---

# 14. General Dashboard

Post-login dashboard should provide:

- Dashboard
- Listings
- Requirements
- Profile
- Admin (only when authorized)

The sidebar must be role-aware.

### Buyer

```text
Dashboard
Requirements
Matches
Profile
```

### Seller

```text
Dashboard
Listings
Buyer Matches
Profile
```

### Admin

```text
Dashboard
Users
Listings
Requirements
Matches
Profile
```

---

# 15. Admin Panel

The admin dashboard should display:

- Total users
- Total listings
- Active listings
- Total matches
- Active requirements
- Total buyers
- Total sellers
- Total staff
- Pending verification listings
- Recent matching activity

## Recent Users

Show:

- Name
- Email
- Role
- Joined date

## Recent Listings

Show:

- Title
- Price
- City
- Status
- Created date

## Admin Match Management

Admin must be able to view matched properties and requirements.

The Admin Match section should provide:

- Buyer requirement
- Buyer name
- Seller name
- Matched property
- Property title
- Property price
- City
- Property type
- BHK
- Match score
- Match status
- Match created date

Admin should be able to:

- Search matches
- Filter by city
- Filter by score
- Filter by property type
- Filter by match status
- Open the requirement
- Open the matched property
- View Buyer details
- View Seller details

Example:

```text
Requirement: 2 BHK in Guwahati
Buyer: John

Matched Properties

Property A       95%
Property B       89%
Property C       82%
```

The admin should be able to see **all matches across the platform**, subject to normal authorization rules.

The admin panel should be protected by role middleware.

---

# 16. Public Homepage

The homepage should have a modern real-estate marketplace design inspired by major property portals.

Use:

- Blade
- Tailwind CSS
- Responsive layouts
- Semantic HTML
- No Bootstrap
- No separate CSS framework

## Header

Sticky header containing:

- Logo
- Home
- Properties
- About
- Contact
- Login
- Register

When authenticated:

- Dashboard
- Logout

---

# 17. Hero Section

The hero should contain:

- Large Unsplash background image
- Gradient overlay
- Main headline
- Supporting text
- Property search bar
- Search filters
- Platform statistics

Example:

```text
Find a place you'll love.

Search thousands of properties
across cities and localities.

[ City ] [ Property Type ] [ Search ]

10,000+ Properties
5,000+ Buyers
2,000+ Sellers
```

---

# 18. Cities Section

Create a responsive city grid.

Each city card should contain:

- Unsplash image
- City name
- Optional property count

Example:

```text
Popular Cities

[ Guwahati ]
[ Delhi ]
[ Mumbai ]
[ Bangalore ]
[ Kolkata ]
[ Hyderabad ]
```

City data should ideally come from the database rather than being hard-coded into Blade templates.

---

# 19. Property Listing Grid

Homepage should display published properties in a responsive grid.

Desktop:

```text
4 columns
```

Tablet:

```text
2 columns
```

Mobile:

```text
1 column
```

Each property card should show:

- Property image
- Property type
- Title
- Price
- BHK
- Area
- City
- Locality
- View details button

---

# 20. How It Works

Create a three or four-step section.

Example:

```text
01
Search Properties

02
Post Your Requirement

03
Get Matched

04
Connect
```

Use Tailwind cards/icons and responsive layout.

---

# 21. CTA Section

Create a strong CTA banner.

Example actions:

```text
Looking to buy?

Post your requirement and
find properties that match.

[Post Requirement]
```

For sellers:

```text
Have a property to sell?

List your property and reach
potential buyers.

[List Property]
```

---

# 22. Footer

Footer should contain:

- Logo
- About
- Property links
- Buyer links
- Seller links
- Contact information
- Social links
- Copyright

---

# 23. MySQL Database Design

## users

```text
id
name
email
phone
password
role
created_by_user_id
created_via_staff
created_at
updated_at
```

Recommended indexes:

```text
email
phone
role
```

Email and phone should be unique.

---

## properties

```text
id
user_id
created_by_user_id
title
description
price
property_type
bhk
area_sqft
city
area
possession_status
status
views
created_at
updated_at
```

Indexes:

```text
user_id
city
property_type
status
price
```

---

## property_images

```text
id
property_id
image_url
sort_order
created_at
updated_at
```

Index:

```text
property_id
```

---

## requirements

```text
id
user_id
created_by_user_id
property_type
bhk
budget_min
budget_max
possession_status
city
description
status
created_at
updated_at
```

Indexes:

```text
user_id
city
property_type
status
budget_min
budget_max
```

---

## matches

```text
id
requirement_id
property_id
score
created_at
updated_at
```

Indexes:

```text
requirement_id
property_id
score
```

Unique constraint:

```text
requirement_id + property_id
```

---

## cities

```text
id
name
image_url
created_at
updated_at
```

---

## activity_logs

Optional but recommended:

```text
id
user_id
action
entity_type
entity_id
metadata
created_at
updated_at
```

---

# 24. Eloquent Relationships

## User

```text
User
 ├── hasMany Properties
 └── hasMany Requirements
```

## Property

```text
Property
 ├── belongsTo User
 ├── hasMany PropertyImages
 └── hasMany Matches
```

## Requirement

```text
Requirement
 ├── belongsTo User
 └── hasMany Matches
```

## Match

```text
Match
 ├── belongsTo Requirement
 └── belongsTo Property
```

---

# 25. Recommended Laravel Controllers

```text
Auth/
    RegisteredUserController
    AuthenticatedSessionController

HomeController

DashboardController

PropertyController
PropertyImageController

RequirementController

MatchController

ProfileController

Admin/
    DashboardController
    UserController
    PropertyController
    RequirementController
    MatchController
```

Use resource controllers where appropriate.

---

# 26. Recommended Services

Keep business logic out of controllers where possible.

```text
app/Services/

MatchingService.php
PropertySearchService.php
PropertyViewService.php
```

The most important service is:

```text
MatchingService.php
```

It should:

1. Receive a requirement.
2. Find suitable published properties.
3. Compare matching criteria.
4. Calculate score.
5. Save/update match records.
6. Return matches ordered by score.

---

# 27. Middleware

Recommended middleware:

```text
auth
guest
role
```

Example route protection:

```text
/dashboard
/listings
/requirements
/profile
/admin
```

Admin routes should require:

```text
auth + admin
```

---

# 28. Routes

Public routes:

```text
/
 /properties
 /properties/{property}
 /login
 /register
```

Authenticated routes:

```text
/dashboard
/profile
/listings
/listings/create
/listings/{property}/edit
/requirements
/requirements/create
/matches
```

Admin routes:

```text
/admin
/admin/users
/admin/staff
/admin/properties
/admin/requirements
/admin/matches
```

Staff routes:

```text
/staff
/staff/users
/staff/users/create
/staff/users/{user}
/staff/properties
/staff/properties/create
/staff/requirements
/staff/requirements/create
/staff/matches
```

Staff routes must be protected by:

```text
auth + staff
```

Admin routes must be protected by:

```text
auth + admin
```

---

# 29. Tailwind CSS Requirements

The entire frontend should use Tailwind CSS.

Do not use:

- Bootstrap
- Material UI
- Bulma
- Foundation
- Custom component libraries unless specifically required

Use Tailwind utilities for:

- Layout
- Typography
- Colors
- Spacing
- Cards
- Forms
- Buttons
- Navigation
- Responsive behavior
- Modals
- Tables
- Badges
- Alerts

The design should be:

- Modern
- Clean
- Professional
- Mobile responsive
- Real-estate focused
- Fast loading

---

# 30. UI Design Direction

Visual style:

```text
Premium real-estate marketplace
Clean white backgrounds
Strong typography
Large property photography
Rounded cards
Subtle shadows
Clear CTA buttons
Professional dashboard
Responsive navigation
```

The homepage should feel similar in usability to major Indian property portals while maintaining an original visual design.

---

# 31. Search Architecture

Use Laravel/Eloquent query building.

Search should support:

```text
keyword
city
property_type
bhk
min_price
max_price
possession_status
```

Only published properties should be returned publicly.

Use pagination:

```text
paginate(12)
```

or an appropriate page size.

---

# 32. Staff Management

Admin should have a dedicated Staff Management section.

Admin can:

- Create Staff accounts
- Activate/deactivate Staff accounts
- Reset Staff passwords
- View Staff members
- View Staff-created Buyers
- View Staff-created Sellers
- View properties created by Staff
- View requirements created by Staff
- Review Staff activity

Recommended staff table:

```text
staff profile can use users table
role = STAFF
```

Do not create a separate authentication system for Staff. Staff should use the same Laravel authentication system with role-based authorization.

## Staff Activity / Audit Trail

Track important Staff actions in `activity_logs`.

Examples:

```text
STAFF_CREATED_BUYER
STAFF_CREATED_SELLER
STAFF_CREATED_PROPERTY
STAFF_CREATED_REQUIREMENT
STAFF_UPDATED_PROPERTY
STAFF_UPDATED_REQUIREMENT
```

This makes it possible for Admin to identify who performed an operational action.

---

# 33. Security Requirements

Implement:

- CSRF protection
- Password hashing
- Authentication middleware
- Authorization policies
- Input validation
- Mass-assignment protection
- SQL injection protection through Eloquent/query builder
- XSS-safe Blade output
- Rate limiting for authentication
- Secure session cookies
- Authorization checks on every protected resource
- File upload validation if image uploads are added

Never trust IDs supplied by the browser without verifying ownership/permissions.

---

# 34. Validation

Use Laravel Form Requests.

Recommended:

```text
StorePropertyRequest
UpdatePropertyRequest
StoreRequirementRequest
UpdateRequirementRequest
RegisterUserRequest
UpdateProfileRequest
```

Validation should happen before business logic.

---

# 35. Database Migrations

All database tables should be created through Laravel migrations.

Do not manually depend on a pre-created production database.

Recommended workflow:

```text
php artisan migrate
php artisan db:seed
```

Factories and seeders should create development/demo data.

---

# 36. Seed Data

Create seeders for:

- Admin
- Demo buyers
- Demo sellers
- Cities
- Properties
- Property images
- Requirements
- Matches

The application should be usable immediately after:

```text
php artisan migrate --seed
```

---

# 37. Environment Configuration

Use `.env`.

Important variables:

```text
APP_NAME=
APP_ENV=
APP_KEY=
APP_URL=

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Never commit real production credentials.

---

# 38. Recommended Project Stack

```text
Laravel
PHP 8.2+
MySQL 8+
Blade
Tailwind CSS
Vite
Eloquent ORM
Laravel Authentication
Laravel Policies
Laravel Middleware
```

Optional:

```text
Alpine.js
```

Alpine.js may be used only where lightweight client-side interactions are required, such as dropdowns, mobile menus, modals, and filters. The primary UI should remain Blade + Tailwind.

---

# 39. Suggested Development Order

Build in this order:

```text
1. Laravel project setup
2. MySQL configuration
3. Authentication
4. User roles: Buyer, Seller, Staff, Admin
5. Database migrations
6. Models and relationships
7. Role middleware and policies
8. Admin Staff management
9. Staff Buyer/Seller account creation
10. Seller listing CRUD
11. Staff-created property workflow
12. Buyer requirement CRUD
13. Staff-created requirement workflow
14. MatchingService
15. Buyer match dashboard
16. Seller match dashboard
17. Admin match management
18. Public property search
19. Property detail page
20. Main homepage
21. Buyer dashboard
22. Seller dashboard
23. Admin dashboard
24. Staff dashboard
25. Audit/activity logging
26. Validation/security hardening
27. Seed/demo data
28. Responsive UI testing
29. Production deployment
```

---

# 40. Final Architecture

```text
                    PUBLIC WEBSITE
                         │
                         ▼
                  Laravel Routes
                         │
             ┌───────────┴───────────┐
             ▼                       ▼
        Blade + Tailwind        Authentication
                                     │
                      ┌──────────────┼──────────────┐
                      ▼              ▼              ▼
                    BUYER          SELLER         ADMIN
                      │              │              │
                      ▼              ▼              ▼
               Requirements       Listings       Admin Panel
                      │              │              │
                      └───────┬──────┘              │
                              ▼                     │
                       MatchingService              │
                              │                     │
                              ▼                     │
                         Matches ◄──────────────────┘
                              │
                              ▼
                            MySQL
```

---

# 41. Important Migration From Original Stack

The original specification mentions:

```text
Next.js
NextAuth v5
PostgreSQL
JWT strategy
```

For this implementation, replace them with:

```text
Laravel
Laravel Authentication
MySQL
Laravel Sessions
Eloquent ORM
Blade
Tailwind CSS
```

The business functionality remains the same.

The main architectural difference is authentication:

```text
OLD

Next.js
   ↓
NextAuth v5
   ↓
JWT
   ↓
PostgreSQL


NEW

Laravel
   ↓
Laravel Auth
   ↓
Server-side Session
   ↓
MySQL
```

This Laravel architecture is simpler and more natural for a traditional PHP/MySQL web application.
