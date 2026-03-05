# Frontend Documentation

## Overview

Nexus uses **Vue.js 3** with **Inertia.js 2** to build a modern single-page application (SPA) experience while maintaining Laravel's server-side routing and controllers.

### Technology Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| Vue.js | 3.x | Frontend framework |
| Inertia.js | 2.x | Server-driven SPA |
| TailwindCSS | 3.x/4.x | Utility-first CSS |
| Vite | 7.x | Build tool |
| TypeScript | 5.x | Type safety |
| Axios | 1.x | HTTP client |

---

## Project Structure

```
resources/
├── css/
│   └── app.css                    # Tailwind imports + custom styles
│
├── js/
│   ├── Components/                # Reusable Vue components
│   │   ├── ApplicationLogo.vue
│   │   ├── Checkbox.vue
│   │   ├── DangerButton.vue
│   │   ├── Dropdown.vue
│   │   ├── DropdownLink.vue
│   │   ├── InputError.vue
│   │   ├── InputLabel.vue
│   │   ├── Modal.vue
│   │   ├── NavLink.vue
│   │   ├── PrimaryButton.vue
│   │   ├── ResponsiveNavLink.vue
│   │   ├── SecondaryButton.vue
│   │   └── TextInput.vue
│   │
│   ├── Layouts/                   # Page layouts
│   │   ├── AuthenticatedLayout.vue
│   │   └── GuestLayout.vue
│   │
│   ├── Pages/                     # Inertia page components
│   │   ├── Auth/
│   │   │   ├── ConfirmPassword.vue
│   │   │   ├── ForgotPassword.vue
│   │   │   ├── Login.vue
│   │   │   ├── Register.vue
│   │   │   ├── ResetPassword.vue
│   │   │   └── VerifyEmail.vue
│   │   ├── Profile/
│   │   │   ├── Edit.vue
│   │   │   └── Partials/
│   │   │       ├── DeleteUserForm.vue
│   │   │       ├── UpdatePasswordForm.vue
│   │   │       └── UpdateProfileInformationForm.vue
│   │   ├── Dashboard.vue
│   │   └── Welcome.vue
│   │
│   ├── types/                     # TypeScript definitions
│   │   ├── global.d.ts
│   │   ├── index.d.ts
│   │   └── vite-env.d.ts
│   │
│   └── app.js                     # Application entry point
│
└── views/
    ├── admin/                     # Admin Blade views
    ├── auth/                      # Auth Blade views
    ├── chat/                      # Chat Blade views
    ├── groups/                    # Group Blade views
    ├── layouts/                   # Blade layouts
    ├── partials/                  # Blade partials
    ├── posts/                     # Post Blade views
    ├── stories/                   # Story Blade views
    ├── users/                     # User Blade views
    ├── errors/                    # Error pages
    ├── emails/                    # Email templates
    └── app.blade.php              # Root template
```

---

## Application Entry Point

### app.js

**Location:** `resources/js/app.js`

```javascript
import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Nexus';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#5e60ce',
    },
});
```

**Purpose:**
- Initializes Inertia.js application
- Configures page title
- Loads Vue pages dynamically
- Integrates Ziggy for route helpers
- Sets progress bar color

---

## Inertia.js Architecture

### How Inertia Works

```
┌─────────────┐     HTTP      ┌─────────────┐
│   Browser   │ ◄───────────► │   Laravel   │
│   (Vue.js)  │   Inertia     │  Controller │
│             │   Requests    │             │
└─────────────┘               └──────┬──────┘
                                     │
                                     ▼
                              ┌─────────────┐
                              │   Inertia   │
                              │  Response   │
                              │  (JSON)     │
                              └──────┬──────┘
                                     │
                                     ▼
                              ┌─────────────┐
                              │   Vue Page  │
                              │   Render    │
                              └─────────────┘
```

### Making Requests

```javascript
import { router } from '@inertiajs/vue3';

// GET request
router.get('/users/1');

// POST request with data
router.post('/posts', {
    content: 'Hello world!',
    media: files,
});

// PUT/PATCH request
router.put('/posts/1', {
    content: 'Updated content',
});

// DELETE request
router.delete('/posts/1');

// With options
router.post('/posts', data, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => console.log('Success!', page),
    onError: (errors) => console.log('Error!', errors),
    onFinish: () => console.log('Request finished'),
});
```

