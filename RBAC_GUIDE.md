# 🔒 JCash Role-Based Access Control (RBAC) System

## Overview

Ang system ay may 2 roles:

- **Admin** - Full access (read & write)
- **Staff** - Read-only access (audit mode)

---

## ✅ Implementation Checklist

### ✅ Backend (Laravel)

1. **Database** - ✅ May `role` column na sa users table
    - Default: `staff`
    - Values: `admin`, `staff`

2. **User Model** - ✅ Updated

    ```php
    protected $fillable = ['name', 'email', 'password', 'username', 'role'];

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isStaff(): bool { return $this->role === 'staff'; }
    ```

3. **Middleware** - ✅ Created `AdminOnly`
    - File: `app/Http/Middleware/AdminOnly.php`
    - Blocks staff from write operations
    - Returns 403 error kung staff

4. **Middleware Registration** - ✅ Registered sa `bootstrap/app.php`

    ```php
    $middleware->alias(['admin' => \App\Http\Middleware\AdminOnly::class]);
    ```

5. **Routes** - ✅ Protected with `admin` middleware
    - **READ-ONLY** (accessible to all authenticated users):
        - GET routes (viewing pages)
    - **ADMIN-ONLY** (protected):
        - POST routes (create/store)
        - PUT/PATCH routes (update)
        - DELETE routes
        - Export routes

6. **Shared Props** - ✅ Global `isAuditMode` via Inertia
    - File: `app/Http/Middleware/HandleInertiaRequests.php`
    - Available sa lahat ng Vue components

---

### ✅ Frontend (Vue)

1. **Composable** - ✅ Created `useAuth()`
    - File: `resources/js/composables/useAuth.js`
    - Returns: `isAuditMode`, `isAdmin`, `isStaff`, `user`

2. **Layout** - ✅ Shows AUDIT MODE badge sa header
    - File: `resources/js/layouts/AuthenticatedLayout.vue`

3. **Usage Examples** - ✅ Created example component
    - File: `resources/js/components/AuditModeExample.vue`

---

## 📖 Usage Guide

### Option 1: Direct access via $page.props (Simple)

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { props } = usePage();
const isAuditMode = computed(() => props.isAuditMode);
</script>
```

### Option 2: Using composable (Recommended)

```vue
<script setup>
import { useAuth } from '@/composables/useAuth';

const { isAuditMode, isAdmin, isStaff, user } = useAuth();
</script>
```

---

## 🎨 UI Patterns

### Hide elements for staff

```vue
<div v-if="!isAuditMode">
    <a :href="route('exports.transactions')" class="btn-green">
        Export CSV
    </a>
</div>
```

### Disable inputs for staff

```vue
<input :disabled="isAuditMode" class="input" placeholder="Amount" />
```

### Disable buttons for staff

```vue
<button
    :disabled="isAuditMode"
    class="btn-primary disabled:cursor-not-allowed disabled:opacity-50"
>
    Save Changes
</button>
```

### Show badge for staff

```vue
<span
    v-if="isAuditMode"
    class="rounded bg-yellow-100 px-2 py-1 text-xs text-yellow-800"
>
    🔍 AUDIT MODE
</span>
```

---

## 🔐 Protected Routes

### Read-Only Routes (All authenticated users)

- ✅ `/transactions/create` - View transaction form
- ✅ `/transactions/history` - View transaction history
- ✅ `/transactions/adjustment` - View adjustment form
- ✅ `/settings/fees` - View fee settings
- ✅ `/settings/gcash` - View GCash accounts
- ✅ `/reports/daily` - View daily reports

### Admin-Only Routes

- 🔒 `POST /transactions` - Create transaction
- 🔒 `POST /transactions/adjustment` - Create adjustment
- 🔒 `POST /daily-session/start` - Start new day
- 🔒 `POST /daily-session/continue` - Continue yesterday
- 🔒 `PUT /settings/fees` - Update fee settings
- 🔒 `POST /settings/gcash` - Add GCash account
- 🔒 `PUT /settings/gcash/{id}` - Update GCash account
- 🔒 `GET /exports/*` - Export data
- 🔒 `POST /pin/verify` - Verify PIN

---

## 🧪 Testing

### Test as Admin

1. Login with admin account
2. ❌ No "AUDIT MODE" badge
3. ✅ Can save/edit/delete
4. ✅ Can export data
5. ✅ All buttons enabled

### Test as Staff

1. Login with staff account
2. ✅ Shows "🔍 AUDIT MODE" badge
3. ✅ Can view all pages
4. ❌ Cannot save/edit/delete (buttons disabled)
5. ❌ Cannot export data (403 error)
6. ❌ POST requests blocked by middleware

---

## 🛠️ How to Create Users

### Via Tinker

```php
php artisan tinker

// Create Admin
User::create([
    'name' => 'Admin User',
    'username' => 'admin',
    'email' => 'admin@jcash.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
]);

// Create Staff
User::create([
    'name' => 'Staff User',
    'username' => 'staff',
    'email' => 'staff@jcash.com',
    'password' => bcrypt('password'),
    'role' => 'staff',
]);
```

### Via Seeder

Create `database/seeders/UserRoleSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@jcash.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Staff
        User::firstOrCreate(
            ['email' => 'staff@jcash.com'],
            [
                'name' => 'Staff',
                'username' => 'staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );
    }
}
```

Run: `php artisan db:seed --class=UserRoleSeeder`

---

## 🚨 Security Notes

1. ✅ **Backend Protection** - Middleware blocks unauthorized requests
2. ✅ **Frontend UX** - Disabled buttons provide visual feedback
3. ✅ **Double Protection** - Both frontend (UX) and backend (security) are protected
4. ✅ **Clear Indicators** - Staff sees AUDIT MODE badge
5. ⚠️ **Never rely on frontend alone** - Always validate on backend

---

## 📝 Next Steps

### Optional Enhancements:

1. **Permission-based** instead of role-based (e.g., Spatie Laravel Permission)
2. **Audit logging** - Track what staff members view
3. **Session timeout** for staff
4. **IP whitelisting** for admin access
5. **Two-factor authentication** for admin only

---

## 📞 Support

Kung may tanong or issue, i-check ang:

- Middleware: `app/Http/Middleware/AdminOnly.php`
- Routes: `routes/web.php`
- Shared props: `app/Http/Middleware/HandleInertiaRequests.php`
- Composable: `resources/js/composables/useAuth.js`
