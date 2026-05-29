<template>
  <div class='hero min-h-screen bg-gradient-to-br from-green-500 to-blue-600'>
    <div class='hero-content flex-col'>
      <div class='text-center text-white'>
        <h1 class='text-5xl font-bold'>Student Registration</h1>
        <p class='py-6'>CLT4BP - Educational Platform</p>
      </div>
      <div class='card flex-shrink-0 w-full max-w-sm shadow-2xl bg-base-100'>
        <form @submit.prevent='handleRegister' class='card-body'>
          <div v-if='error' class='alert alert-error'>
            <span>{{ error }}</span>
          </div>

          <div class='form-control'>
            <label class='label'>
              <span class='label-text'>Full Name</span>
            </label>
            <input
              v-model='form.name'
              type='text'
              placeholder='John Doe'
              class='input input-bordered'
              required
            />
          </div>

          <div class='form-control'>
            <label class='label'>
              <span class='label-text'>Email</span>
            </label>
            <input
              v-model='form.email'
              type='email'
              placeholder='email@example.com'
              class='input input-bordered'
              required
            />
          </div>

          <div class='form-control'>
            <label class='label'>
              <span class='label-text'>Password</span>
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
              <span class='label-text'>Confirm Password</span>
            </label>
            <input
              v-model='form.password_confirmation'
              type='password'
              placeholder='••••••••'
              class='input input-bordered'
              required
            />
          </div>

          <div class='form-control mt-6'>
            <button
              type='submit'
              class='btn btn-primary'
              :disabled='loading'
            >
              <span v-if='loading' class='loading loading-spinner'></span>
              <span v-else>Register</span>
            </button>
          </div>

          <div class='divider'>Or</div>

          <div class='text-center'>
            <p>Already have an account?</p>
            <router-link to='/login' class='link link-primary'>
              Sign in here
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const error = ref(null);
const loading = ref(false);

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }

  try {
    loading.value = true;
    error.value = null;
    await authStore.register(form.value);
  } catch (err) {
    error.value = err.response?.data?.message || 'Registration error';
  } finally {
    loading.value = false;
  }
};
</script>
