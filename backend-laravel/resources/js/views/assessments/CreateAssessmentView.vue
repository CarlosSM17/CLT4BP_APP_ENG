<template>
  <AppLayout>
    <div class="container mx-auto max-w-4xl">
      <div class="flex items-center mb-8">
        <router-link :to="`/instructor/courses/${route.params.courseId}/assessments`" class="btn btn-ghost btn-circle mr-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        <h1 class="text-4xl font-bold">Create Assessment</h1>
      </div>

      <!-- Option to create from template -->
      <div v-if="availableTemplates.length > 0" class="card bg-primary/10 shadow-xl mb-6">
        <div class="card-body">
          <h2 class="card-title text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Available Templates
          </h2>
          <p class="text-sm mb-4">
            You can quickly create an assessment using one of the predefined templates with all questions already configured.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="template in availableTemplates"
              :key="template.id"
              class="card bg-base-100 cursor-pointer hover:shadow-lg transition-shadow"
              @click="createFromTemplate(template)"
            >
              <div class="card-body p-4">
                <h3 class="font-bold">{{ template.title }}</h3>
                <p class="text-sm text-gray-600">{{ template.description }}</p>
                <div class="flex gap-2 mt-2">
                  <span class="badge badge-primary">{{ getTemplateTypeLabel(template.assessment_type) }}</span>
                  <span class="badge badge-ghost">{{ template.time_limit ? template.time_limit + ' min' : 'No limit' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="divider" v-if="availableTemplates.length > 0">Or create manually</div>

      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <form @submit.prevent="handleSubmit">
            <div v-if="error" class="alert alert-error mb-4">
              <span>{{ error }}</span>
            </div>

            <!-- Assessment Type -->
            <div class="form-control">
              <label class="label">
                <span class="label-text font-bold">Assessment Type *</span>
              </label>
              <select v-model="form.assessment_type" class="select select-bordered" required @change="onTypeChange">
                <option value="">Select type...</option>
                <option value="prior_knowledge">Prior Knowledge</option>
                <option value="recall_initial">Initial Recall</option>
                <option value="comprehension_initial">Initial Comprehension</option>
                <option value="mslq_motivation_initial">MSLQ Initial Motivation</option>
                <option value="mslq_strategies">MSLQ Strategies</option>
                <option value="recall_final">Final Recall</option>
                <option value="comprehension_final">Final Comprehension</option>
                <option value="cognitive_load">Cognitive Load</option>
                <option value="mslq_motivation_final">MSLQ Final Motivation</option>
                <option value="course_interest">Course Interest (CIS)</option>
                <option value="imms">Motivational Materials (IMMS)</option>
              </select>
            </div>

            <!-- Template available alert -->
            <div v-if="templateForType" class="alert alert-info mt-4">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div>
                <h3 class="font-bold">Template available</h3>
                <div class="text-sm">
                  A template with {{ templateForType.questions?.length || 'all' }} predefined questions exists for this assessment type.
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-primary" @click="loadTemplateQuestions">
                Load Questions
              </button>
            </div>

            <!-- Title -->
            <div class="form-control">
              <label class="label">
                <span class="label-text font-bold">Title *</span>
              </label>
              <input
                v-model="form.title"
                type="text"
                placeholder="e.g. Prior Knowledge Assessment"
                class="input input-bordered"
                required
              />
            </div>

            <!-- Description -->
            <div class="form-control">
              <label class="label">
                <span class="label-text font-bold">Description</span>
              </label>
              <textarea
                v-model="form.description"
                placeholder="Assessment description..."
                class="textarea textarea-bordered h-24"
              ></textarea>
            </div>

            <!-- Time Limit -->
            <div class="form-control">
              <label class="label">
                <span class="label-text font-bold">Time Limit (minutes)</span>
              </label>
              <input
                v-model.number="form.time_limit"
                type="number"
                placeholder="Leave empty for no limit"
                class="input input-bordered"
                min="1"
              />
            </div>

            <!-- Questions -->
            <div class="form-control">
              <label class="label">
                <span class="label-text font-bold">Questions * ({{ form.questions.length }} questions)</span>
              </label>

              <!-- Summary of questions loaded from template -->
              <div v-if="questionsLoadedFromTemplate" class="alert alert-success mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ form.questions.length }} questions loaded from template. You can edit them if needed.</span>
                <button type="button" class="btn btn-sm btn-ghost" @click="toggleQuestionsVisibility">
                  {{ showQuestions ? 'Hide' : 'Show' }} Questions
                </button>
              </div>

              <div v-show="!questionsLoadedFromTemplate || showQuestions" class="space-y-4">
                <div v-for="(question, index) in form.questions" :key="question.id || index" class="card bg-base-200">
                  <div class="card-body">
                    <div class="flex justify-between items-start">
                      <h3 class="font-bold">Question {{ index + 1 }}</h3>
                      <button
                        type="button"
                        @click="removeQuestion(index)"
                        class="btn btn-error btn-sm btn-circle"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>

                    <!-- Dimension (MSLQ only) -->
                    <div v-if="question.dimension" class="badge badge-outline mb-2">
                      {{ formatDimension(question.dimension) }}
                    </div>

                    <!-- Question Type -->
                    <div class="form-control">
                      <label class="label">
                        <span class="label-text">Question Type</span>
                      </label>
                      <select v-model="question.type" class="select select-bordered select-sm">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="text">Open Answer</option>
                        <option value="scale">Scale (1-5)</option>
                        <option value="likert">Likert (1-7)</option>
                      </select>
                    </div>

                    <!-- Question Text -->
                    <div class="form-control">
                      <label class="label">
                        <span class="label-text">Question</span>
                      </label>
                      <textarea
                        v-model="question.question"
                        class="textarea textarea-bordered textarea-sm"
                        required
                      ></textarea>
                    </div>

                    <!-- Options (multiple_choice only) -->
                    <div v-if="question.type === 'multiple_choice'" class="form-control">
                      <label class="label">
                        <span class="label-text">Options</span>
                      </label>
                      <div class="space-y-2">
                        <div v-for="(_, optIndex) in question.options" :key="optIndex" class="flex gap-2">
                          <input
                            v-model="question.options[optIndex]"
                            type="text"
                            placeholder="Option..."
                            class="input input-bordered input-sm flex-1"
                          />
                          <button
                            type="button"
                            @click="removeOption(index, optIndex)"
                            class="btn btn-error btn-sm btn-square"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </button>
                        </div>
                        <button
                          type="button"
                          @click="addOption(index)"
                          class="btn btn-outline btn-sm"
                        >
                          Add Option
                        </button>
                      </div>
                    </div>

                    <!-- Likert options (read-only) -->
                    <div v-if="question.type === 'likert' && question.options" class="form-control">
                      <label class="label">
                        <span class="label-text">Likert Scale</span>
                      </label>
                      <div class="flex flex-wrap gap-2">
                        <span v-for="opt in question.options" :key="opt.value" class="badge badge-outline">
                          {{ opt.value }}: {{ opt.label || '-' }}
                        </span>
                      </div>
                    </div>

                    <!-- Correct Answer (optional for multiple_choice) -->
                    <div v-if="question.type === 'multiple_choice' && Array.isArray(question.options)" class="form-control">
                      <label class="label">
                        <span class="label-text">Correct Answer (optional for auto-grading)</span>
                      </label>
                      <select v-model="question.correct_answer" class="select select-bordered select-sm">
                        <option value="">No correct answer defined</option>
                        <option v-for="(opt, i) in question.options" :key="i" :value="i">
                          Option {{ i + 1 }}: {{ typeof opt === 'string' ? opt : opt.label }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

                <button type="button" @click="addQuestion" class="btn btn-outline w-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add Question
                </button>
              </div>
            </div>

            <!-- Initial Status -->
            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text font-bold">Activate assessment immediately</span>
                <input v-model="form.is_active" type="checkbox" class="checkbox checkbox-primary" />
              </label>
            </div>

            <div class="alert alert-info mt-6">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div>
                <h3 class="font-bold">Information</h3>
                <div class="text-xs">
                  Inactive assessments will not be visible to students until you activate them.
                </div>
              </div>
            </div>

            <div class="divider"></div>

            <div class="card-actions justify-end">
              <router-link :to="`/instructor/courses/${route.params.courseId}/assessments`" class="btn btn-outline">
                Cancel
              </router-link>
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="assessmentStore.loading || form.questions.length === 0"
              >
                <span v-if="assessmentStore.loading" class="loading loading-spinner"></span>
                <span v-else>Create Assessment</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAssessmentStore } from '../../stores/assessments';
import AppLayout from '../../components/layout/AppLayout.vue';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const assessmentStore = useAssessmentStore();

const form = ref({
  assessment_type: '',
  title: '',
  description: '',
  time_limit: null,
  questions: [],
  is_active: false,
});

const error = ref(null);
const availableTemplates = ref([]);
const allTemplates = ref([]);
const questionsLoadedFromTemplate = ref(false);
const showQuestions = ref(false);

onMounted(async () => {
  try {
    const response = await axios.get(
      `/api/courses/${route.params.courseId}/available-templates`
    );
    availableTemplates.value = response.data.available_templates || [];

    const allResponse = await axios.get('/api/assessment-templates');
    allTemplates.value = allResponse.data.templates || [];
  } catch (err) {
    console.error('Error loading templates:', err);
  }
});

const templateForType = computed(() => {
  if (!form.value.assessment_type) return null;
  return allTemplates.value.find(t => t.assessment_type === form.value.assessment_type);
});

const getTemplateTypeLabel = (type) => {
  const labels = {
    'mslq_motivation_initial': 'MSLQ Motivation',
    'mslq_strategies': 'MSLQ Strategies',
    'prior_knowledge': 'Prior Knowledge',
    'cognitive_load': 'Cognitive Load',
  };
  return labels[type] || type;
};

const formatDimension = (dimension) => {
  const translations = {
    'intrinsic_goal_orientation': 'Intrinsic goal orientation',
    'extrinsic_goal_orientation': 'Extrinsic goal orientation',
    'task_value': 'Task value',
    'control_beliefs': 'Control beliefs',
    'self_efficacy': 'Self-efficacy',
    'test_anxiety': 'Test anxiety',
    'rehearsal': 'Rehearsal',
    'elaboration': 'Elaboration',
    'organization': 'Organization',
    'critical_thinking': 'Critical thinking',
    'metacognitive_self_regulation': 'Metacognitive self-regulation',
    'time_management': 'Time management',
    'effort_regulation': 'Effort regulation',
    'peer_learning': 'Peer learning',
    'help_seeking': 'Help seeking'
  };
  return translations[dimension] || dimension;
};

const onTypeChange = () => {
  if (questionsLoadedFromTemplate.value) {
    questionsLoadedFromTemplate.value = false;
    form.value.questions = [];
  }
};

const loadTemplateQuestions = async () => {
  if (!templateForType.value) return;

  try {
    const response = await axios.get(
      `/api/assessment-templates/${templateForType.value.id}`
    );

    const template = response.data.template;

    form.value.title = template.title;
    form.value.description = template.description || '';
    form.value.time_limit = template.time_limit;

    form.value.questions = (template.questions || []).map(q => ({
      ...q,
      id: q.id || Date.now().toString() + Math.random().toString(36).substring(2, 11)
    }));

    questionsLoadedFromTemplate.value = true;
    showQuestions.value = false;
  } catch (err) {
    console.error('Error loading template:', err);
    error.value = 'Error loading template questions';
  }
};

const createFromTemplate = async (template) => {
  try {
    await axios.post(
      `/api/courses/${route.params.courseId}/assessments/from-template/${template.id}`
    );
    router.push(`/instructor/courses/${route.params.courseId}/assessments`);
  } catch (err) {
    if (err.response?.status === 409) {
      error.value = err.response.data.message || 'An assessment of this type already exists in this course';
    } else {
      error.value = err.response?.data?.message || 'Error creating assessment from template';
    }
  }
};

const toggleQuestionsVisibility = () => {
  showQuestions.value = !showQuestions.value;
};

const addQuestion = () => {
  form.value.questions.push({
    id: Date.now().toString(),
    type: 'multiple_choice',
    question: '',
    options: [''],
    correct_answer: '',
  });
};

const removeQuestion = (index) => {
  form.value.questions.splice(index, 1);
};

const addOption = (questionIndex) => {
  form.value.questions[questionIndex].options.push('');
};

const removeOption = (questionIndex, optionIndex) => {
  form.value.questions[questionIndex].options.splice(optionIndex, 1);
};

const handleSubmit = async () => {
  error.value = null;

  if (form.value.questions.length === 0) {
    error.value = 'You must add at least one question';
    return;
  }

  try {
    await assessmentStore.createAssessment(route.params.courseId, form.value);
    router.push(`/instructor/courses/${route.params.courseId}/assessments`);
  } catch (err) {
    error.value = err.response?.data?.message || 'Error creating assessment';
  }
};
</script>
