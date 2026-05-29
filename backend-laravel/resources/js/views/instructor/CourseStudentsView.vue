<template>
  <AppLayout>
    <div class="container mx-auto">
      <div class="flex items-center mb-8">
        <router-link :to="`/instructor/courses/${route.params.id}`" class="btn btn-ghost btn-circle mr-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        <div>
          <h1 class="text-4xl font-bold">Student Management</h1>
          <p class="text-gray-600">{{ course?.title }}</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-8">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Enrolled Students List -->
        <div class="lg:col-span-2">
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title">Enrolled Students ({{ enrolledStudents.length }})</h2>

              <div v-if="enrolledStudents.length > 0" class="overflow-x-auto">
                <table class="table table-zebra">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Enrollment Date</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="student in enrolledStudents" :key="student.id">
                      <td>{{ student.name }}</td>
                      <td>{{ student.email }}</td>
                      <td>{{ formatDate(student.pivot.enrollment_date) }}</td>
                      <td>
                        <button
                          @click="confirmUnenroll(student)"
                          class="btn btn-sm btn-error"
                        >
                          Remove
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No students enrolled in this course
              </div>
            </div>
          </div>
        </div>

        <!-- Enrollment Panel -->
        <div class="lg:col-span-1">
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title">Enroll Students</h2>

              <div v-if="successMessage" class="alert alert-success mb-4">
                <span>{{ successMessage }}</span>
              </div>

              <div v-if="errorMessage" class="alert alert-error mb-4">
                <span>{{ errorMessage }}</span>
              </div>

              <!-- Search students -->
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Search students</span>
                </label>
                <input
                  v-model="searchQuery"
                  @input="searchStudents"
                  type="text"
                  placeholder="Name or email..."
                  class="input input-bordered"
                />
              </div>

              <!-- Available students list -->
              <div class="form-control mt-4">
                <label class="label">
                  <span class="label-text">Select students</span>
                </label>
                <div class="border rounded-lg p-2 max-h-64 overflow-y-auto">
                  <div v-if="filteredAvailableStudents.length > 0">
                    <label
                      v-for="student in filteredAvailableStudents"
                      :key="student.id"
                      class="flex items-center gap-2 p-2 hover:bg-base-200 rounded cursor-pointer"
                    >
                      <input
                        type="checkbox"
                        :value="student.id"
                        v-model="selectedStudents"
                        class="checkbox checkbox-primary"
                      />
                      <div class="flex-1">
                        <div class="font-medium">{{ student.name }}</div>
                        <div class="text-sm text-gray-600">{{ student.email }}</div>
                      </div>
                    </label>
                  </div>
                  <div v-else class="text-center py-4 text-gray-500">
                    No students available
                  </div>
                </div>
              </div>

              <div class="form-control mt-4">
                <button
                  @click="enrollSelectedStudents"
                  class="btn btn-primary"
                  :disabled="selectedStudents.length === 0 || enrolling"
                >
                  <span v-if="enrolling" class="loading loading-spinner"></span>
                  <span v-else>Enroll {{ selectedStudents.length }} student(s)</span>
                </button>
              </div>

              <div class="divider"></div>

              <div class="stats stats-vertical shadow">
                <div class="stat">
                  <div class="stat-title">Total Available</div>
                  <div class="stat-value text-primary">{{ availableStudents.length }}</div>
                </div>

                <div class="stat">
                  <div class="stat-title">Selected</div>
                  <div class="stat-value text-secondary">{{ selectedStudents.length }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation modal -->
    <dialog ref="confirmModal" class="modal">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Removal</h3>
        <p class="py-4">Are you sure you want to remove <strong>{{ selectedStudent?.name }}</strong> from the course?</p>
        <div class="modal-action">
          <button @click="closeModal" class="btn">Cancel</button>
          <button @click="unenrollStudent" class="btn btn-error" :disabled="unenrolling">
            <span v-if="unenrolling" class="loading loading-spinner"></span>
            <span v-else>Remove</span>
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
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCourseStore } from '../../stores/courses';
import AppLayout from '../../components/layout/AppLayout.vue';
import axios from 'axios';

const route = useRoute();
const courseStore = useCourseStore();

const course = ref(null);
const enrolledStudents = ref([]);
const availableStudents = ref([]);
const selectedStudents = ref([]);
const searchQuery = ref('');

const loading = ref(true);
const enrolling = ref(false);
const unenrolling = ref(false);
const successMessage = ref(null);
const errorMessage = ref(null);

const selectedStudent = ref(null);
const confirmModal = ref(null);

const filteredAvailableStudents = computed(() => {
  if (!searchQuery.value) {
    return availableStudents.value;
  }
  const query = searchQuery.value.toLowerCase();
  return availableStudents.value.filter(student =>
    student.name.toLowerCase().includes(query) ||
    student.email.toLowerCase().includes(query)
  );
});

const loadData = async () => {
  try {
    loading.value = true;

    course.value = await courseStore.fetchCourse(route.params.id);
    enrolledStudents.value = await courseStore.getCourseStudents(route.params.id);

    const response = await axios.get('/api/users', {
      params: { role: 'student' }
    });

    const enrolledIds = enrolledStudents.value.map(s => s.id);
    availableStudents.value = response.data.users.filter(
      student => !enrolledIds.includes(student.id)
    );

  } catch (error) {
    console.error('Error loading data:', error);
    errorMessage.value = 'Error loading data';
  } finally {
    loading.value = false;
  }
};

const searchStudents = () => {};

const enrollSelectedStudents = async () => {
  if (selectedStudents.value.length === 0) return;

  try {
    enrolling.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    const result = await courseStore.enrollStudents(route.params.id, selectedStudents.value);

    successMessage.value = `${result.enrolled.length} student(s) enrolled successfully`;

    if (result.already_enrolled.length > 0) {
      successMessage.value += `. ${result.already_enrolled.length} already enrolled`;
    }

    selectedStudents.value = [];
    searchQuery.value = '';

    await loadData();

  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error enrolling students';
  } finally {
    enrolling.value = false;
  }
};

const confirmUnenroll = (student) => {
  selectedStudent.value = student;
  confirmModal.value.showModal();
};

const unenrollStudent = async () => {
  try {
    unenrolling.value = true;
    await courseStore.unenrollStudent(route.params.id, selectedStudent.value.id);

    successMessage.value = `${selectedStudent.value.name} removed successfully`;

    closeModal();
    await loadData();
  } catch (error) {
    errorMessage.value = 'Error removing student';
  } finally {
    unenrolling.value = false;
  }
};

const closeModal = () => {
  confirmModal.value.close();
  selectedStudent.value = null;
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

onMounted(() => {
  loadData();
});
</script>
