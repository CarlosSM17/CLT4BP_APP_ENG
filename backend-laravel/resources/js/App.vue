<template>
  <div id="app" class="min-h-screen">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
  console.log('CLT4BP App mounted successfully');
});
</script>
