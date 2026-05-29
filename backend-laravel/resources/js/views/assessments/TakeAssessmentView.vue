<template>
  <AppLayout>
    <div class="container mx-auto max-w-4xl">
      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-8">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Assessment -->
      <div v-else-if="assessment">
        <!-- Header -->
        <div class="card bg-base-100 shadow-xl mb-6">
          <div class="card-body">
            <h1 class="text-3xl font-bold">{{ assessment.title }}</h1>
            <p class="text-gray-600">{{ assessment.description }}</p>

            <div class="flex gap-4 mt-4">
              <div class="stat bg-base-200 rounded-lg">
                <div class="stat-title">Questions</div>
                <div class="stat-value text-primary text-2xl">{{ assessment.questions?.length || 0 }}</div>
              </div>

              <div v-if="assessment.time_limit" class="stat bg-base-200 rounded-lg">
                <div class="stat-title">Time Limit</div>
                <div class="stat-value text-secondary text-2xl">{{ assessment.time_limit }} min</div>
              </div>

              <div v-if="timeRemaining !== null" class="stat bg-base-200 rounded-lg">
                <div class="stat-title">Time Remaining</div>
                <div class="stat-value text-2xl" :class="timeRemaining < 300 ? 'text-error' : 'text-info'">
                  {{ formatTime(timeRemaining) }}
                </div>
              </div>
            </div>

            <!-- Start button -->
            <div v-if="!hasStarted && !isCompleted" class="mt-6">
              <button @click="startAssessment" class="btn btn-primary btn-lg">
                Start Assessment
              </button>
            </div>

            <!-- Completed message -->
            <div v-if="isCompleted" class="alert alert-success mt-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <h3 class="font-bold">Assessment Completed!</h3>
                <div class="text-sm">You have successfully completed this assessment.</div>
                <div v-if="currentResponse && currentResponse.score !== null && currentResponse.score !== undefined" class="text-sm font-bold mt-2">
                  Score: {{ Number(currentResponse.score).toFixed(1) }}%
                </div>
                <div v-if="currentResponse && currentResponse.completed_at" class="text-sm mt-1">
                  Completed on: {{ formatDateTime(currentResponse.completed_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Questions -->
        <div v-if="hasStarted && !isCompleted">
          <form @submit.prevent="submitAssessment">
            <div class="space-y-6">
              <div v-for="(question, index) in assessment.questions" :key="question.id" class="card bg-base-100 shadow-xl">
                <div class="card-body">
                  <h3 class="font-bold text-lg">Question {{ index + 1 }}</h3>
                  <p class="mb-4">{{ question.question }}</p>

                  <!-- Multiple Choice -->
                  <div v-if="question.type === 'multiple_choice'" class="space-y-2">
                    <label
                      v-for="(option, optIndex) in question.options"
                      :key="optIndex"
                      class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-base-200"
                    >
                      <input
                        type="radio"
                        :name="`question-${question.id}`"
                        :value="optIndex"
                        v-model="responses[question.id]"
                        class="radio radio-primary"
                      />
                      <span>{{ option }}</span>
                    </label>
                  </div>

                  <!-- Text -->
                  <div v-else-if="question.type === 'text'">
                    <textarea
                      v-model="responses[question.id]"
                      class="textarea textarea-bordered w-full h-32"
                      placeholder="Your answer..."
                    ></textarea>
                  </div>

                  <!-- Scale (1-5) -->
                  <div v-else-if="question.type === 'scale'" class="flex justify-center gap-4">
                    <label v-for="n in 5" :key="n" class="flex flex-col items-center gap-2 cursor-pointer">
                      <input
                        type="radio"
                        :name="`question-${question.id}`"
                        :value="n"
                        v-model="responses[question.id]"
                        class="radio radio-primary"
                      />
                      <span class="text-sm">{{ n }}</span>
                    </label>
                  </div>

                  <!-- Likert (1-7) -->
                  <div v-else-if="question.type === 'likert'" class="flex justify-center gap-3">
                    <label v-for="n in 7" :key="n" class="flex flex-col items-center gap-2 cursor-pointer">
                      <input
                        type="radio"
                        :name="`question-${question.id}`"
                        :value="n"
                        v-model="responses[question.id]"
                        class="radio radio-primary"
                      />
                      <span class="text-xs">{{ n }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-between mt-8">
              <button
                type="button"
                @click="saveProgress"
                class="btn btn-outline"
                :disabled="saving"
              >
                <span v-if="saving" class="loading loading-spinner"></span>
                <span v-else>Save Progress</span>
              </button>

              <button
                type="submit"
                class="btn btn-primary"
                :disabled="submitting"
              >
                <span v-if="submitting" class="loading loading-spinner"></span>
                <span v-else>Submit Assessment</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Show responses if completed -->
        <div v-if="isCompleted && currentResponse && currentResponse.responses">
          <h2 class="text-2xl font-bold mb-4">Your Answers</h2>
          <div class="space-y-6">
            <div v-for="(question, index) in assessment.questions" :key="question.id" class="card bg-base-100 shadow-xl">
              <div class="card-body">
                <h3 class="font-bold text-lg">Question {{ index + 1 }}</h3>
                <p class="mb-4">{{ question.question }}</p>

                <!-- Multiple Choice -->
                <div v-if="question.type === 'multiple_choice'" class="space-y-2">
                  <div
                    v-for="(option, optIndex) in question.options"
                    :key="optIndex"
                    class="flex items-center gap-3 p-3 border rounded-lg"
                    :class="currentResponse.responses[question.id] == optIndex ? 'bg-primary/10 border-primary' : ''"
                  >
                    <input
                      type="radio"
                      :checked="currentResponse.responses[question.id] == optIndex"
                      disabled
                      class="radio radio-primary"
                    />
                    <span>{{ option }}</span>
                  </div>
                </div>

                <!-- Text -->
                <div v-else-if="question.type === 'text'">
                  <div class="p-4 bg-base-200 rounded-lg">
                    {{ currentResponse.responses[question.id] || 'No answer' }}
                  </div>
                </div>

                <!-- Scale (1-5) -->
                <div v-else-if="question.type === 'scale'" class="flex justify-center gap-4">
                  <label v-for="n in 5" :key="n" class="flex flex-col items-center gap-2">
                    <input
                      type="radio"
                      :checked="currentResponse.responses[question.id] == n"
                      disabled
                      class="radio radio-primary"
                    />
                    <span class="text-sm">{{ n }}</span>
                  </label>
                </div>

                <!-- Likert (1-7) -->
                <div v-else-if="question.type === 'likert'" class="flex justify-center gap-3">
                  <label v-for="n in 7" :key="n" class="flex flex-col items-center gap-2">
                    <input
                      type="radio"
                      :checked="currentResponse.responses[question.id] == n"
                      disabled
                      class="radio radio-primary"
                    />
                    <span class="text-xs">{{ n }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Back button if completed -->
        <div v-if="isCompleted" class="mt-6 flex gap-4">
          <router-link :to="`/student/courses/${route.params.courseId}/assessments`" class="btn btn-primary">
            Back to Assessments
          </router-link>
          <router-link to="/student/courses" class="btn btn-outline">
            Back to My Courses
          </router-link>
        </div>
      </div>
    </div>

    <!-- Confirmation modal -->
    <dialog ref="confirmModal" class="modal">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Submission</h3>
        <p class="py-4">Are you sure you want to submit your assessment? You will not be able to modify your answers afterwards.</p>
        <div class="modal-action">
          <button @click="closeModal" class="btn">Cancel</button>
          <button @click="confirmSubmit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="loading loading-spinner"></span>
            <span v-else>Confirm Submission</span>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAssessmentStore } from '../../stores/assessments';
import AppLayout from '../../components/layout/AppLayout.vue';

const route = useRoute();
const assessmentStore = useAssessmentStore();

const assessment = ref(null);
const currentResponse = ref(null);
const responses = ref({});
const loading = ref(true);
const saving = ref(false);
const submitting = ref(false);
const hasStarted = ref(false);
const startTime = ref(null);
const timeRemaining = ref(null);
const timerInterval = ref(null);
const confirmModal = ref(null);

const isCompleted = computed(() => {
  return currentResponse.value?.completed_at !== null && currentResponse.value?.completed_at !== undefined;
});

const startAssessment = async () => {
  try {
    const response = await assessmentStore.startAssessment(route.params.courseId, route.params.assessmentId);
    currentResponse.value = response;
    hasStarted.value = true;
    startTime.value = new Date(response.started_at);

    if (response.responses && Object.keys(response.responses).length > 0) {
      responses.value = { ...response.responses };
    }

    if (assessment.value.time_limit) {
      startTimer();
    }
  } catch (error) {
    console.error('Error starting assessment:', error);
  }
};

const startTimer = () => {
  const limitInSeconds = assessment.value.time_limit * 60;

  timerInterval.value = setInterval(() => {
    const elapsed = Math.floor((Date.now() - startTime.value.getTime()) / 1000);
    timeRemaining.value = Math.max(0, limitInSeconds - elapsed);

    if (timeRemaining.value === 0) {
      clearInterval(timerInterval.value);
      confirmSubmit();
    }
  }, 1000);
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
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

const saveProgress = async () => {
  try {
    saving.value = true;
    await assessmentStore.saveResponse(route.params.courseId, route.params.assessmentId, responses.value, false);
  } catch (error) {
    console.error('Error saving progress:', error);
  } finally {
    saving.value = false;
  }
};

const submitAssessment = () => {
  confirmModal.value.showModal();
};

const confirmSubmit = async () => {
  try {
    submitting.value = true;
    const response = await assessmentStore.saveResponse(
      route.params.courseId,
      route.params.assessmentId,
      responses.value,
      true
    );
    currentResponse.value = response;

    if (timerInterval.value) {
      clearInterval(timerInterval.value);
    }

    closeModal();
  } catch (error) {
    console.error('Error submitting assessment:', error);
  } finally {
    submitting.value = false;
  }
};

const closeModal = () => {
  confirmModal.value.close();
};

onMounted(async () => {
  try {
    loading.value = true;

    assessmentStore.clearCurrentResponse();
    currentResponse.value = null;
    responses.value = {};
    hasStarted.value = false;

    assessment.value = await assessmentStore.fetchAssessment(route.params.courseId, route.params.assessmentId);

    try {
      const existingResponse = await assessmentStore.getMyResponse(route.params.courseId, route.params.assessmentId);
      currentResponse.value = existingResponse;

      if (existingResponse.completed_at) {
        hasStarted.value = false;
        if (existingResponse.responses) {
          responses.value = { ...existingResponse.responses };
        }
      } else {
        hasStarted.value = true;
        startTime.value = new Date(existingResponse.started_at);
        responses.value = { ...existingResponse.responses };

        if (assessment.value.time_limit) {
          startTimer();
        }
      }
    } catch (err) {
      hasStarted.value = false;
    }
  } catch (error) {
    console.error('Error loading assessment:', error);
  } finally {
    loading.value = false;
  }
});

onUnmounted(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }
});
</script>
