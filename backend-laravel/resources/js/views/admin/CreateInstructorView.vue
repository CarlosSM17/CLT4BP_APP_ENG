<template>
  <AppLayout>
    <div class='container mx-auto max-w-2xl'>
      <div class='flex items-center mb-8'>
        <router-link to='/admin/users' class='btn btn-ghost btn-circle mr-4'>
          <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' />
          </svg>
        </router-link>
        <h1 class='text-4xl font-bold'>Create New Instructor</h1>
      </div>

      <div class='card bg-base-100 shadow-xl'>
        <div class='card-body'>
          <form @submit.prevent='handleSubmit'>
            <div v-if='error' class='alert alert-error mb-4'>
              <svg xmlns='http://www.w3.org/2000/svg' class='stroke-current shrink-0 h-6 w-6' fill='none' viewBox='0 0 24 24'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' />
              </svg>
              <span>{{ error }}</span>
            </div>

            <div v-if='success' class='alert alert-success mb-4'>
              <svg xmlns='http://www.w3.org/2000/svg' class='stroke-current shrink-0 h-6 w-6' fill='none' viewBox='0 0 24 24'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' />
              </svg>
              <span>{{ success }}</span>
            </div>

            <div class='form-control'>
              <label class='label'>
                <span class='label-text font-bold'>Full Name *</span>
              </label>
              <input
                v-model='form.name'
                type='text'
                placeholder='e.g. John Smith'
                class='input input-bordered'
                required
              />
              <label class='label'>
                <span class='label-text-alt'>Instructor's full name</span>
              </label>
            </div>

            <div class='form-control'>
              <label class='label'>
                <span class='label-text font-bold'>Email *</span>
              </label>
              <input
                v-model='form.email'
                type='email'
                placeholder='instructor@example.com'
                class='input input-bordered'
                required
              />
              <label class='label'>
                <span class='label-text-alt'>Instructor's institutional email</span>
              </label>
            </div>

            <div class='form-control'>
              <label class='label'>
                <span class='label-text font-bold'>Password *</span>
              </label>
              <input
                v-model='form.password'
                type='password'
                placeholder='••••••••'
                class='input input-bordered'
                required
                minlength='8'
              />
              <label class='label'>
                <span class='label-text-alt'>Minimum 8 characters</span>
              </label>
            </div>

            <div class='form-control'>
              <label class='label'>
                <span class='label-text font-bold'>Confirm Password *</span>
              </label>
              <input
                v-model='form.password_confirmation'
                type='password'
                placeholder='••••••••'
                class='input input-bordered'
                required
              />
            </div>

            <div class='alert alert-info mt-6'>
              <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' class='stroke-current shrink-0 w-6 h-6'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path>
              </svg>
              <div>
                <h3 class='font-bold'>Information</h3>
                <div class='text-xs'>
                  The instructor will be able to create courses, assign students, and generate AI instructional materials.
                </div>
              </div>
            </div>

            <div class='divider'></div>

            <div class='card-actions justify-end'>
              <router-link to='/admin/users' class='btn btn-outline'>
                Cancel
              </router-link>
              <button
                type='submit'
                class='btn btn-primary'
                :disabled='loading'
              >
                <span v-if='loading' class='loading loading-spinner'></span>
                <span v-else>Create Instructor</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Additional information -->
      <div class='card bg-base-100 shadow-xl mt-6'>
        <div class='card-body'>
          <h2 class='card-title'>Instructor Permissions</h2>
          <ul class='list-disc list-inside space-y-2'>
            <li>Create and edit courses</li>
            <li>Assign students to courses</li>
            <li>Configure assessments and questionnaires</li>
            <li>Generate AI instructional materials</li>
            <li>Activate/deactivate content</li>
            <li>View reports and analytics</li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '../../components/layout/AppLayout.vue';
import axios from 'axios';

const router = useRouter();

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const error = ref(null);
const success = ref(null);

const handleSubmit = async () => {
  error.value = null;
  success.value = null;

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }

  try {
    loading.value = true;

    const response = await axios.post('/api/users/instructors', form.value);

    success.value = `Instructor ${response.data.user.name} created successfully`;

    form.value = {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    };

    setTimeout(() => {
      router.push('/admin/users');
    }, 2000);

  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat();
      error.value = errors.join(', ');
    } else {
      error.value = err.response?.data?.message || 'Error creating instructor';
    }
  } finally {
    loading.value = false;
  }
};
</script>
