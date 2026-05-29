<!-- src/components/profiles/StudentProfileCard.vue -->
<template>
  <div class="card bg-base-100 shadow-xl">
    <div class="card-body">
      <h3 class="card-title">
        {{ profile.student_info.name }}
        <div class="badge badge-primary ml-2">ID: {{ profile.student_info.student_id }}</div>
      </h3>

      <!-- Profile summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div class="stat bg-base-200 rounded-lg">
          <div class="stat-title">Motivation</div>
          <div class="stat-value text-2xl" :class="getLevelClass(profile.profile_summary.overall_motivation)">
            {{ profile.profile_summary.overall_motivation }}
          </div>
        </div>

        <div class="stat bg-base-200 rounded-lg">
          <div class="stat-title">Strategies</div>
          <div class="stat-value text-2xl" :class="getLevelClass(profile.profile_summary.overall_strategies)">
            {{ profile.profile_summary.overall_strategies }}
          </div>
        </div>

        <div class="stat bg-base-200 rounded-lg">
          <div class="stat-title">Prior Knowledge</div>
          <div class="stat-value text-2xl" :class="getLevelClass(profile.profile_summary.prior_knowledge)">
            {{ profile.profile_summary.prior_knowledge }}
          </div>
        </div>
      </div>

      <!-- Strengths and Support Areas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
          <h4 class="font-semibold mb-2 flex items-center">
            <svg class="w-5 h-5 mr-2 text-success" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            Strengths
          </h4>
          <ul class="list-disc list-inside text-sm">
            <li v-for="strength in profile.profile_summary.key_strengths" :key="strength" class="mb-1">
              {{ formatDimension(strength) }}
            </li>
          </ul>
        </div>

        <div>
          <h4 class="font-semibold mb-2 flex items-center">
            <svg class="w-5 h-5 mr-2 text-warning" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
            </svg>
            Support Areas
          </h4>
          <ul class="list-disc list-inside text-sm">
            <li v-for="area in profile.profile_summary.areas_for_support" :key="area" class="mb-1">
              {{ formatDimension(area) }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Recommendations -->
      <div class="mt-4" v-if="profile.recommendations && profile.recommendations.length > 0">
        <h4 class="font-semibold mb-2">Instructional Recommendations</h4>
        <div class="alert alert-info">
          <ul class="list-disc list-inside text-sm">
            <li v-for="(rec, index) in profile.recommendations" :key="index" class="mb-1">
              {{ rec }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="card-actions justify-end mt-4">
        <button class="btn btn-sm btn-outline" @click="$emit('view-details', profile)">
          View Full Details
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  profile: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['view-details']);

const getLevelClass = (level) => {
  switch(level?.toLowerCase()) {
    case 'alto': return 'text-success';
    case 'medio': return 'text-warning';
    case 'bajo': return 'text-error';
    default: return 'text-base-content';
  }
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
</script>
