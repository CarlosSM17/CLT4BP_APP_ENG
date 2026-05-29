<!-- src/components/clt/CltEffectCard.vue -->
<template>
  <div
    class="card bg-base-100 border-2 cursor-pointer transition-all"
    :class="{
      'border-primary bg-primary/5': selected,
      'border-base-300 hover:border-primary/50': !selected
    }"
    @click="$emit('toggle')"
  >
    <div class="card-body p-4">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <h3 class="card-title text-base">
            {{ effect.name }}
            <div class="badge badge-sm" :class="categoryBadgeClass">
              {{ categoryLabel }}
            </div>
          </h3>

          <p class="text-sm mt-2 text-base-content/70">
            {{ effect.description }}
          </p>

          <div class="collapse collapse-arrow mt-2">
            <input type="checkbox" class="min-h-0" />
            <div class="collapse-title text-sm font-medium px-0 py-2 min-h-0">
              How to apply it
            </div>
            <div class="collapse-content px-0">
              <p class="text-sm text-base-content/80">
                {{ effect.application_guidance }}
              </p>
            </div>
          </div>
        </div>

        <div class="ml-4">
          <input
            type="checkbox"
            :checked="selected"
            class="checkbox checkbox-primary"
            @click.stop
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  effect: {
    type: Object,
    required: true
  },
  selected: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['toggle']);

const categoryLabel = computed(() => {
  return props.effect.category === 'new_knowledge'
    ? 'New Knowledge'
    : 'Prior Knowledge';
});

const categoryBadgeClass = computed(() => {
  return props.effect.category === 'new_knowledge'
    ? 'badge-info'
    : 'badge-success';
});
</script>
