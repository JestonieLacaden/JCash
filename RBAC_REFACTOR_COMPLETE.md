# RBAC Refactor Complete - 3-Role System

## Overview

This document summarizes the complete refactor of the Role-Based Access Control (RBAC) system to properly distinguish between three distinct roles:

1. **Admin** - Full access to all features
2. **Staff** - Encoder role (can create cash_in and cash_out transactions only)
3. **Auditor** - Read-only observer (cannot create or modify any data)

## Key Changes

### 1. Database Migration

**File:** `database/migrations/2026_01_07_142242_update_user_roles_add_auditor.php`

- Documentation-only migration
- Added 'auditor' as third valid role option
- Updated role constraints from admin/staff to admin/staff/auditor

### 2. Middleware Shared Props

**File:** `app/Http/Middleware/HandleInertiaRequests.php`

- **Before:** `'isAuditMode' => $user ? $user->role !== 'admin' : false`
- **After:**
    ```php
    'isReadOnly' => $user ? $user->role === 'auditor' : false,
    'userRole' => $user?->role,
    ```
- Changed from `isAuditMode` (non-admin = read-only) to `isReadOnly` (auditor only)
- Added `userRole` prop for granular role checking in components

### 3. Route Structure

**File:** `routes/web.php`
Routes are now organized into 3 logical groups:

#### READ-ONLY Routes (All authenticated users)

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions/history', [TransactionController::class, 'history'])->name('transactions.history');
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
});
```

#### STAFF Routes (Admin + Staff - Encoder access)

```php
Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/sessions/start', [SessionController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/end', [SessionController::class, 'end'])->name('sessions.end');
});
```

#### ADMIN-ONLY Routes (Full control)

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/transactions/adjustment', [TransactionController::class, 'storeAdjustment'])->name('transactions.adjustment.store');
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::resource('users', UserManagementController::class);
    Route::resource('fee-settings', FeeSettingController::class);
    Route::resource('gcash-accounts', GcashAccountController::class);
});
```

### 4. Controller Validation

**File:** `app/Http/Controllers/UserManagementController.php`

- Updated `store()` method validation: `'role' => ['required', 'in:admin,staff,auditor']`
- Updated `update()` method validation: `'role' => ['required', 'in:admin,staff,auditor']`

### 5. Composable

**File:** `resources/js/composables/useAuth.js`

```javascript
export function useAuth() {
    const page = usePage();
    const userRole = computed(() => page.props.userRole);
    const isReadOnly = computed(() => page.props.isReadOnly);

    const isAdmin = computed(() => userRole.value === 'admin');
    const isStaff = computed(() => userRole.value === 'staff');
    const isAuditor = computed(() => userRole.value === 'auditor');
    const user = computed(() => page.props.auth.user);

    return {
        isAdmin,
        isStaff,
        isAuditor,
        isReadOnly,
        userRole,
        user,
    };
}
```

### 6. Layout Navigation

**File:** `resources/js/layouts/AuthenticatedLayout.vue`

#### Role Badges

- **Admin:** No badge
- **Staff:** `📝 ENCODER` badge
- **Auditor:** `📖 READ-ONLY` badge

#### Menu Structure

**All roles see:**

- Dashboard
- Transaction History

**Staff + Admin see:**

- Cash In/Out (for creating transactions)

**Admin only sees:**

- Funds Adjustment
- Fee Settings
- GCash Accounts
- User Management
- Export

### 7. User Management Forms

**Files:**

- `resources/js/Pages/Admin/Users/Create.vue`
- `resources/js/Pages/Admin/Users/Edit.vue`

Updated role dropdown options:

```vue
<select v-model="form.role">
    <option value="staff">📝 Staff (Encoder - Cash In/Out only)</option>
    <option value="auditor">📖 Auditor (Read-Only Mode)</option>
    <option value="admin">👑 Admin (Full Control)</option>
</select>
```

Help text:

> Staff can create cash_in/out. Auditor is read-only. Admin has full access.

### 8. Example Component

**File:** `resources/js/components/AuditModeExample.vue`

- Updated from `isAuditMode` to `isReadOnly`
- Updated comments to reference auditors instead of staff
- Badge changed from "🔍 AUDIT MODE" to "📖 READ-ONLY MODE"

