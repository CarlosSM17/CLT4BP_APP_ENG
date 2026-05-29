<template>
  <AppLayout>
    <div class='container mx-auto'>
      <div class='flex justify-between items-center mb-8'>
        <h1 class='text-4xl font-bold'>User Management</h1>
        <router-link to='/admin/instructors/create' class='btn btn-primary'>
          <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 mr-2' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4' />
          </svg>
          Create Instructor
        </router-link>
      </div>

      <!-- Filters -->
      <div class='card bg-base-100 shadow-xl mb-6'>
        <div class='card-body'>
          <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
            <div class='form-control'>
              <label class='label'>
                <span class='label-text'>Filter by role</span>
              </label>
              <select v-model='filters.role' @change='loadUsers' class='select select-bordered'>
                <option value=''>All</option>
                <option value='admin'>Administrators</option>
                <option value='instructor'>Instructors</option>
                <option value='student'>Students</option>
              </select>
            </div>

            <div class='form-control'>
              <label class='label'>
                <span class='label-text'>Search</span>
              </label>
              <input
                v-model='searchQuery'
                @input='handleSearch'
                type='text'
                placeholder='Search by name or email...'
                class='input input-bordered'
              />
            </div>

            <div class='form-control'>
              <label class='label'>
                <span class='label-text'>&nbsp;</span>
              </label>
              <button @click='resetFilters' class='btn btn-outline'>
                Clear Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if='loading' class='flex justify-center py-8'>
        <span class='loading loading-spinner loading-lg'></span>
      </div>

      <!-- Error -->
      <div v-else-if='error' class='alert alert-error'>
        <span>{{ error }}</span>
      </div>

      <!-- Users table -->
      <div v-else class='card bg-base-100 shadow-xl'>
        <div class='card-body'>
          <div class='overflow-x-auto'>
            <table class='table table-zebra'>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Last Login</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for='user in filteredUsers' :key='user.id'>
                  <td>{{ user.id }}</td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>
                    <span class='badge' :class='getRoleBadgeClass(user.role)'>
                      {{ getRoleLabel(user.role) }}
                    </span>
                  </td>
                  <td>{{ formatDate(user.last_login) }}</td>
                  <td>
                    <span class='badge' :class='user.is_active ? "badge-success" : "badge-error"'>
                      {{ user.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>
                    <div class='flex gap-2'>
                      <button
                        @click='viewUser(user)'
                        class='btn btn-sm btn-info'
                        title='View details'
                      >
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                          <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z' />
                          <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' />
                        </svg>
                      </button>
                      <button
                        v-if='user.id !== authStore.user?.id'
                        @click='confirmDeactivate(user)'
                        class='btn btn-sm btn-error'
                        :disabled='!user.is_active'
                        title='Deactivate user'
                      >
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                          <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-if='filteredUsers.length === 0' class='text-center py-8 text-gray-500'>
              No users found
            </div>
          </div>

          <div class='flex justify-between items-center mt-4'>
            <div class='text-sm text-gray-600'>
              Total: {{ filteredUsers.length }} users
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation modal -->
    <dialog ref='confirmModal' class='modal'>
      <div class='modal-box'>
        <h3 class='font-bold text-lg'>Confirm Deactivation</h3>
        <p class='py-4'>Are you sure you want to deactivate user <strong>{{ selectedUser?.name }}</strong>?</p>
        <div class='modal-action'>
          <button @click='closeModal' class='btn'>Cancel</button>
          <button @click='deactivateUser' class='btn btn-error' :disabled='deactivating'>
            <span v-if='deactivating' class='loading loading-spinner'></span>
            <span v-else>Deactivate</span>
          </button>
        </div>
      </div>
      <form method='dialog' class='modal-backdrop'>
        <button>close</button>
      </form>
    </dialog>

    <!-- Details modal -->
    <dialog ref='detailsModal' class='modal'>
      <div class='modal-box'>
        <h3 class='font-bold text-lg mb-4'>User Details</h3>
        <div v-if='selectedUser' class='space-y-3'>
          <div>
            <label class='font-semibold'>ID:</label>
            <p>{{ selectedUser.id }}</p>
          </div>
          <div>
            <label class='font-semibold'>Name:</label>
            <p>{{ selectedUser.name }}</p>
          </div>
          <div>
            <label class='font-semibold'>Email:</label>
            <p>{{ selectedUser.email }}</p>
          </div>
          <div>
            <label class='font-semibold'>Role:</label>
            <p>{{ getRoleLabel(selectedUser.role) }}</p>
          </div>
          <div>
            <label class='font-semibold'>Last Login:</label>
            <p>{{ formatDate(selectedUser.last_login) }}</p>
          </div>
          <div>
            <label class='font-semibold'>Status:</label>
            <p>{{ selectedUser.is_active ? 'Active' : 'Inactive' }}</p>
          </div>
        </div>
        <div class='modal-action'>
          <button @click='closeDetailsModal' class='btn'>Close</button>
        </div>
      </div>
      <form method='dialog' class='modal-backdrop'>
        <button>close</button>
      </form>
    </dialog>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import AppLayout from '../../components/layout/AppLayout.vue';
import axios from 'axios';

const authStore = useAuthStore();

const users = ref([]);
const loading = ref(false);
const error = ref(null);
const filters = ref({
  role: '',
});
const searchQuery = ref('');
const selectedUser = ref(null);
const deactivating = ref(false);
const confirmModal = ref(null);
const detailsModal = ref(null);

const filteredUsers = computed(() => {
  let result = users.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(user =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    );
  }

  return result;
});

const loadUsers = async () => {
  try {
    loading.value = true;
    error.value = null;
    const params = {};
    if (filters.value.role) {
      params.role = filters.value.role;
    }
    const response = await axios.get('/api/users', { params });
    users.value = response.data.users;
  } catch (err) {
    error.value = err.response?.data?.message || 'Error loading users';
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {};

const resetFilters = () => {
  filters.value.role = '';
  searchQuery.value = '';
  loadUsers();
};

const getRoleLabel = (role) => {
  const roles = {
    admin: 'Administrator',
    instructor: 'Instructor',
    student: 'Student',
  };
  return roles[role] || role;
};

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'badge-error',
    instructor: 'badge-warning',
    student: 'badge-info',
  };
  return classes[role] || '';
};

const formatDate = (date) => {
  if (!date) return 'Never';
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const viewUser = (user) => {
  selectedUser.value = user;
  detailsModal.value.showModal();
};

const confirmDeactivate = (user) => {
  selectedUser.value = user;
  confirmModal.value.showModal();
};

const deactivateUser = async () => {
  try {
    deactivating.value = true;
    await axios.delete(`/api/users/${selectedUser.value.id}/deactivate`);
    closeModal();
    loadUsers();
  } catch (err) {
    error.value = err.response?.data?.message || 'Error deactivating user';
  } finally {
    deactivating.value = false;
  }
};

const closeModal = () => {
  confirmModal.value.close();
  selectedUser.value = null;
};

const closeDetailsModal = () => {
  detailsModal.value.close();
  selectedUser.value = null;
};

onMounted(() => {
  loadUsers();
});
</script>
