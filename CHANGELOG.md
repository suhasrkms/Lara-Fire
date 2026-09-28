# Changelog

All notable changes to Lara Fire are listed here.

## [2.0.0] - 2026-09-27

### Added
- **Laravel 13** with the slim application structure (`bootstrap/app.php`) and PHP 8.3+.
- **Social login** with Google and GitHub through the Firebase JS SDK v12. The server verifies the ID token.
- **Cloud Firestore Notes**: per-user CRUD over the Firestore REST API. No `ext-grpc` is needed, so it works on Windows.
- **Push notifications (FCM)**: users opt in per browser, and admins can broadcast or target one user.
- **REST API** `/api/v1` secured with Firebase ID tokens (`me`, `notes`).
- Admin panel: search, stats, provider breakdown, grant/revoke admin, send password reset, enable/disable.
- `php artisan larafire:make-admin {email}` and `larafire:about` commands.
- Dark mode, a new Bootstrap 5.3 UI built with Vite 8 (no more CDN/jQuery mix).
- Test suite with mocked Firebase, and GitHub Actions CI.

### Changed
- kreait/laravel-firebase 5 → 7 (firebase-php 8).
- Auth now uses a real Laravel guard (`FirebaseUserProvider`) with short-lived user caching.
- The service account path moved to `storage/app/firebase/` (git-ignored).

### Security
- Removed `/home/iamadmin`, which let **any** user make themselves admin.
- Login no longer resets the `admin` claim, and other custom claims are preserved.
- Profile update/disable now only acts on the signed-in user (it used to trust a UID from the URL).
- Rate limiting on login, register, password reset and the API.

### Removed
- `laravel/ui`, `laravel/sanctum`, `spatie/laravel-html` and `google/cloud-firestore`, along with the gRPC dependency.

## [1.x]

### Added
- Email/password auth with Firebase, email verification, password reset.
- Basic admin panel with user list, create, edit and disable.
