<!-- src/views/instructor/ProfilesDashboard.vue -->
<template>
  <div class="profiles-dashboard container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Student Profiles</h1>

      <div class="flex gap-2">
        <button
          class="btn btn-primary"
          @click="regenerateAllProfiles"
          :disabled="loading"
        >
          <svg v-if="loading" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Generating...' : 'Regenerate All Profiles' }}
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs tabs-boxed mb-6">
      <a
        class="tab"
        :class="{ 'tab-active': activeTab === 'group' }"
        @click="activeTab = 'group'"
      >
        Group Profile
      </a>
      <a
        class="tab"
        :class="{ 'tab-active': activeTab === 'individual' }"
        @click="activeTab = 'individual'"
      >
        Individual Profiles
      </a>
    </div>

    <!-- Content -->
    <div v-if="activeTab === 'group'">
      <GroupProfileView
        v-if="groupProfile"
        :groupProfile="groupProfile"
      />
      <div v-else class="alert alert-warning">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <span>No group profile generated. Generate individual profiles first.</span>
      </div>
    </div>

    <div v-else>
      <!-- Filters and search -->
      <div class="mb-4 flex gap-4">
        <input
          type="text"
          placeholder="Search by name..."
          class="input input-bordered flex-1"
          v-model="searchQuery"
        />

        <select class="select select-bordered" v-model="filterLevel">
          <option value="">All levels</option>
          <option value="alto">High</option>
          <option value="medio">Medium</option>
          <option value="bajo">Low</option>
        </select>
      </div>

      <!-- Profile list -->
      <div class="grid grid-cols-1 gap-6">
        <StudentProfileCard
          v-for="profile in filteredProfiles"
          :key="profile.id"
          :profile="profile.profile_data"
          @view-details="viewProfileDetails"
        />
      </div>

      <div v-if="filteredProfiles.length === 0" class="alert alert-info mt-6">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>No profiles found matching the filters.</span>
      </div>
    </div>

    <!-- Details modal -->
    <ProfileDetailsModal
      v-if="selectedProfile"
      :profile="selectedProfile"
      @close="selectedProfile = null"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import GroupProfileView from '@/components/profiles/GroupProfileView.vue';
import StudentProfileCard from '@/components/profiles/StudentProfileCard.vue';
import ProfileDetailsModal from '@/components/profiles/ProfileDetailsModal.vue';
import { useProfilesStore } from '@/stores/profiles';

const route = useRoute();
const profilesStore = useProfilesStore();

const courseId = computed(() => route.params.courseId);
const activeTab = ref('group');
const loading = ref(false);
const searchQuery = ref('');
const filterLevel = ref('');
const selectedProfile = ref(null);

const studentProfiles = ref([]);
const groupProfile = ref(null);

const filteredProfiles = computed(() => {
  let profiles = studentProfiles.value;

  if (searchQuery.value) {
    profiles = profiles.filter(p =>
      p.profile_data.student_info.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (filterLevel.value) {
    profiles = profiles.filter(p =>
      p.profile_data.profile_summary.overall_motivation === filterLevel.value ||
      p.profile_data.profile_summary.overall_strategies === filterLevel.value ||
      p.profile_data.profile_summary.prior_knowledge === filterLevel.value
    );
  }

  return profiles;
});

const loadProfiles = async () => {
  try {
    loading.value = true;

    studentProfiles.value = await profilesStore.fetchCourseProfiles(courseId.value);

    try {
      groupProfile.value = await profilesStore.fetchGroupProfile(courseId.value);
    } catch (error) {
      console.log('No group profile generated yet');
    }
  } catch (error) {
    console.error('Error loading profiles:', error);
  } finally {
    loading.value = false;
  }
};

const regenerateAllProfiles = async () => {
  if (!confirm('Are you sure you want to regenerate all profiles? This will overwrite existing profiles.')) {
    return;
  }

  try {
    loading.value = true;
    await profilesStore.regenerateAllProfiles(courseId.value);
    await loadProfiles();
    alert('Profiles regenerated successfully');
  } catch (error) {
    console.error('Error regenerating profiles:', error);
    alert('Error regenerating profiles. Please try again.');
  } finally {
    loading.value = false;
  }
};

const viewProfileDetails = (profile) => {
  selectedProfile.value = profile;
};

onMounted(() => {
  loadProfiles();
});
</script>