### Using Form Helper

```javascript
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    content: '',
    media: null,
    is_private: false,
});

// Submit form
form.post('/posts', {
    preserveScroll: true,
    resetOnSuccess: true,
});

// Reset form
form.reset();

// Clear errors
form.clearErrors();

// Processing state
if (form.processing) {
    // Show loading spinner
}
```

---

## Page Components

### Page Structure

```vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Props from controller
defineProps({
    posts: Array,
    user: Object,
});

// Form handling
const form = useForm({
    content: '',
});

const submit = () => {
    form.post('/posts');
};
</script>

<template>
    <Head title="Home" />
    
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Feed
            </h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Page content -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

## Layouts

### AuthenticatedLayout

**Location:** `resources/js/Layouts/AuthenticatedLayout.vue`

**Purpose:** Main layout for authenticated users with navigation.

**Features:**
- Top navigation bar
- User dropdown menu
- Notification badge
- Responsive mobile menu
- Flash message display

**Slots:**
- `#header` - Page header content

**Usage:**
```vue
<template>
    <AuthenticatedLayout>
        <template #header>
            <h2>Page Title</h2>
        </template>
        
        <!-- Page content -->
    </AuthenticatedLayout>
</template>
```

---

### GuestLayout

**Location:** `resources/js/Layouts/GuestLayout.vue`

**Purpose:** Layout for guest pages (login, register).

**Features:**
- Centered card design
- Application logo
- Minimal navigation

---

## Reusable Components

### PrimaryButton

```vue
<template>
    <button
        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>
```

---

### Modal

```vue
<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    maxWidth: String,
    closeable: Boolean,
});

const emit = defineEmits(['close']);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = null;
        }
    }
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition leave-active-duration="200">
            <div
                v-show="show"
                class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
            >
                <!-- Modal content -->
            </div>
        </Transition>
    </Teleport>
</template>
```

---

### Dropdown

```vue
<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: String,
    width: String,
    contentClasses: Array,
});

const open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));
</script>

<template>
    <div class="relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <div v-show="open" class="absolute z-50">
            <div @click="open = false">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>
```

---

## State Management

### Component State

```vue
<script setup>
import { ref, computed, watch } from 'vue';

// Reactive state
const count = ref(0);
const posts = ref([]);
const loading = ref(false);

// Computed properties
const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read).length;
});

// Watch for changes
watch(count, (newVal, oldVal) => {
    console.log(`Count changed from ${oldVal} to ${newVal}`);
});

// Methods
const increment = () => {
    count.value++;
};
</script>
```

---

### Shared State (Inertia)

Data shared from Laravel is available via props:

```vue
<script setup>
const props = defineProps({
    auth: Object,
    errors: Object,
    flash: Object,
});

// Access shared data
const user = props.auth.user;
const unreadNotifications = props.auth.user?.unread_notifications_count;
</script>
```

---

## Routing

### Using Ziggy

Ziggy provides Laravel route helpers in JavaScript:

```javascript
import { route } from '../../vendor/tightenco/ziggy';

// Generate URL
const url = route('posts.show', 1);

// Navigate
router.get(route('posts.index'));

// With parameters
route('users.follow', { user: 1 });

// Query parameters
route('posts.index', { page: 2, filter: 'latest' });
```

### Route Types

```typescript
// resources/js/types/index.d.ts
export interface User {
    id: number;
    name: string;
    email: string;
    username: string;
    avatar_url: string;
    is_online: boolean;
}

export interface Post {
    id: number;
    slug: string;
    content: string;
    user: User;
    media: Media[];
    likes_count: number;
    comments_count: number;
    is_liked: boolean;
    created_at: string;
}

export interface PageProps {
    auth: {
        user: User;
    };
    errors: Record<string, string>;
    flash: {
        success?: string;
        error?: string;
    };
}
```

---

## Real-time Features

### Polling Implementation

