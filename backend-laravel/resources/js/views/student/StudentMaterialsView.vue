<template>
  <AppLayout>
    <div class="container mx-auto">
      <!-- Header -->
      <div class="flex items-center mb-8">
        <router-link :to="`/student/courses/${route.params.courseId}/assessments`" class="btn btn-ghost btn-circle mr-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        <div>
          <h1 class="text-3xl font-bold">Study Materials</h1>
          <p class="text-gray-600">{{ course?.title }}</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-16">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="alert alert-error shadow-lg mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="font-bold">Error</h3>
          <div class="text-sm">{{ error }}</div>
        </div>
        <router-link to="/student/courses" class="btn btn-sm">Back to My Courses</router-link>
      </div>

      <!-- Materials grouped by type -->
      <div v-else-if="materials.length > 0" class="space-y-8">
        <div v-for="(group, type) in materialsByType" :key="type">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <span class="badge" :class="getMaterialTypeBadge(type)">
              {{ getMaterialTypeLabel(type) }}
            </span>
            <span class="text-sm text-gray-500 font-normal">({{ group.length }})</span>
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="material in group"
              :key="material.id"
              class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow cursor-pointer"
              @click="openMaterial(material)"
            >
              <div class="card-body">
                <div class="flex justify-between items-start">
                  <span class="badge badge-sm" :class="getMaterialTypeBadge(material.material_type)">
                    {{ getMaterialTypeLabel(material.material_type) }}
                  </span>
                  <span class="badge badge-sm badge-ghost">
                    {{ material.target_type === 'group' ? 'Group' : 'Individual' }}
                  </span>
                </div>

                <div v-if="material.material_type === 'support_info' && material.timer_seconds" class="text-sm text-gray-500 mt-2">
                  Estimated time: {{ Math.round(material.timer_seconds / 60) }} min
                </div>

                <div v-if="material.total_time_spent > 0" class="text-xs text-gray-400 mt-1">
                  Study time: {{ Math.round(material.total_time_spent / 60) }} min
                </div>

                <div class="card-actions justify-end mt-3">
                  <button class="btn btn-primary btn-sm">View Material</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="text-xl font-bold mt-4">No materials available</h3>
        <p class="text-gray-600 mt-2">The instructor has not activated any materials for this course yet</p>
      </div>

      <!-- Material viewer modal -->
      <dialog ref="materialModal" class="modal">
        <div class="modal-box max-w-4xl max-h-[85vh]">
          <h3 class="font-bold text-lg" v-if="selectedMaterial">
            {{ getMaterialTypeLabel(selectedMaterial.material_type) }}
            <span class="badge badge-sm badge-ghost ml-2">
              {{ selectedMaterial.target_type === 'group' ? 'Group' : 'Individual' }}
            </span>
          </h3>

          <!-- Timer countdown for support_info -->
          <div
            v-if="selectedMaterial?.material_type === 'support_info' && selectedMaterial?.timer_seconds"
            class="alert mt-3"
            :class="timerRemaining > 60 ? 'alert-info' : timerRemaining > 0 ? 'alert-warning' : 'alert-error'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-mono text-lg">{{ formatTimer(timerRemaining) }}</span>
            <span v-if="timerRemaining <= 0" class="font-semibold">Time expired</span>
          </div>

          <!-- Content rendering based on material type -->
          <div class="py-4 overflow-auto max-h-[60vh]" v-if="selectedMaterial">

            <!-- Example type -->
            <div v-if="selectedMaterial.material_type === 'example'">
              <h4 class="text-lg font-bold">{{ selectedMaterial.content?.example_title }}</h4>
              <p class="mt-2">{{ selectedMaterial.content?.description }}</p>
              <div v-if="selectedMaterial.content?.use_case" class="mt-2 text-sm text-gray-600">
                <span class="font-semibold">Use case:</span> {{ selectedMaterial.content.use_case }}
              </div>
              <div v-if="selectedMaterial.content?.code" class="bg-gray-900 text-green-400 p-4 rounded-lg mt-3 overflow-x-auto">
                <pre class="text-sm font-mono whitespace-pre-wrap">{{ selectedMaterial.content.code }}</pre>
              </div>
              <div v-if="selectedMaterial.content?.explanation" class="mt-3">
                <h5 class="font-semibold">Explanation:</h5>
                <p class="mt-1">{{ selectedMaterial.content.explanation }}</p>
              </div>
              <div v-if="selectedMaterial.content?.key_concepts_demonstrated?.length" class="mt-3">
                <h5 class="font-semibold">Key Concepts:</h5>
                <ul class="list-disc ml-5 mt-1">
                  <li v-for="concept in selectedMaterial.content.key_concepts_demonstrated" :key="concept">{{ concept }}</li>
                </ul>
              </div>
              <div v-if="selectedMaterial.content?.common_mistakes?.length" class="mt-3">
                <h5 class="font-semibold">Common Mistakes:</h5>
                <ul class="list-disc ml-5 mt-1">
                  <li v-for="mistake in selectedMaterial.content.common_mistakes" :key="mistake">{{ mistake }}</li>
                </ul>
              </div>
            </div>

            <!-- Learning Tasks type -->
            <div v-else-if="selectedMaterial.material_type === 'learning_tasks'">
              <div v-for="(task, index) in (selectedMaterial.content?.tasks || [])" :key="index" class="collapse collapse-arrow bg-base-200 mb-2">
                <input type="checkbox" :checked="index === 0" />
                <div class="collapse-title font-medium">
                  <span class="badge badge-sm badge-primary mr-2">{{ task.difficulty_level || 'Task' }}</span>
                  {{ task.title || ('Task ' + (index + 1)) }}
                </div>
                <div class="collapse-content">
                  <p>{{ task.description }}</p>
                  <div v-if="task.context" class="mt-2 text-sm text-gray-600">
                    <span class="font-semibold">Context:</span> {{ task.context }}
                  </div>
                  <div v-if="task.why_relevant" class="mt-2 text-sm text-gray-600">
                    <span class="font-semibold">Relevance:</span> {{ task.why_relevant }}
                  </div>
                  <div v-if="task.expected_outcome" class="mt-2 text-sm">
                    <span class="font-semibold">Expected outcome:</span> {{ task.expected_outcome }}
                  </div>
                  <div v-if="task.estimated_time" class="badge badge-outline badge-sm mt-2">
                    {{ task.estimated_time }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Support Info type -->
            <div v-else-if="selectedMaterial.material_type === 'support_info'">
              <div v-for="section in (selectedMaterial.content?.sections || [])" :key="section.title" class="mb-6">
                <h5 class="font-semibold text-lg border-b pb-1 mb-2">{{ section.title }}</h5>
                <p class="whitespace-pre-wrap">{{ section.content }}</p>
                <div v-if="section.key_concepts?.length" class="mt-2">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="kc in section.key_concepts" :key="kc" class="badge badge-outline badge-sm">{{ kc }}</span>
                  </div>
                </div>
                <div v-if="section.examples?.length" class="mt-2 space-y-2">
                  <div v-for="(ex, i) in section.examples" :key="i" class="bg-base-200 p-3 rounded">
                    <p class="text-sm">{{ ex.description }}</p>
                    <pre v-if="ex.code" class="bg-gray-900 text-green-400 p-2 rounded mt-1 text-xs font-mono whitespace-pre-wrap">{{ ex.code }}</pre>
                  </div>
                </div>
              </div>
              <div v-if="selectedMaterial.content?.summary" class="alert alert-info mt-4">
                <div><span class="font-bold">Summary:</span> {{ selectedMaterial.content.summary }}</div>
              </div>
              <div v-if="selectedMaterial.content?.key_takeaways?.length" class="mt-3">
                <h5 class="font-semibold">Key Takeaways:</h5>
                <ul class="list-disc ml-5 mt-1">
                  <li v-for="t in selectedMaterial.content.key_takeaways" :key="t">{{ t }}</li>
                </ul>
              </div>
            </div>

            <!-- Procedural Info type -->
            <div v-else-if="selectedMaterial.material_type === 'procedural_info'">
              <div v-for="(example, idx) in (selectedMaterial.content?.worked_examples || [])" :key="idx" class="card bg-base-200 mb-4">
                <div class="card-body">
                  <h5 class="font-semibold text-lg">{{ example.title || ('Example ' + (idx + 1)) }}</h5>
                  <p v-if="example.problem" class="mt-1">{{ example.problem }}</p>
                  <div v-if="example.solution_steps?.length" class="mt-3 space-y-3">
                    <div v-for="(step, si) in example.solution_steps" :key="si" class="ml-4 border-l-2 border-primary pl-3">
                      <span class="badge badge-sm badge-primary">Step {{ step.step_number || (si + 1) }}</span>
                      <p class="mt-1 font-medium">{{ step.description }}</p>
                      <p v-if="step.explanation" class="text-sm text-gray-600 mt-1">{{ step.explanation }}</p>
                      <pre v-if="step.code" class="bg-gray-900 text-green-400 p-2 rounded mt-1 text-xs font-mono whitespace-pre-wrap">{{ step.code }}</pre>
                    </div>
                  </div>
                  <div v-if="example.key_insights?.length" class="mt-3">
                    <h6 class="font-semibold text-sm">Insights:</h6>
                    <ul class="list-disc ml-5 text-sm">
                      <li v-for="insight in example.key_insights" :key="insight">{{ insight }}</li>
                    </ul>
                  </div>
                </div>
              </div>
              <div v-if="selectedMaterial.content?.guiding_questions?.length" class="mt-4">
                <h5 class="font-semibold">Guiding Questions:</h5>
                <ul class="list-decimal ml-5 mt-1">
                  <li v-for="q in selectedMaterial.content.guiding_questions" :key="q">{{ q }}</li>
                </ul>
              </div>
            </div>

            <!-- Verbal Protocols type -->
            <div v-else-if="selectedMaterial.material_type === 'verbal_protocols'">
              <h4 class="text-lg font-bold">{{ selectedMaterial.content?.protocol_title }}</h4>
              <p class="mt-2 italic bg-base-200 p-3 rounded">{{ selectedMaterial.content?.scenario }}</p>
              <div v-if="selectedMaterial.content?.think_aloud_transcript" class="bg-base-200 p-4 rounded-lg mt-3">
                <h5 class="font-semibold mb-2">Think-Aloud Transcript:</h5>
                <p class="whitespace-pre-wrap text-sm">{{ selectedMaterial.content.think_aloud_transcript }}</p>
              </div>
              <div v-if="selectedMaterial.content?.key_thinking_patterns?.length" class="mt-3">
                <h5 class="font-semibold">Thinking Patterns:</h5>
                <ul class="list-disc ml-5 mt-1">
                  <li v-for="pattern in selectedMaterial.content.key_thinking_patterns" :key="pattern">{{ pattern }}</li>
                </ul>
              </div>
              <div v-if="selectedMaterial.content?.teaching_points?.length" class="mt-3">
                <h5 class="font-semibold">Teaching Points:</h5>
                <ul class="list-disc ml-5 mt-1">
                  <li v-for="point in selectedMaterial.content.teaching_points" :key="point">{{ point }}</li>
                </ul>
              </div>
            </div>

            <!-- Fallback: raw JSON -->
            <div v-else>
              <pre class="whitespace-pre-wrap text-sm bg-base-200 p-4 rounded">{{ JSON.stringify(selectedMaterial.content, null, 2) }}</pre>
            </div>
          </div>

          <div class="modal-action">
            <form method="dialog">
              <button class="btn" @click="closeMaterial">Close</button>
            </form>
          </div>
        </div>
      </dialog>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '../../components/layout/AppLayout.vue';
import api from '../../services/api';

const route = useRoute();
const courseId = computed(() => route.params.courseId);

const loading = ref(true);
const error = ref(null);
const course = ref(null);
const materials = ref([]);
const selectedMaterial = ref(null);
const materialModal = ref(null);
const currentAccessLogId = ref(null);
const accessStartTime = ref(null);

const timerRemaining = ref(0);
let timerInterval = null;

const materialsByType = computed(() => {
  const grouped = {};
  const typeOrder = ['example', 'support_info', 'learning_tasks', 'procedural_info', 'verbal_protocols'];
  materials.value.forEach(m => {
    if (!grouped[m.material_type]) grouped[m.material_type] = [];
    grouped[m.material_type].push(m);
  });
  const ordered = {};
  typeOrder.forEach(type => {
    if (grouped[type]) ordered[type] = grouped[type];
  });
  return ordered;
});

const getMaterialTypeLabel = (type) => {
  const labels = {
    'example': 'Example',
    'learning_tasks': 'Learning Tasks',
    'support_info': 'Support Information',
    'procedural_info': 'Procedural Information',
    'verbal_protocols': 'Verbal Protocols'
  };
  return labels[type] || type;
};

const getMaterialTypeBadge = (type) => {
  const badges = {
    'example': 'badge-primary',
    'learning_tasks': 'badge-secondary',
    'support_info': 'badge-accent',
    'procedural_info': 'badge-info',
    'verbal_protocols': 'badge-warning'
  };
  return badges[type] || 'badge-ghost';
};

const formatTimer = (seconds) => {
  if (seconds <= 0) return '00:00';
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
};

const openMaterial = async (material) => {
  selectedMaterial.value = material;
  accessStartTime.value = Date.now();

  try {
    const response = await api.post(
      `/courses/${courseId.value}/materials/${material.id}/access/start`
    );
    currentAccessLogId.value = response.data.data.id;
  } catch (err) {
    console.error('Error logging access:', err);
  }

  if (material.material_type === 'support_info' && material.timer_seconds) {
    timerRemaining.value = material.timer_seconds;
    timerInterval = setInterval(() => {
      timerRemaining.value--;
      if (timerRemaining.value <= 0) {
        clearInterval(timerInterval);
        timerInterval = null;
      }
    }, 1000);
  }

  materialModal.value?.showModal();
};

const closeMaterial = async () => {
  if (timerInterval) {
    clearInterval(timerInterval);
    timerInterval = null;
  }

  if (currentAccessLogId.value && accessStartTime.value) {
    const timeSpent = Math.round((Date.now() - accessStartTime.value) / 1000);
    try {
      await api.post(
        `/courses/${courseId.value}/materials/${selectedMaterial.value.id}/access/complete`,
        {
          log_id: currentAccessLogId.value,
          time_spent_seconds: Math.max(timeSpent, 1)
        }
      );
    } catch (err) {
      console.error('Error logging access complete:', err);
    }
  }

  currentAccessLogId.value = null;
  accessStartTime.value = null;
  selectedMaterial.value = null;
};

const fetchData = async () => {
  try {
    loading.value = true;
    error.value = null;

    const courseRes = await api.get(`/courses/${courseId.value}`);
    course.value = courseRes.data.course || courseRes.data.data;

    const materialsRes = await api.get(`/courses/${courseId.value}/materials/student/list`);
    materials.value = materialsRes.data.data || [];
  } catch (err) {
    console.error('Error loading data:', err);
    if (err.response?.status === 403) {
      error.value = 'You do not have access to this course. Make sure you are enrolled.';
    } else {
      error.value = 'Error loading course materials.';
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => fetchData());
onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});
</script>
