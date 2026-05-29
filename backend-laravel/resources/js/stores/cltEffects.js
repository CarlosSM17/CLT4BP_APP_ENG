// src/stores/cltEffects.js
import { defineStore } from 'pinia';
import api from '@/services/api';

export const useCltEffectsStore = defineStore('cltEffects', {
  state: () => ({
    availableEffects: [],
    currentSelection: null,
    recommendations: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchAvailableEffects(courseId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.get(`/courses/${courseId}/clt-effects/available`);
        this.availableEffects = response.data.data;
        return response.data.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchSelection(courseId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.get(`/courses/${courseId}/clt-effects/selection`);
        this.currentSelection = response.data.data;
        return this.currentSelection;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async saveSelection(courseId, selectionData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.post(
          `/courses/${courseId}/clt-effects/selection`,
          selectionData
        );
        this.currentSelection = response.data.data;
        return this.currentSelection;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchRecommendations(courseId) {
      try {
        const response = await api.get(`/courses/${courseId}/clt-effects/recommendations`);
        this.recommendations = response.data.data;
        return this.recommendations;
      } catch (error) {
        this.error = error.message;
        throw error;
      }
    }
  }
});
