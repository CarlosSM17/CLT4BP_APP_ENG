<!-- src/components/profiles/ProfileDetailsModal.vue -->
<template>
  <div class="modal modal-open">
    <div class="modal-box max-w-4xl">
      <button
        class="btn btn-sm btn-circle absolute right-2 top-2"
        @click="$emit('close')"
      >
        X
      </button>

      <h2 class="font-bold text-2xl mb-4">
        Detailed Profile: {{ profile.student_info?.name }}
      </h2>

      <!-- Student information -->
      <div class="flex items-center gap-4 mb-6">
        <div class="avatar placeholder">
          <div class="bg-primary text-primary-content rounded-full w-16">
            <span class="text-xl">{{ getInitials(profile.student_info?.name) }}</span>
          </div>
        </div>
        <div>
          <p class="text-lg font-semibold">{{ profile.student_info?.name }}</p>
          <p class="text-gray-600">ID: {{ profile.student_info?.student_id }}</p>
        </div>
      </div>

      <!-- General summary -->
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="stat bg-base-200 rounded-lg p-4">
          <div class="stat-title text-sm">Overall Motivation</div>
          <div class="stat-value text-xl" :class="getLevelClass(profile.profile_summary?.overall_motivation)">
            {{ profile.profile_summary?.overall_motivation }}
          </div>
        </div>
        <div class="stat bg-base-200 rounded-lg p-4">
          <div class="stat-title text-sm">Learning Strategies</div>
          <div class="stat-value text-xl" :class="getLevelClass(profile.profile_summary?.overall_strategies)">
            {{ profile.profile_summary?.overall_strategies }}
          </div>
        </div>
        <div class="stat bg-base-200 rounded-lg p-4">
          <div class="stat-title text-sm">Prior Knowledge</div>
          <div class="stat-value text-xl" :class="getLevelClass(profile.profile_summary?.prior_knowledge)">
            {{ profile.profile_summary?.prior_knowledge }}
          </div>
        </div>
      </div>

      <!-- Radar chart -->
      <div class="mb-6">
        <h3 class="font-semibold text-lg mb-3">MSLQ Profile</h3>
        <ProfileRadarChart :data="getRadarData()" :title="profile.student_info?.name" />
      </div>

      <!-- Detailed dimensions -->
      <div class="mb-6">
        <h3 class="font-semibold text-lg mb-3">MSLQ Dimensions</h3>

        <!-- Motivation Scales -->
        <div class="collapse collapse-arrow bg-base-200 mb-2">
          <input type="checkbox" checked />
          <div class="collapse-title font-medium">
            Motivation Scales
          </div>
          <div class="collapse-content">
            <div class="overflow-x-auto">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Dimension</th>
                    <th>Score</th>
                    <th>Level</th>
                    <th>Progress</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="dim in motivationDimensions" :key="dim.key">
                    <td>{{ dim.label }}</td>
                    <td>{{ getMslqScore(dim.key)?.toFixed(2) || 'N/A' }}</td>
                    <td>
                      <span class="badge" :class="getLevelBadgeClass(getMslqLevel(dim.key))">
                        {{ getMslqLevel(dim.key) }}
                      </span>
                    </td>
                    <td>
                      <progress
                        class="progress w-32"
                        :class="getProgressClass(getMslqScore(dim.key))"
                        :value="getMslqScore(dim.key)"
                        max="7"
                      ></progress>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Strategy Scales -->
        <div class="collapse collapse-arrow bg-base-200">
          <input type="checkbox" checked />
          <div class="collapse-title font-medium">
            Learning Strategy Scales
          </div>
          <div class="collapse-content">
            <div class="overflow-x-auto">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Dimension</th>
                    <th>Score</th>
                    <th>Level</th>
                    <th>Progress</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="dim in strategyDimensions" :key="dim.key">
                    <td>{{ dim.label }}</td>
                    <td>{{ getMslqScore(dim.key)?.toFixed(2) || 'N/A' }}</td>
                    <td>
                      <span class="badge" :class="getLevelBadgeClass(getMslqLevel(dim.key))">
                        {{ getMslqLevel(dim.key) }}
                      </span>
                    </td>
                    <td>
                      <progress
                        class="progress w-32"
                        :class="getProgressClass(getMslqScore(dim.key))"
                        :value="getMslqScore(dim.key)"
                        max="7"
                      ></progress>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Strengths and Support Areas -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-success/10 rounded-lg p-4">
          <h4 class="font-semibold mb-2 flex items-center text-success">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            Strengths
          </h4>
          <ul class="list-disc list-inside text-sm">
            <li v-for="strength in profile.profile_summary?.key_strengths" :key="strength" class="mb-1">
              {{ formatDimension(strength) }}
            </li>
            <li v-if="!profile.profile_summary?.key_strengths?.length" class="text-gray-500">
              No specific strengths identified
            </li>
          </ul>
        </div>

        <div class="bg-warning/10 rounded-lg p-4">
          <h4 class="font-semibold mb-2 flex items-center text-warning">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
            </svg>
            Support Areas
          </h4>
          <ul class="list-disc list-inside text-sm">
            <li v-for="area in profile.profile_summary?.areas_for_support" :key="area" class="mb-1">
              {{ formatDimension(area) }}
            </li>
            <li v-if="!profile.profile_summary?.areas_for_support?.length" class="text-gray-500">
              No specific support areas identified
            </li>
          </ul>
        </div>
      </div>

      <!-- Instructional Recommendations -->
      <div v-if="profile.recommendations?.length" class="mb-6">
        <h3 class="font-semibold text-lg mb-3">Instructional Recommendations</h3>
        <div class="alert alert-info">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <ul class="list-disc list-inside">
              <li v-for="(rec, index) in profile.recommendations" :key="index" class="mb-1">
                {{ rec }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Recommended CLT Effects -->
      <div v-if="profile.clt_effects?.length" class="mb-6">
        <h3 class="font-semibold text-lg mb-3">Recommended CLT Effects</h3>
        <div class="flex flex-wrap gap-2">
          <div
            v-for="effect in profile.clt_effects"
            :key="effect.effect"
            class="badge badge-lg badge-outline p-3"
          >
            {{ effect.effect }}
            <span class="ml-1 text-xs opacity-70">({{ effect.priority }})</span>
          </div>
        </div>
      </div>

      <div class="modal-action">
        <button class="btn" @click="$emit('close')">Close</button>
      </div>
    </div>
    <div class="modal-backdrop bg-black/50" @click="$emit('close')"></div>
  </div>
</template>

<script setup>
import ProfileRadarChart from './ProfileRadarChart.vue';

const props = defineProps({
  profile: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close']);

const motivationDimensions = [
  { key: 'intrinsic_goal_orientation', label: 'Intrinsic Goal Orientation' },
  { key: 'extrinsic_goal_orientation', label: 'Extrinsic Goal Orientation' },
  { key: 'task_value', label: 'Task Value' },
  { key: 'control_beliefs', label: 'Control Beliefs' },
  { key: 'self_efficacy', label: 'Self-Efficacy' },
  { key: 'test_anxiety', label: 'Test Anxiety' }
];

const strategyDimensions = [
  { key: 'rehearsal', label: 'Rehearsal' },
  { key: 'elaboration', label: 'Elaboration' },
  { key: 'organization', label: 'Organization' },
  { key: 'critical_thinking', label: 'Critical Thinking' },
  { key: 'metacognitive_self_regulation', label: 'Metacognitive Self-Regulation' },
  { key: 'time_management', label: 'Time & Environment Management' },
  { key: 'effort_regulation', label: 'Effort Regulation' },
  { key: 'peer_learning', label: 'Peer Learning' },
  { key: 'help_seeking', label: 'Help Seeking' }
];

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getLevelClass = (level) => {
  switch(level?.toLowerCase()) {
    case 'alto': return 'text-success';
    case 'medio': return 'text-warning';
    case 'bajo': return 'text-error';
    default: return 'text-base-content';
  }
};

const getLevelBadgeClass = (level) => {
  switch(level?.toLowerCase()) {
    case 'alto': return 'badge-success';
    case 'medio': return 'badge-warning';
    case 'bajo': return 'badge-error';
    default: return 'badge-ghost';
  }
};

const getProgressClass = (score) => {
  if (!score) return 'progress-primary';
  if (score >= 5) return 'progress-success';
  if (score >= 3) return 'progress-warning';
  return 'progress-error';
};

const getMslqScore = (dimension) => {
  return props.profile.mslq_scores?.[dimension]?.average;
};

const getMslqLevel = (dimension) => {
  return props.profile.mslq_scores?.[dimension]?.level || 'N/A';
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
    'help_seeking': 'Help seeking',
    'prior_knowledge': 'Prior knowledge'
  };
  return translations[dimension] || dimension;
};

const getRadarData = () => {
  const labels = [
    'Self-Efficacy',
    'Task Value',
    'Elaboration',
    'Organization',
    'Crit. Thinking',
    'Self-Regulation',
    'Time Mgmt',
    'Peer Learning'
  ];

  const mslq = props.profile.mslq_scores || {};

  const values = [
    mslq.self_efficacy?.average || 0,
    mslq.task_value?.average || 0,
    mslq.elaboration?.average || 0,
    mslq.organization?.average || 0,
    mslq.critical_thinking?.average || 0,
    mslq.metacognitive_self_regulation?.average || 0,
    mslq.time_management?.average || 0,
    mslq.peer_learning?.average || 0
  ];

  return { labels, values };
};
</script>