```vue
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const notifications = ref([]);
const unreadCount = ref(0);
let pollingInterval = null;

const fetchNotifications = async () => {
    const response = await fetch('/api/notifications/realtime-updates');
    const data = await response.json();
    
    if (data.has_updates) {
        unreadCount.value = data.unread_count;
        notifications.value = data.new_notifications;
    }
};

onMounted(() => {
    // Poll every 5 seconds
    pollingInterval = setInterval(fetchNotifications, 5000);
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>
```

---

### Online Status Polling

```javascript
// Update user online status
const updateOnlineStatus = async () => {
    await fetch('/user/update-online-status', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });
};

// Poll every 30 seconds
setInterval(updateOnlineStatus, 30000);
```

---

## Form Handling

### Basic Form

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        },
        onError: (errors) => {
            // Handle errors
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit">
        <TextInput
            v-model="form.email"
            type="email"
            :error="form.errors.email"
        />
        
        <TextInput
            v-model="form.password"
            type="password"
            :error="form.errors.password"
        />
        
        <label>
            <input type="checkbox" v-model="form.remember" />
            Remember me
        </label>
        
        <PrimaryButton :disabled="form.processing">
            Login
        </PrimaryButton>
        
        <div v-if="form.errors">
            {{ form.errors.email }}
        </div>
    </form>
</template>
```

---

### File Upload Form

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    content: '',
    media: [],
    is_private: false,
});

const previewUrls = ref([]);

const handleFileChange = (event) => {
    const files = Array.from(event.target.files);
    form.media = files;
    
    // Generate previews
    previewUrls.value = files.map(file => URL.createObjectURL(file));
};

const submit = () => {
    form.post('/posts', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <form @submit.prevent="submit">
        <textarea v-model="form.content" />
        
        <input 
            type="file" 
            multiple 
            @change="handleFileChange"
            accept="image/*,video/*"
        />
        
        <div v-if="previewUrls.length">
            <img 
                v-for="url in previewUrls" 
                :src="url" 
                :key="url"
            />
        </div>
        
        <label>
            <input type="checkbox" v-model="form.is_private" />
            Private
        </label>
        
        <button type="submit" :disabled="form.processing">
            Post
        </button>
    </form>
</template>
```

---

## CSS & Styling

### TailwindCSS Configuration

**Location:** `tailwind.config.js`

```javascript
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#5e60ce',
                secondary: '#6930c3',
                dark: '#1a1a2e',
            },
        },
    },

    plugins: [forms],
};
```

---

### Custom CSS (app.css)

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom properties */
:root {
    --color-primary: #5e60ce;
    --color-secondary: #6930c3;
    --color-dark: #1a1a2e;
    --color-light: #f8f9fa;
    
    --spacing-unit: 0.25rem;
    
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
}

/* Dark theme */
.dark {
    --color-dark: #f8f9fa;
    --color-light: #1a1a2e;
}

/* Custom components */
.btn-primary {
    @apply bg-primary text-white px-4 py-2 rounded hover:bg-secondary transition;
}

.card {
    @apply bg-white dark:bg-dark-800 rounded-lg shadow-md p-6;
}
```

---

## Blade Views

### Root Template (app.blade.php)

**Location:** `resources/views/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <title inertia>{{ config('app.name', 'Nexus') }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
        
        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
```

---

### Layout Template

**Location:** `resources/views/layouts/app.blade.php`

A comprehensive layout with:
- Navigation bar
- Sidebar (optional)
- Main content area
- Footer
- Flash messages
- Theme toggle

---

### Partial Views

#### Post Partial

**Location:** `resources/views/partials/post.blade.php`

Reusable post card component used across multiple pages.

**Features:**
- User info header
- Post content with mentions
- Media gallery
- Like/comment/save actions
- Engagement counts

---

## JavaScript Utilities

### Bootstrap (axios setup)

**Location:** `resources/js/bootstrap.js`

```javascript
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;
```

---

### Date Formatting

```javascript
// resources/js/utils/date.js

export function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (seconds < 60) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    
    return date.toLocaleDateString();
}

export function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}
```

---

### Media Preview

```javascript
// resources/js/utils/media.js

