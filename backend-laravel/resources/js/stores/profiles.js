import { defineStore } from 'pinia';
import api from '@/services/api';

export const useProfilesStore = defineStore('profiles', {
  state: () => ({
    studentProfiles: [],
    groupProfile: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchCourseProfiles(courseId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.get(`/courses/${courseId}/profiles/students`);
        this.studentProfiles = response.data.data;
        return this.studentProfiles;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchGroupProfile(courseId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.get(`/courses/${courseId}/profiles/group`);
        this.groupProfile = response.data.data;
        return this.groupProfile;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async generateStudentProfile(courseId, studentId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.post(
          `/courses/${courseId}/profiles/students/${studentId}/generate`
        );
        return response.data.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async generateAllStudentProfiles(courseId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.post(
          `/courses/${courseId}/profiles/students/generate-all`
        );
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async generateGroupProfile(courseId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.post(
          `/courses/${courseId}/profiles/group/generate`
        );
        this.groupProfile = response.data.data;
        return this.groupProfile;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async regenerateAllProfiles(courseId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.post(
          `/courses/${courseId}/profiles/regenerate-all`
        );
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
