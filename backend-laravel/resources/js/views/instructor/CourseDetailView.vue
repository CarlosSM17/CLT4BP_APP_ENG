<template>
  <AppLayout>
    <div class="container mx-auto">
      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-8">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Course content -->
      <div v-else-if="course">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8">
          <div class="flex items-center">
            <router-link to="/instructor/courses" class="btn btn-ghost btn-circle mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </router-link>
            <div>
              <h1 class="text-4xl font-bold">{{ course.title }}</h1>
              <p class="text-gray-600 mt-2">{{ course.description }}</p>
            </div>
          </div>
          <div class="flex gap-2">
            <router-link :to="`/instructor/courses/${course.id}/edit`" class="btn btn-warning">
              Edit
            </router-link>
            <router-link :to="`/instructor/courses/${course.id}/students`" class="btn btn-primary">
              Manage Students
            </router-link>
            <router-link :to="`/instructor/courses/${course.id}/data-collection`" class="btn btn-accent">
              Data Collection
            </router-link>
            <router-link :to="`/instructor/courses/${course.id}/reports`" class="btn btn-info">
              Reports
            </router-link>
          </div>
        </div>

        <!-- Stats -->
        <div class="stats shadow w-full mb-8">
          <div class="stat">
            <div class="stat-title">Status</div>
            <div class="stat-value text-sm">{{ getStatusLabel(course.status) }}</div>
            <div class="stat-desc">
              <span class="badge" :class="getStatusBadge(course.status)">
                {{ getStatusLabel(course.status) }}
              </span>
            </div>
          </div>

          <div class="stat">
            <div class="stat-title">Enrolled Students</div>
            <div class="stat-value">{{ course.students_count || 0 }}</div>
            <div class="stat-desc">Total students</div>
          </div>

          <div class="stat">
            <div class="stat-title">Start Date</div>
            <div class="stat-value text-sm">{{ formatDate(course.start_date) }}</div>
          </div>

          <div class="stat">
            <div class="stat-title">End Date</div>
            <div class="stat-value text-sm">{{ formatDate(course.end_date) }}</div>
          </div>
        </div>

        <!-- Course Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Learning Objectives -->
          <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-xl">
              <div class="card-body">
                <h2 class="card-title">Learning Objectives</h2>
                <div v-if="course.learning_objectives && course.learning_objectives.length > 0">
                  <ul class="list-disc list-inside space-y-2">
                    <li v-for="(objective, index) in course.learning_objectives" :key="index">
                      {{ objective }}
                    </li>
                  </ul>
                </div>
                <div v-else class="text-gray-500 italic">
                  No learning objectives defined
                </div>
              </div>
            </div>

            <!-- Recent Students -->
            <div class="card bg-base-100 shadow-xl mt-6">
              <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="card-title">Enrolled Students</h2>
                  <router-link :to="`/instructor/courses/${course.id}/students`" class="btn btn-sm btn-primary">
                    View All
                  </router-link>
                </div>
                <div v-if="course.students && course.students.length > 0">
                  <div class="overflow-x-auto">
                    <table class="table table-zebra">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>Email</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="student in course.students.slice(0, 5)" :key="student.id">
                          <td>{{ student.name }}</td>
                          <td>{{ student.email }}</td>
                          <td>
                            <span class="badge badge-success">
                              {{ student.pivot.status }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-if="course.students.length > 5" class="text-center mt-4 text-sm text-gray-600">
                    And {{ course.students.length - 5 }} more students...
                  </div>
                </div>
                <div v-else class="text-gray-500 italic">
                  No enrolled students
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-xl">
              <div class="card-body">
                <h2 class="card-title">Course Information</h2>

                <div class="space-y-4">
                  <div>
                    <label class="font-semibold">Instructor:</label>
                    <p>{{ course.instructor?.name }}</p>
                    <p class="text-sm text-gray-600">{{ course.instructor?.email }}</p>
                  </div>

                  <div class="divider"></div>

                  <div>
                    <label class="font-semibold">Created:</label>
                    <p>{{ formatDateTime(course.created_at) }}</p>
                  </div>

                  <div>
                    <label class="font-semibold">Updated:</label>
                    <p>{{ formatDateTime(course.updated_at) }}</p>
                  </div>

                  <div class="divider"></div>

                  <div>
                    <label class="font-semibold">Course ID:</label>
                    <p class="font-mono text-sm">{{ course.id }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="card bg-base-100 shadow-xl mt-6">
              <div class="card-body">
                <h2 class="card-title">Quick Actions</h2>

                <div class="space-y-2">
                  <router-link
                    :to="`/instructor/courses/${course.id}/students`"
                    class="btn btn-block btn-outline"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Enroll Students
                  </router-link>

                  <router-link
                    :to="`/instructor/courses/${course.id}/assessments`"
                    class="btn btn-block btn-outline"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Manage Assessments
                  </router-link>

                  <router-link
                    :to="`/instructor/courses/${course.id}/profiles`"
                    class="btn btn-block btn-outline btn-info"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Student Profiles
                  </router-link>

                  <router-link
                    :to="`/instructor/courses/${course.id}/grading`"
                    class="btn btn-block btn-outline btn-warning"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Pending Grades
                  </router-link>

                  <div class="divider text-xs">---- AI ----</div>

                  <router-link
                    :to="`/instructor/courses/${course.id}/clt-effects`"
                    class="btn btn-block btn-outline btn-secondary"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    CLT Effects
                  </router-link>

                  <router-link
                    :to="`/instructor/courses/${course.id}/materials`"
                    class="btn btn-block btn-outline btn-accent"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Generate AI Material
                  </router-link>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCourseStore } from '../../stores/courses';
import AppLayout from '../../components/layout/AppLayout.vue';

const route = useRoute();
const courseStore = useCourseStore();

const course = ref(null);
const loading = ref(true);

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    active: 'Active',
    inactive: 'Inactive',
    completed: 'Completed',
  };
  return labels[status] || status;
};

const getStatusBadge = (status) => {
  const badges = {
    draft: 'badge-ghost',
    active: 'badge-success',
    inactive: 'badge-warning',
    completed: 'badge-info',
  };
  return badges[status] || 'badge-ghost';
};

const formatDate = (date) => {
  if (!date) return 'Not defined';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatDateTime = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(async () => {
  try {
    loading.value = true;
    course.value = await courseStore.fetchCourse(route.params.id);
  } catch (error) {
    console.error('Error loading course:', error);
  } finally {
    loading.value = false;
  }
});
</script>
