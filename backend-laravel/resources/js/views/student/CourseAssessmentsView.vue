<template>
  <AppLayout>
    <div class="container mx-auto">
      <div class="flex items-center mb-8">
        <router-link to="/student/courses" class="btn btn-ghost btn-circle mr-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        <div class="flex-1">
          <h1 class="text-4xl font-bold">Assessments</h1>
          <p class="text-gray-600">{{ course?.title }}</p>
        </div>
        <router-link
          :to="`/student/courses/${$route.params.courseId}/materials`"
          class="btn btn-secondary btn-sm"
        >
          View Study Materials
        </router-link>

        <router-link
          :to="`/student/courses/${$route.params.courseId}/report`"
          class="btn btn-info btn-sm ml-2"
          @click="regenerateAllProfiles"
        >
          My Report
        </router-link>
      </div>

      <!-- Error -->
      <div v-if="error" class="alert alert-error shadow-lg mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="font-bold">Error loading course</h3>
          <div class="text-sm">{{ error }}</div>
        </div>
        <router-link to="/student/courses" class="btn btn-sm">Back to My Courses</router-link>
      </div>

      <!-- Loading -->
      <div v-else-if="loading" class="flex justify-center py-16">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Assessment list -->
      <div v-else-if="assessments.length > 0" class="grid grid-cols-1 gap-6">
        <div v-for="assessment in assessments" :key="assessment.id" class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h2 class="card-title">{{ assessment.title }}</h2>
                <p class="text-sm text-gray-600">{{ assessment.description }}</p>

                <div class="flex gap-2 mt-3">
                  <span class="badge" :class="getTypeBadge(assessment.assessment_type)">
                    {{ getTypeLabel(assessment.assessment_type) }}
                  </span>
                  <span v-if="!assessment.is_active" class="badge badge-ghost">
                    Inactive
                  </span>
                  <span v-else-if="assessment.is_completed" class="badge badge-success">
                    Completed
                  </span>
                  <span v-else class="badge badge-warning">
                    Pending
                  </span>
                  <span class="badge badge-outline">
                    {{ assessment.questions?.length || 0 }} questions
                  </span>
                  <span v-if="assessment.time_limit" class="badge badge-outline">
                    {{ assessment.time_limit }} min
                  </span>
                </div>

                <!-- Student response info -->
                <div v-if="assessment.user_response" class="mt-4 p-3 bg-base-200 rounded-lg">
                  <div class="flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-semibold">Completed on:</span>
                    <span>{{ formatDateTime(assessment.user_response.completed_at) }}</span>
                  </div>
                  <div v-if="assessment.user_response.score !== null && !isLikertType(assessment.assessment_type)" class="flex items-center gap-2 text-sm mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <span class="font-semibold">Score:</span>
                    <span>{{ assessment.user_response.score }}%</span>
                  </div>
                </div>
              </div>

              <div class="flex flex-col gap-2">
                <router-link
                  v-if="assessment.is_active && !assessment.is_completed"
                  :to="`/student/courses/${route.params.courseId}/assessments/${assessment.id}/take`"
                  class="btn btn-primary btn-sm"
                >
                  Start Assessment
                </router-link>
                <router-link
                  v-else-if="assessment.is_active && assessment.is_completed"
                  :to="`/student/courses/${route.params.courseId}/assessments/${assessment.id}/take`"
                  class="btn btn-outline btn-sm"
                >
                  View Answers
                </router-link>
                <span v-else class="badge badge-ghost">
                  Not available
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-xl font-bold mt-4">No assessments available</h3>
        <p class="text-gray-600 mt-2">The instructor has not published any assessments for this course yet</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import AppLayout from '../../components/layout/AppLayout.vue';

const route = useRoute();
const assessments = ref([]);
const course = ref(null);
const loading = ref(true);
const error = ref(null);

const getTypeLabel = (type) => {
  const labels = {
    recall_initial: 'Initial Recall',
    comprehension_initial: 'Initial Comprehension',
    mslq_motivation_initial: 'MSLQ Initial Motivation',
    mslq_strategies: 'MSLQ Strategies',
    recall_final: 'Final Recall',
    comprehension_final: 'Final Comprehension',
    cognitive_load: 'Cognitive Load',
    mslq_motivation_final: 'MSLQ Final Motivation',
    course_interest: 'Course Interest (CIS)',
    imms: 'Motivational Materials (IMMS)',
  };
  return labels[type] || type;
};

const isLikertType = (type) => {
  return ['mslq_motivation_initial', 'mslq_strategies', 'mslq_motivation_final',
          'cognitive_load', 'course_interest', 'imms'].includes(type);
};

const getTypeBadge = (type) => {
  if (type.includes('initial')) return 'badge-info';
  if (type.includes('final')) return 'badge-success';
  if (type.includes('mslq')) return 'badge-warning';
  return 'badge-ghost';
};

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const fetchData = async () => {
  try {
    loading.value = true;
    error.value = null;

    const courseResponse = await axios.get(`/api/courses/${route.params.courseId}`);
    course.value = courseResponse.data.course;

    const assessmentsResponse = await axios.get(`/api/courses/${route.params.courseId}/assessments`);
    assessments.value = assessmentsResponse.data.assessments;
  } catch (err) {
    console.error('Error loading data:', err);
    if (err.response?.status === 403) {
      error.value = 'You do not have access to this course. Make sure you are enrolled.';
    } else if (err.response?.status === 404) {
      error.value = 'The course was not found.';
    } else {
      error.value = 'An error occurred while loading course data.';
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
