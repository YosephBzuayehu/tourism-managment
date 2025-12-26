# Restrict Frontend Menu by Role (TOUR-20)

## Goal
Hide or disable UI menu items and pages depending on the logged-in user role.

## Proposed Changes

### Frontend (React)
#### [NEW] `src/components/Navbar.jsx`
- Access `user` from `useAuth()`.
- Conditional rendering:
    - Guests: Login/Register
    - Authenticated: Dashboard, Logout
    - Admin: Admin Panel link
    - Guide: My Tours link

#### [MODIFY] `src/App.jsx`
- Included `Navbar` at the top of the app structure.

## Verification Plan
### Manual Verification
- Login as 'Tourist' -> Should NOT see "Admin Panel".
- Login as 'Admin' -> Should see "Admin Panel".
