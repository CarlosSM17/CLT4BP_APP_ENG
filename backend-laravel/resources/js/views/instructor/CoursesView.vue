<template>
  <AppLayout>
    <div class="container mx-auto">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold">My Courses</h1>
        <router-link to="/instructor/courses/create" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Course
        </router-link>
      </div>

      <!-- Filters -->
      <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-control">
              <label class="label">
                <span class="label-text">Status</span>
              </label>
              <select v-model="filters.status" @change="loadCourses" class="select select-bordered">
                <option value="">All</option>
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Search</span>
              </label>
              <input
                v-model="filters.search"
                @input="handleSearch"
                type="text"
                placeholder="Search courses..."
                class="input input-bordered"
              />
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">&nbsp;</span>
              </label>
              <button @click="resetFilters" class="btn btn-outline">
                Clear Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="courseStore.loading" class="flex justify-center py-8">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Error -->
      <div v-else-if="courseStore.error" class="alert alert-error">
        <span>{{ courseStore.error }}</span>
      </div>

      <!-- Course list -->
      <div v-else-if="courseStore.courses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="course in courseStore.courses" :key="course.id" class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">{{ course.title }}</h2>
            <p class="text-sm text-gray-600">{{ truncate(course.description, 100) }}</p>

            <div class="flex gap-2 mt-2">
              <span class="badge" :class="getStatusBadge(course.status)">
                {{ getStatusLabel(course.status) }}
              </span>
              <span class="badge badge-ghost">
                {{ course.students_count || 0 }} students
              </span>
            </div>

            <div class="divider my-2"></div>

            <div class="card-actions justify-end">
              <router-link :to="`/instructor/courses/${course.id}`" class="btn btn-sm btn-info">
                View
              </router-link>
              <router-link :to="`/instructor/courses/${course.id}/edit`" class="btn btn-sm btn-warning">
                Edit
              </router-link>
              <button @click="confirmDelete(course)" class="btn btn-sm btn-error">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="text-xl font-bold mt-4">No courses</h3>
        <p class="text-gray-600 mt-2">Create your first course to get started</p>
        <router-link to="/instructor/courses/create" class="btn btn-primary mt-4">
          Create Course
        </router-link>
      </div>
    </div>

    <!-- Confirmation modal -->
    <dialog ref="confirmModal" class="modal">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Deletion</h3>
        <p class="py-4">Are you sure you want to delete the course <strong>{{ selectedCourse?.title }}</strong>?</p>
        <div class="modal-action">
          <button @click="closeModal" class="btn">Cancel</button>
          <button @click="deleteCourse" class="btn btn-error" :disabled="deleting">
            <span v-if="deleting" class="loading loading-spinner"></span>
            <span v-else>Delete</span>
          </button>
        </div>
      </div>
      <form method="dialog" class="modal-backdrop">
        <button>close</button>
      </form>
    </dialog>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useCourseStore } from '../../stores/courses';
import AppLayout from '../../components/layout/AppLayout.vue';

const courseStore = useCourseStore();

const filters = ref({
  status: '',
  search: '',
});

const selectedCourse = ref(null);
const deleting = ref(false);
const confirmModal = ref(null);

const loadCourses = async () => {
  await courseStore.fetchCourses(filters.value);
};

const handleSearch = () => {
  loadCourses();
};

const resetFilters = () => {
  filters.value = {
    status: '',
    search: '',
  };
  loadCourses();
};

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

const truncate = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const confirmDelete = (course) => {
  selectedCourse.value = course;
  confirmModal.value.showModal();
};

const deleteCourse = async () => {
  try {
    deleting.value = true;
    await courseStore.deleteCourse(selectedCourse.value.id);
    closeModal();
  } catch (error) {
    console.error('Error deleting:', error);
  } finally {
    deleting.value = false;
  }
};

const closeModal = () => {
  confirmModal.value.close();
  selectedCourse.value = null;
};

onMounted(() => {
  loadCourses();
});
</script>