## Role Permissions Summary

| Feature                  | Admin | Staff | Auditor |
| ------------------------ | ----- | ----- | ------- |
| View Dashboard           | ✅    | ✅    | ✅      |
| View Transaction History | ✅    | ✅    | ✅      |
| View Reports             | ✅    | ✅    | ✅      |
| Create Cash In/Out       | ✅    | ✅    | ❌      |
| Start/End Sessions       | ✅    | ✅    | ❌      |
| Create Adjustments       | ✅    | ❌    | ❌      |
| Export Data              | ✅    | ❌    | ❌      |
| Manage Fee Settings      | ✅    | ❌    | ❌      |
| Manage GCash Accounts    | ✅    | ❌    | ❌      |
| Manage Users             | ✅    | ❌    | ❌      |

## Testing Checklist

### Test Users to Create

```
Admin:
- email: admin@test.com
- password: password
- role: admin

Staff:
- email: staff@test.com
- password: password
- role: staff

Auditor:
- email: auditor@test.com
- password: password
- role: auditor
```

### Test Scenarios

#### Admin Tests

- [ ] Can access all menu items
- [ ] Can create cash_in/cash_out transactions
- [ ] Can create adjustments
- [ ] Can export data
- [ ] Can manage users, fee settings, GCash accounts
- [ ] No role badge displayed

#### Staff Tests

- [ ] Sees Dashboard, History, Cash In/Out menu items
- [ ] Can create cash_in/cash_out transactions
- [ ] Can start/end daily sessions
- [ ] Cannot access adjustment page (should get 403)
- [ ] Cannot access user management (should get 403)
- [ ] Cannot access exports (should get 403)
- [ ] Shows "📝 ENCODER" badge

#### Auditor Tests

- [ ] Sees only Dashboard and History menu items
- [ ] Cannot see Cash In/Out menu item
- [ ] Can view all read-only data
- [ ] Cannot access transaction creation (should get 403)
- [ ] Cannot access any POST/PUT/DELETE routes (should get 403)
- [ ] Shows "📖 READ-ONLY" badge
- [ ] All buttons and inputs should be disabled (if applicable)

## Next Steps

1. **Run Migration**

    ```bash
    php artisan migrate
    ```

2. **Create Test Users**

    ```bash
    php artisan tinker
    ```

    ```php
    User::create(['name' => 'Admin User', 'email' => 'admin@test.com', 'password' => bcrypt('password'), 'role' => 'admin']);
    User::create(['name' => 'Staff User', 'email' => 'staff@test.com', 'password' => bcrypt('password'), 'role' => 'staff']);
    User::create(['name' => 'Auditor User', 'email' => 'auditor@test.com', 'password' => bcrypt('password'), 'role' => 'auditor']);
    ```

3. **Build Frontend Assets**

    ```bash
    npm run build
    ```

4. **Test Each Role**
    - Login as each user type
    - Verify menu items display correctly
    - Test accessible routes return 200
    - Test restricted routes return 403
    - Verify badges display correctly

## Files Modified

### Backend

1. `database/migrations/2026_01_07_142242_update_user_roles_add_auditor.php`
2. `app/Http/Middleware/HandleInertiaRequests.php`
3. `app/Http/Controllers/UserManagementController.php`
4. `routes/web.php`

### Frontend

5. `resources/js/composables/useAuth.js`
6. `resources/js/layouts/AuthenticatedLayout.vue`
7. `resources/js/Pages/Admin/Users/Create.vue`
8. `resources/js/Pages/Admin/Users/Edit.vue`
9. `resources/js/components/AuditModeExample.vue`

## Migration from Old System

If you have existing users with role = 'staff' who should remain as staff (encoders), no changes are needed. They will continue to work as staff encoders.

If you have users who should be auditors (read-only), update their role:

```php
User::where('id', $userId)->update(['role' => 'auditor']);
```

## Security Notes

- All routes are protected at the middleware level
- Frontend menu hiding is for UX only - security is enforced on backend
- Always verify `role:admin` or `role:admin,staff` middleware is applied to sensitive routes
- RoleMiddleware accepts multiple roles for flexibility

---

**Refactor Date:** January 7, 2026
**Status:** ✅ Complete
**Tested:** ⚠️ Pending manual testing with actual users
