<template>
  <div class="min-h-screen bg-base-200">
    <!-- Navbar -->
    <div class="navbar bg-base-100 shadow-lg">
      <div class="flex-1">
        <router-link to="/dashboard" class="btn btn-ghost text-xl">CLT4BP</router-link>
      </div>
      <div class="flex-none gap-2">
        <div class="dropdown dropdown-end">
          <label tabindex="0" class="btn btn-ghost btn-circle avatar">
            <div class="w-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
              <span class="text-lg">{{ userInitials }}</span>
            </div>
          </label>
          <ul tabindex="0" class="mt-3 p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52">
            <li>
              <router-link to="/profile" class="justify-between">
                Profile
                <span class="badge">{{ userRole }}</span>
              </router-link>
            </li>
            <li><a @click="handleLogout">Log Out</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Sidebar and content -->
    <div class="flex">
      <!-- Sidebar -->
      <div class="w-64 bg-base-100 min-h-screen shadow-lg">
        <ul class="menu p-4">
          <li>
            <router-link to="/dashboard" active-class="active">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              Dashboard
            </router-link>
          </li>

          <!-- Admin Menu -->
          <li v-if="authStore.user?.role === 'admin'">
            <details open>
              <summary>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Administration
              </summary>
              <ul>
                <li><router-link to="/admin/users">Users</router-link></li>
                <li><router-link to="/admin/instructors/create">Create Instructor</router-link></li>
              </ul>
            </details>
          </li>

          <!-- Instructor Menu -->
          <li v-if="authStore.user?.role === 'instructor'">
            <details open>
              <summary>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Courses
              </summary>
              <ul>
                <li><router-link to="/instructor/courses">My Courses</router-link></li>
                <li><router-link to="/instructor/courses/create">Create Course</router-link></li>
              </ul>
            </details>
          </li>

          <!-- Student Menu -->
          <li v-if="authStore.user?.role === 'student'">
            <router-link to="/student/courses">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              My Courses
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Main content -->
      <div class="flex-1 p-8">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

const userInitials = computed(() => {
  if (!authStore.user) return '?';
  return authStore.user.name.split(' ').map(n => n[0]).join('').toUpperCase();
});

const userRole = computed(() => {
  const roles = {
    admin: 'Admin',
    instructor: 'Instructor',
    student: 'Student',
  };
  return roles[authStore.user?.role] || '';
});

const handleLogout = () => {
  authStore.logout();
};
</script>