export function generatePreview(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

export function validateMedia(file, options = {}) {
    const {
        maxSize = 10 * 1024 * 1024, // 10MB
        allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4'],
    } = options;
    
    if (file.size > maxSize) {
        throw new Error('File too large');
    }
    
    if (!allowedTypes.includes(file.type)) {
        throw new Error('Invalid file type');
    }
    
    return true;
}
```

---

## TypeScript Configuration

### tsconfig.json

```json
{
    "compilerOptions": {
        "target": "ESNext",
        "useDefineForClassFields": true,
        "module": "ESNext",
        "moduleResolution": "Node",
        "strict": true,
        "jsx": "preserve",
        "resolveJsonModule": true,
        "isolatedModules": true,
        "esModuleInterop": true,
        "lib": ["ESNext", "DOM"],
        "skipLibCheck": true,
        "noEmit": true,
        "paths": {
            "@/*": ["./resources/js/*"]
        }
    },
    "include": ["resources/js/**/*.ts", "resources/js/**/*.d.ts", "resources/js/**/*.vue"],
    "exclude": ["node_modules"]
}
```

---

## Vite Configuration

### vite.config.js

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
});
```

---

## Development Commands

```bash
# Start development server with hot reload
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Format code
npm run format

# Lint code
npm run lint

# Type check
npm run type-check
```

---

## Best Practices

### Component Organization

1. **Single Responsibility**: Each component should do one thing
2. **Props Validation**: Always define prop types
3. **Emit Events**: Use emits for parent communication
4. **Composables**: Extract reusable logic

### Performance

1. **Lazy Loading**: Load components on demand
2. **Memoization**: Use computed properties
3. **Debouncing**: For search inputs
4. **Virtual Scrolling**: For long lists

### Accessibility

1. **Semantic HTML**: Use proper tags
2. **ARIA Labels**: For interactive elements
3. **Keyboard Navigation**: Tab support
4. **Focus Management**: Trap focus in modals

### Security

1. **XSS Prevention**: Never use v-html with user input
2. **CSRF Tokens**: Automatic with Inertia
3. **Input Validation**: Client and server-side

---

## Common Patterns

### Infinite Scroll

```vue
<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const posts = ref([]);
const page = ref(1);
const loading = ref(false);
const hasMore = ref(true);

const loadMore = async () => {
    if (loading.value || !hasMore.value) return;
    
    loading.value = true;
    
    const response = await fetch(`/api/posts?page=${page.value + 1}`);
    const data = await response.json();
    
    posts.value = [...posts.value, ...data.posts];
    page.value++;
    hasMore.value = data.meta.current_page < data.meta.last_page;
    loading.value = false;
};

onMounted(() => {
    window.addEventListener('scroll', () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
            loadMore();
        }
    });
});
</script>
```

---

### Search with Debounce

```vue
<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const query = ref('');
const results = ref([]);
const searching = ref(false);

const search = async () => {
    if (!query.value) {
        results.value = [];
        return;
    }
    
    searching.value = true;
    
    const response = await fetch(`/api/search?q=${query.value}`);
    const data = await response.json();
    
    results.value = data.results;
    searching.value = false;
};

// Debounce search
let timeout;
watch(query, () => {
    clearTimeout(timeout);
    timeout = setTimeout(search, 300);
});
</script>
```

---

### Modal Pattern

```vue
<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const showModal = ref(false);

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <PrimaryButton @click="openModal">
        Open Modal
    </PrimaryButton>
    
    <Modal :show="showModal" @close="closeModal">
        <template #header>
            <h3>Modal Title</h3>
        </template>
        
        <template #default>
            <!-- Modal content -->
        </template>
        
        <template #footer>
            <SecondaryButton @click="closeModal">
                Cancel
            </SecondaryButton>
            <PrimaryButton @click="closeModal">
                Confirm
            </PrimaryButton>
        </template>
    </Modal>
</template>
```

---

## Troubleshooting

### Common Issues

**1. Page not refreshing after update**
```javascript
// Use preserveScroll: false
form.post('/posts', {
    preserveScroll: false,
});
```

**2. Form data not sending**
```javascript
// Force FormData for file uploads
form.post('/posts', {
    forceFormData: true,
});
```

**3. CSRF token mismatch**
```html
<!-- Ensure meta tag exists -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

**4. Vite manifest not found**
```bash
npm run build
```

**5. TypeScript errors**
```bash
# Check types
npm run type-check

# Regenerate types
php artisan ziggy:generate --types
```

---

**Last Updated**: March 2026
