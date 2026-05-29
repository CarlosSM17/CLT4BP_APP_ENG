<template>
  <AppLayout>
    <div class="container mx-auto">
      <!-- Header -->
      <div class="flex items-center mb-8">
        <router-link :to="`/student/courses/${courseId}/assessments`" class="btn btn-ghost btn-circle mr-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        <div>
          <h1 class="text-3xl font-bold">My Learning Report</h1>
          <p class="text-gray-600 mt-1">Your cognitive profile and assessment results</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-16">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- No profile -->
      <div v-else-if="!data.has_profile" class="alert alert-info shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
          <h3 class="font-bold">Profile not generated</h3>
          <p class="text-sm">{{ data.message || 'Complete the initial assessments so the instructor can generate your profile.' }}</p>
        </div>
        <router-link :to="`/student/courses/${courseId}/assessments`" class="btn btn-sm">
          View Assessments
        </router-link>
      </div>

      <!-- Report content -->
      <div v-else class="space-y-6">
        <!-- General stats -->
        <div class="stats shadow w-full">
          <div class="stat">
            <div class="stat-title">Overall Motivation</div>
            <div class="stat-value text-sm">
              <span class="badge badge-lg capitalize" :class="levelBadgeClass(data.profile?.profile_summary?.overall_motivation)">
                {{ data.profile?.profile_summary?.overall_motivation || 'N/A' }}
              </span>
            </div>
          </div>
          <div class="stat">
            <div class="stat-title">Learning Strategies</div>
            <div class="stat-value text-sm">
              <span class="badge badge-lg capitalize" :class="levelBadgeClass(data.profile?.profile_summary?.overall_strategies)">
                {{ data.profile?.profile_summary?.overall_strategies || 'N/A' }}
              </span>
            </div>
          </div>
          <div class="stat">
            <div class="stat-title">Prior Knowledge</div>
            <div class="stat-value text-sm">
              <span class="badge badge-lg capitalize" :class="levelBadgeClass(data.profile?.profile_summary?.prior_knowledge)">
                {{ data.profile?.profile_summary?.prior_knowledge || 'N/A' }}
              </span>
            </div>
          </div>
        </div>

        <!-- MSLQ radar section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title text-lg">MSLQ Profile — You vs. the Group</h2>
              <p class="text-xs text-gray-500">Blue line = your scores / Gray line = group average (scale 1-7)</p>
              <div class="relative" style="height: 300px;">
                <canvas ref="radarRef"></canvas>
              </div>
            </div>
          </div>

          <!-- Prior knowledge -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title text-lg">Knowledge Assessment</h2>
              <div class="relative" style="height: 200px;">
                <canvas ref="knowledgeRef"></canvas>
              </div>
              <!-- Pre/post if available -->
              <div v-if="data.pre_post?.recall?.available || data.pre_post?.comprehension?.available" class="mt-4 space-y-2">
                <p class="text-sm font-semibold text-gray-600">Progress (Initial → Final):</p>
                <div v-if="data.pre_post?.recall?.available" class="flex items-center gap-2">
                  <span class="text-xs text-gray-500 w-24">Recall:</span>
                  <span class="text-sm">{{ data.pre_post.recall.initial?.toFixed(1) }}% → {{ data.pre_post.recall.final?.toFixed(1) }}%</span>
                  <span class="badge badge-xs" :class="data.pre_post.recall.change >= 0 ? 'badge-success' : 'badge-error'">
                    {{ data.pre_post.recall.change >= 0 ? '+' : '' }}{{ data.pre_post.recall.change?.toFixed(1) }}%
                  </span>
                </div>
                <div v-if="data.pre_post?.comprehension?.available" class="flex items-center gap-2">
                  <span class="text-xs text-gray-500 w-24">Comprehension:</span>
                  <span class="text-sm">{{ data.pre_post.comprehension.initial?.toFixed(1) }}% → {{ data.pre_post.comprehension.final?.toFixed(1) }}%</span>
                  <span class="badge badge-xs" :class="data.pre_post.comprehension.change >= 0 ? 'badge-success' : 'badge-error'">
                    {{ data.pre_post.comprehension.change >= 0 ? '+' : '' }}{{ data.pre_post.comprehension.change?.toFixed(1) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cognitive Load -->
        <div v-if="data.cognitive_load?.available" class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title text-lg">Perceived Cognitive Load</h2>
            <p class="text-xs text-gray-500">Scale 0-10</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
              <div v-for="(label, key) in cltLabels" :key="key" class="text-center">
                <div class="radial-progress text-2xl font-bold"
                  :style="`--value:${((data.cognitive_load.dimensions?.[key] ?? 0) / 10) * 100}; --size:5rem;`"
                  :class="getCltColor(key)">
                  {{ (data.cognitive_load.dimensions?.[key] ?? 0).toFixed(1) }}
                </div>
                <p class="text-sm mt-1">{{ label }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- CIS / IMMS -->
        <div v-if="data.course_interest?.available || data.imms?.available" class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title text-lg">Course Motivation & Interest</h2>
            <p class="text-xs text-gray-500 mb-3">ARCS dimensions (scale 1-5)</p>
            <div class="relative" style="height: 220px;">
              <canvas ref="arcsRef"></canvas>
            </div>
          </div>
        </div>

        <!-- Recommendations -->
        <div v-if="data.profile?.recommendations?.length" class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title text-lg">Personalized Recommendations</h2>
            <ul class="space-y-2 mt-2">
              <li v-for="(rec, i) in data.profile.recommendations" :key="i"
                  class="flex items-start gap-2 p-3 bg-base-200 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">{{ rec }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Detailed MSLQ table (collapsible) -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <div class="flex justify-between items-center cursor-pointer" @click="showMslqDetail = !showMslqDetail">
              <h2 class="card-title text-lg">Detailed MSLQ Scores</h2>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform" :class="showMslqDetail ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-show="showMslqDetail" class="overflow-x-auto mt-4">
              <table class="table table-zebra table-sm">
                <thead>
                  <tr><th>Dimension</th><th>Average (1-7)</th><th>Level</th></tr>
                </thead>
                <tbody>
                  <tr v-for="(val, dim) in data.profile?.mslq_scores" :key="dim">
                    <td>{{ mslqLabels[dim] || dim }}</td>
                    <td>{{ val.average?.toFixed(2) }}</td>
                    <td><span class="badge badge-sm" :class="levelBadgeClass(val.level)">{{ val.level }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { Chart, registerables } from 'chart.js';
import AppLayout from '../../components/layout/AppLayout.vue';
import api from '../../services/api';

Chart.register(...registerables);

const route = useRoute();
const courseId = computed(() => parseInt(route.params.courseId));

const loading  = ref(true);
const data     = ref({ has_profile: false });
const showMslqDetail = ref(false);

const radarRef    = ref(null);
const knowledgeRef = ref(null);
const arcsRef     = ref(null);

let charts = {};

const mslqLabels = {
  intrinsic_goal_orientation:      'Intrinsic Orientation',
  extrinsic_goal_orientation:      'Extrinsic Orientation',
  task_value:                      'Task Value',
  control_beliefs:                 'Control Beliefs',
  self_efficacy:                   'Self-Efficacy',
  test_anxiety:                    'Test Anxiety',
  rehearsal:                       'Rehearsal',
  elaboration:                     'Elaboration',
  organization:                    'Organization',
  critical_thinking:               'Critical Thinking',
  metacognitive_self_regulation:   'Metacognitive Self-Regulation',
  time_management:                 'Time Management',
  effort_regulation:               'Effort Regulation',
  peer_learning:                   'Peer Learning',
  help_seeking:                    'Help Seeking',
};

const cltLabels = {
  intrinsic_load:  'Intrinsic Load',
  extraneous_load: 'Extraneous Load',
  germane_load:    'Germane Load',
};

const arcsLabels = {
  attention:    'Attention',
  relevance:    'Relevance',
  confidence:   'Confidence',
  satisfaction: 'Satisfaction',
};

const levelBadgeClass = (level) => {
  if (level === 'alto')  return 'badge-success';
  if (level === 'medio') return 'badge-warning';
  if (level === 'bajo')  return 'badge-error';
  return 'badge-ghost';
};

const getCltColor = (dim) => {
  if (dim === 'intrinsic_load')  return 'text-primary';
  if (dim === 'extraneous_load') return 'text-error';
  if (dim === 'germane_load')    return 'text-success';
  return 'text-base-content';
};

const destroyAll = () => {
  Object.values(charts).forEach(c => c?.destroy());
  charts = {};
};

const initCharts = async () => {
  await nextTick();
  const profile      = data.value.profile ?? {};
  const groupAvg     = data.value.group_averages ?? {};
  const mslqScores   = profile.mslq_scores ?? {};
  const knowledge    = profile.knowledge ?? {};

  const mslqKeys = Object.keys(mslqLabels);
  if (radarRef.value) {
    charts.radar = new Chart(radarRef.value, {
      type: 'radar',
      data: {
        labels: mslqKeys.map(k => mslqLabels[k]),
        datasets: [
          {
            label: 'My Profile',
            data: mslqKeys.map(k => mslqScores[k]?.average ?? 0),
            backgroundColor: 'rgba(99,102,241,0.2)',
            borderColor: '#6366f1',
            pointBackgroundColor: '#6366f1',
          },
          {
            label: 'Group Average',
            data: mslqKeys.map(k => groupAvg[k]?.average ?? 0),
            backgroundColor: 'rgba(156,163,175,0.1)',
            borderColor: '#9ca3af',
            borderDash: [4, 4],
            pointBackgroundColor: '#9ca3af',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { r: { min: 0, max: 7, ticks: { stepSize: 1 } } },
      },
    });
  }

  const recall = data.value.pre_post?.recall?.initial ?? knowledge?.recall?.percentage ?? 0;
  const comp   = data.value.pre_post?.comprehension?.initial ?? knowledge?.comprehension?.percentage ?? 0;
  if (knowledgeRef.value) {
    charts.knowledge = new Chart(knowledgeRef.value, {
      type: 'bar',
      data: {
        labels: ['Recall', 'Comprehension'],
        datasets: [{
          label: 'Result (%)',
          data: [recall, comp],
          backgroundColor: ['rgba(99,102,241,0.7)', 'rgba(16,185,129,0.7)'],
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { min: 0, max: 100, ticks: { callback: v => v + '%' } } },
        plugins: { legend: { display: false } },
      },
    });
  }

  const hasCis  = data.value.course_interest?.available;
  const hasImms = data.value.imms?.available;
  if ((hasCis || hasImms) && arcsRef.value) {
    const arcsDims = Object.keys(arcsLabels);
    const datasets = [];
    if (hasCis) {
      datasets.push({
        label: 'CIS',
        data: arcsDims.map(d => data.value.course_interest.dimensions?.[d] ?? 0),
        backgroundColor: 'rgba(99,102,241,0.7)',
      });
    }
    if (hasImms) {
      datasets.push({
        label: 'IMMS',
        data: arcsDims.map(d => data.value.imms.dimensions?.[d] ?? 0),
        backgroundColor: 'rgba(16,185,129,0.7)',
      });
    }
    charts.arcs = new Chart(arcsRef.value, {
      type: 'bar',
      data: { labels: arcsDims.map(d => arcsLabels[d]), datasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { min: 1, max: 5 } },
      },
    });
  }
};

onMounted(async () => {
  try {
    const res = await api.get(`/courses/${courseId.value}/reports/my-report`);
    data.value = res.data;
  } catch (err) {
    console.error('Error loading report:', err);
  } finally {
    loading.value = false;
  }
  if (data.value.has_profile) {
    await initCharts();
  }
});

onUnmounted(() => {
  destroyAll();
});
</script>
