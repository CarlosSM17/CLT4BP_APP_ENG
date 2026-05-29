<template>
  <AppLayout>
    <div class="container mx-auto">
      <div class="flex items-center mb-8">
        <router-link :to="`/instructor/courses/${route.params.courseId}`" class="btn btn-ghost btn-circle mr-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        <div>
          <h1 class="text-4xl font-bold">Assessments</h1>
          <p class="text-gray-600">{{ course?.title }}</p>
        </div>
      </div>

      <div class="flex justify-end mb-6">
        <router-link :to="`/instructor/courses/${route.params.courseId}/assessments/create`" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Assessment
        </router-link>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-8">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Assessment list -->
      <div v-else-if="assessmentStore.assessments.length > 0" class="grid grid-cols-1 gap-6">
        <div v-for="assessment in assessmentStore.assessments" :key="assessment.id" class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h2 class="card-title">{{ assessment.title }}</h2>
                <p class="text-sm text-gray-600">{{ assessment.description }}</p>

                <div class="flex gap-2 mt-3">
                  <span class="badge" :class="getTypeBadge(assessment.assessment_type)">
                    {{ getTypeLabel(assessment.assessment_type) }}
                  </span>
                  <span class="badge" :class="assessment.is_active ? 'badge-success' : 'badge-ghost'">
                    {{ assessment.is_active ? 'Active' : 'Inactive' }}
                  </span>
                  <span class="badge badge-outline">
                    {{ assessment.questions?.length || 0 }} questions
                  </span>
                  <span v-if="assessment.time_limit" class="badge badge-outline">
                    {{ assessment.time_limit }} minutes
                  </span>
                </div>

                <div class="mt-3">
                  <div class="text-sm">
                    <span class="font-semibold">Completion rate:</span>
                    {{ assessment.completion_rate?.toFixed(1) || 0 }}%
                  </div>
                  <progress class="progress progress-primary w-full mt-1" :value="assessment.completion_rate || 0" max="100"></progress>
                </div>
              </div>

              <div class="flex flex-col gap-2">
                <button
                  @click="toggleActive(assessment)"
                  :class="assessment.is_active ? 'btn btn-sm btn-warning' : 'btn btn-sm btn-success'"
                >
                  {{ assessment.is_active ? 'Deactivate' : 'Activate' }}
                </button>

                <router-link
                  :to="`/instructor/courses/${route.params.courseId}/assessments/${assessment.id}/responses`"
                  class="btn btn-sm btn-info"
                >
                  View Responses
                </router-link>

                <router-link
                  :to="`/instructor/courses/${route.params.courseId}/assessments/${assessment.id}/edit`"
                  class="btn btn-sm btn-outline"
                >
                  Edit
                </router-link>

                <button
                  @click="confirmDelete(assessment)"
                  class="btn btn-sm btn-error"
                >
                  Delete
                </button>
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
        <h3 class="text-xl font-bold mt-4">No assessments</h3>
        <p class="text-gray-600 mt-2">Create your first assessment for this course</p>
        <router-link :to="`/instructor/courses/${route.params.courseId}/assessments/create`" class="btn btn-primary mt-4">
          Create Assessment
        </router-link>
      </div>
    </div>

    <!-- Confirmation modal -->
    <dialog ref="confirmModal" class="modal">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Deletion</h3>
        <p class="py-4">Are you sure you want to delete the assessment <strong>{{ selectedAssessment?.title }}</strong>?</p>
        <p class="text-sm text-error">This action will also delete all student responses.</p>
        <div class="modal-action">
          <button @click="closeModal" class="btn">Cancel</button>
          <button @click="deleteAssessment" class="btn btn-error" :disabled="deleting">
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
import { useRoute } from 'vue-router';
import { useAssessmentStore } from '../../stores/assessments';
import { useCourseStore } from '../../stores/courses';
import AppLayout from '../../components/layout/AppLayout.vue';

const route = useRoute();
const assessmentStore = useAssessmentStore();
const courseStore = useCourseStore();

const course = ref(null);
const loading = ref(true);
const selectedAssessment = ref(null);
const deleting = ref(false);
const confirmModal = ref(null);

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

const getTypeBadge = (type) => {
  if (type.includes('initial')) return 'badge-info';
  if (type.includes('final')) return 'badge-success';
  if (type.includes('mslq')) return 'badge-warning';
  return 'badge-ghost';
};

const toggleActive = async (assessment) => {
  try {
    await assessmentStore.toggleActive(route.params.courseId, assessment.id);
  } catch (error) {
    console.error('Error toggling status:', error);
  }
};

const confirmDelete = (assessment) => {
  selectedAssessment.value = assessment;
  confirmModal.value.showModal();
};

const deleteAssessment = async () => {
  try {
    deleting.value = true;
    await assessmentStore.deleteAssessment(route.params.courseId, selectedAssessment.value.id);
    closeModal();
  } catch (error) {
    console.error('Error deleting:', error);
  } finally {
    deleting.value = false;
  }
};

const closeModal = () => {
  confirmModal.value.close();
  selectedAssessment.value = null;
};

onMounted(async () => {
  try {
    loading.value = true;
    course.value = await courseStore.fetchCourse(route.params.courseId);
    await assessmentStore.fetchAssessments(route.params.courseId);
  } catch (error) {
    console.error('Error loading data:', error);
  } finally {
    loading.value = false;
  }
});
</script>
