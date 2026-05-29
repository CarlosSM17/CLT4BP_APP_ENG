import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useCourseStore = defineStore('courses', () => {
    const courses = ref([]);
    const currentCourse = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const fetchCourses = async (filters = {}) => {
        try {
            loading.value = true;
            error.value = null;
            const params = {};
            if (filters.status) params.status = filters.status;
            if (filters.search) params.search = filters.search;

            const response = await axios.get('/api/courses', { params });
            courses.value = response.data.courses;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al cargar cursos';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const fetchCourse = async (id) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get(`/api/courses/${id}`);
            currentCourse.value = response.data.course;
            return response.data.course;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al cargar curso';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createCourse = async (courseData) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.post('/api/courses', courseData);
            courses.value.unshift(response.data.course);
            return response.data.course;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al crear curso';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateCourse = async (id, courseData) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.put(`/api/courses/${id}`, courseData);
            const index = courses.value.findIndex(c => c.id === id);
            if (index !== -1) {
                courses.value[index] = response.data.course;
            }
            return response.data.course;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al actualizar curso';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteCourse = async (id) => {
        try {
            loading.value = true;
            error.value = null;
            await axios.delete(`/api/courses/${id}`);
            courses.value = courses.value.filter(c => c.id !== id);
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al eliminar curso';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const enrollStudents = async (courseId, studentIds) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.post(`/api/courses/${courseId}/enroll`, {
                student_ids: studentIds
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al inscribir estudiantes';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getCourseStudents = async (courseId) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get(`/api/courses/${courseId}/students`);
            return response.data.students;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al cargar estudiantes';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const unenrollStudent = async (courseId, studentId) => {
        try {
            loading.value = true;
            error.value = null;
            await axios.delete(`/api/courses/${courseId}/students/${studentId}`);
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al remover estudiante';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        courses,
        currentCourse,
        loading,
        error,
        fetchCourses,
        fetchCourse,
        createCourse,
        updateCourse,
        deleteCourse,
        enrollStudents,
        getCourseStudents,
        unenrollStudent,
    };
});
