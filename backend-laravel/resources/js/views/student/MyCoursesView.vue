<template>
  <AppLayout>
    <div class="container mx-auto">
      <div class="mb-8">
        <h1 class="text-4xl font-bold">My Courses</h1>
        <p class="text-gray-600 mt-2">Courses you are enrolled in</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-16">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Course list -->
      <div v-else-if="enrollments.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="enrollment in enrollments" :key="enrollment.id" class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
          <div class="card-body">
            <h2 class="card-title">{{ enrollment.course.title }}</h2>
            <p class="text-sm text-gray-600 line-clamp-3">{{ enrollment.course.description }}</p>

            <div class="mt-3">
              <div class="flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="font-semibold">Instructor:</span>
                <span>{{ enrollment.course.instructor?.name }}</span>
              </div>

              <div class="flex items-center gap-2 text-sm mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="font-semibold">Enrolled:</span>
                <span>{{ formatDate(enrollment.enrollment_date) }}</span>
              </div>
            </div>

            <div class="card-actions justify-end mt-4">
              <router-link
                :to="`/student/courses/${enrollment.course.id}/assessments`"
                class="btn btn-primary btn-sm"
              >
                View Assessments
              </router-link>
              <router-link
                :to="`/student/courses/${enrollment.course.id}/materials`"
                class="btn btn-secondary btn-sm"
              >
                View Materials
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="text-xl font-bold mt-4">You are not enrolled in any course</h3>
        <p class="text-gray-600 mt-2">Contact your instructor to be enrolled in a course</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '../../components/layout/AppLayout.vue';

const enrollments = ref([]);
const loading = ref(true);

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const fetchEnrollments = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/my-enrollments');
    enrollments.value = response.data.enrollments;
  } catch (error) {
    console.error('Error loading courses:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchEnrollments();
});
</script>
