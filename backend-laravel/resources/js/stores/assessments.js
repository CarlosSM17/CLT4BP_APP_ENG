import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useAssessmentStore = defineStore('assessments', () => {
    const assessments = ref([]);
    const currentAssessment = ref(null);
    const currentResponse = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const fetchAssessments = async (courseId) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get(`/api/courses/${courseId}/assessments`);
            assessments.value = response.data.assessments;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error loading assessments';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const fetchAssessment = async (courseId, assessmentId) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get(`/api/courses/${courseId}/assessments/${assessmentId}`);
            currentAssessment.value = response.data.assessment;
            return response.data.assessment;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error loading assessment';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createAssessment = async (courseId, assessmentData) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.post(`/api/courses/${courseId}/assessments`, assessmentData);
            assessments.value.unshift(response.data.assessment);
            return response.data.assessment;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error creating assessment';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateAssessment = async (courseId, assessmentId, assessmentData) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.put(`/api/courses/${courseId}/assessments/${assessmentId}`, assessmentData);
            const index = assessments.value.findIndex(a => a.id === assessmentId);
            if (index !== -1) {
                assessments.value[index] = response.data.assessment;
            }
            return response.data.assessment;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error updating assessment';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteAssessment = async (courseId, assessmentId) => {
        try {
            loading.value = true;
            error.value = null;
            await axios.delete(`/api/courses/${courseId}/assessments/${assessmentId}`);
            assessments.value = assessments.value.filter(a => a.id !== assessmentId);
        } catch (err) {
            error.value = err.response?.data?.message || 'Error deleting assessment';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const toggleActive = async (courseId, assessmentId) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.post(`/api/courses/${courseId}/assessments/${assessmentId}/toggle`);
            const index = assessments.value.findIndex(a => a.id === assessmentId);
            if (index !== -1) {
                assessments.value[index] = response.data.assessment;
            }
            return response.data.assessment;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error toggling status';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Funciones para estudiantes
    const startAssessment = async (courseId, assessmentId) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.post(`/api/courses/${courseId}/assessments/${assessmentId}/start`);
            currentResponse.value = response.data.response;
            return response.data.response;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error starting assessment';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const saveResponse = async (courseId, assessmentId, responses, isFinal = false) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.post(`/api/courses/${courseId}/assessments/${assessmentId}/save`, {
                responses,
                is_final: isFinal,
            });
            currentResponse.value = response.data.response;
            return response.data.response;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error saving response';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getMyResponse = async (courseId, assessmentId) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get(`/api/courses/${courseId}/assessments/${assessmentId}/my-response`);
            currentResponse.value = response.data.response;
            return response.data.response;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error loading response';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getAssessmentResponses = async (courseId, assessmentId) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get(`/api/courses/${courseId}/assessments/${assessmentId}/responses`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error loading responses';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const clearCurrentResponse = () => {
        currentResponse.value = null;
        currentAssessment.value = null;
    };

    return {
        assessments,
        currentAssessment,
        currentResponse,
        loading,
        error,
        fetchAssessments,
        fetchAssessment,
        createAssessment,
        updateAssessment,
        deleteAssessment,
        toggleActive,
        startAssessment,
        saveResponse,
        getMyResponse,
        getAssessmentResponses,
        clearCurrentResponse,
    };
});
