<template>
  <div class='hero min-h-screen bg-gradient-to-br from-blue-500 to-purple-600'>
    <div class='hero-content flex-col lg:flex-row-reverse'>
      <div class='text-center lg:text-left text-white'>
        <h1 class='text-5xl font-bold'>CLT4BP</h1>
        <p class='py-6'>AI-Powered Educational Web Platform</p>
      </div>
      <div class='card flex-shrink-0 w-full max-w-sm shadow-2xl bg-base-100'>
        <form @submit.prevent='handleLogin' class='card-body'>
          <h2 class='text-2xl font-bold text-center mb-4'>Sign In</h2>

          <div v-if='error' class='alert alert-error'>
            <span>{{ error }}</span>
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
            />
          </div>

          <div class='form-control mt-6'>
            <button
              type='submit'
              class='btn btn-primary'
              :disabled='loading'
            >
              <span v-if='loading' class='loading loading-spinner'></span>
              <span v-else>Sign In</span>
            </button>
          </div>

          <div class='divider'>Or</div>

          <div class='text-center'>
            <p>Don't have an account?</p>
            <router-link to='/register' class='link link-primary'>
              Register here
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
  email: '',
  password: '',
});

const error = ref(null);
const loading = ref(false);

const handleLogin = async () => {
  try {
    loading.value = true;
    error.value = null;
    await authStore.login(form.value);
  } catch (err) {
    error.value = err.response?.data?.message || 'Error signing in';
  } finally {
    loading.value = false;
  }
};
</script>
