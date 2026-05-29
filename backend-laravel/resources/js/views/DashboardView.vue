<template>
  <AppLayout>
    <div class='container mx-auto'>
      <h1 class='text-4xl font-bold mb-8'>
        Welcome, {{ authStore.user?.name }}
      </h1>

      <div class='grid grid-cols-1 md:grid-cols-3 gap-6'>
        <!-- Profile card -->
        <div class='card bg-base-100 shadow-xl'>
          <div class='card-body'>
            <h2 class='card-title'>Your Profile</h2>
            <div class='space-y-2'>
              <p><strong>Role:</strong> {{ roleLabel }}</p>
              <p><strong>Email:</strong> {{ authStore.user?.email }}</p>
              <p v-if='authStore.user?.last_login'>
                <strong>Last login:</strong><br/>
                {{ formatDate(authStore.user.last_login) }}
              </p>
            </div>
            <div class='card-actions justify-end'>
              <router-link to='/profile' class='btn btn-primary btn-sm'>
                View Profile
              </router-link>
            </div>
          </div>
        </div>

        <!-- Role-specific cards -->
        <div v-if='authStore.user?.role === "admin"' class='card bg-primary text-primary-content shadow-xl'>
          <div class='card-body'>
            <h2 class='card-title'>Administration Panel</h2>
            <p>Manage users and instructors in the system</p>
            <div class='card-actions justify-end'>
              <router-link to='/admin/users' class='btn btn-secondary btn-sm'>
                View Users
              </router-link>
            </div>
          </div>
        </div>

        <div v-if='authStore.user?.role === "instructor"' class='card bg-secondary text-secondary-content shadow-xl'>
          <div class='card-body'>
            <h2 class='card-title'>My Courses</h2>
            <p>Create and manage courses, students and assessments</p>
            <div class='card-actions justify-end mt-4'>
              <router-link to='/instructor/courses' class='btn btn-primary btn-sm'>
                View Courses
              </router-link>
            </div>
          </div>
        </div>

        <div v-if='authStore.user?.role === "student"' class='card bg-accent text-accent-content shadow-xl'>
          <div class='card-body'>
            <h2 class='card-title'>My Courses</h2>
            <p>Access your enrolled courses and assessments</p>
            <div class='card-actions justify-end mt-4'>
              <router-link to='/student/courses' class='btn btn-primary btn-sm'>
                View My Courses
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class='stats shadow mt-8 w-full'>
        <div class='stat'>
          <div class='stat-figure text-primary'>
            <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' class='inline-block w-8 h-8 stroke-current'>
              <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>
            </svg>
          </div>
          <div class='stat-title'>Status</div>
          <div class='stat-value text-primary'>Active</div>
          <div class='stat-desc'>System running</div>
        </div>

        <div class='stat'>
          <div class='stat-title'>Your Role</div>
          <div class='stat-value text-sm'>{{ roleLabel }}</div>
          <div class='stat-desc'>{{ authStore.user?.email }}</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import AppLayout from '../components/layout/AppLayout.vue';

const authStore = useAuthStore();

const roleLabel = computed(() => {
  const roles = {
    admin: 'Administrator',
    instructor: 'Instructor',
    student: 'Student',
  };
  return roles[authStore.user?.role] || '';
});

const formatDate = (date) => {
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(async () => {
  if (!authStore.user && authStore.token) {
    try {
      await authStore.fetchUser();
    } catch (error) {
      console.error('Error fetching user:', error);
    }
  }
});
</script>
