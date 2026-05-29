import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/',
        redirect: '/login',
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/auth/LoginView.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('../views/auth/RegisterView.vue'),
        meta: { guest: true },
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('../views/DashboardView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('../views/ProfileView.vue'),
        meta: { requiresAuth: true },
    },
    // Rutas de Admin
    {
        path: '/admin/users',
        name: 'AdminUsers',
        component: () => import('../views/admin/UsersView.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
    },
    {
        path: '/admin/instructors/create',
        name: 'CreateInstructor',
        component: () => import('../views/admin/CreateInstructorView.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
    },
    // Rutas de Instructor - Cursos
    {
        path: '/instructor/courses',
        name: 'InstructorCourses',
        component: () => import('../views/instructor/CoursesView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    {
        path: '/instructor/courses/create',
        name: 'CreateCourse',
        component: () => import('../views/instructor/CreateCourseView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    {
        path: '/instructor/courses/:id',
        name: 'CourseDetail',
        component: () => import('../views/instructor/CourseDetailView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    {
        path: '/instructor/courses/:id/edit',
        name: 'EditCourse',
        component: () => import('../views/instructor/EditCourseView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    {
        path: '/instructor/courses/:id/students',
        name: 'CourseStudents',
        component: () => import('../views/instructor/CourseStudentsView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    // Rutas de Instructor - Evaluaciones
    {
        path: '/instructor/courses/:courseId/assessments',
        name: 'CourseAssessments',
        component: () => import('../views/assessments/AssessmentsListView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    {
        path: '/instructor/courses/:courseId/assessments/create',
        name: 'CreateAssessment',
        component: () => import('../views/assessments/CreateAssessmentView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    {
        path: '/instructor/courses/:courseId/assessments/:assessmentId/edit',
        name: 'EditAssessment',
        component: () => import('../views/assessments/EditAssessmentView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    {
        path: '/instructor/courses/:courseId/assessments/:assessmentId/responses',
        name: 'AssessmentResponses',
        component: () => import('../views/assessments/AssessmentResponsesView.vue'),
        meta: { requiresAuth: true, roles: ['instructor'] },
    },
    // Rutas de Instructor - Perfiles (Sprint 4)
    {
        path: '/instructor/courses/:courseId/profiles',
        name: 'CourseProfiles',
        component: () => import('../views/instructor/ProfilesDashboard.vue'),
        meta: { requiresAuth: true, roles: ['instructor', 'admin'] },
    },
    // Rutas de Instructor - Calificación Manual
    {
        path: '/instructor/courses/:courseId/grading',
        name: 'CourseGrading',
        component: () => import('../views/instructor/GradingDashboard.vue'),
        meta: { requiresAuth: true, roles: ['instructor', 'admin'] },
    },
    // Rutas de Instructor - Sprint 5: CLT Effects y Material Generation
    {
        path: '/instructor/courses/:courseId/clt-effects',
        name: 'CourseCltEffects',
        component: () => import('../views/instructor/CltEffectsView.vue'),
        meta: { requiresAuth: true, roles: ['instructor', 'admin'] },
    },
    {
        path: '/instructor/courses/:courseId/materials',
        name: 'CourseMaterials',
        component: () => import('../views/instructor/MaterialsView.vue'),
        meta: { requiresAuth: true, roles: ['instructor', 'admin'] },
    },
    {
        path: '/instructor/courses/:courseId/data-collection',
        name: 'CourseDataCollection',
        component: () => import('../views/instructor/FinalDataDashboardView.vue'),
        meta: { requiresAuth: true, roles: ['instructor', 'admin'] },
    },
    {
        path: '/instructor/courses/:courseId/reports',
        name: 'CourseReports',
        component: () => import('../views/instructor/InstructorReportDashboard.vue'),
        meta: { requiresAuth: true, roles: ['instructor', 'admin'] },
    },
    // Rutas de Estudiante - Cursos
    {
        path: '/student/courses',
        name: 'StudentCourses',
        component: () => import('../views/student/MyCoursesView.vue'),
        meta: { requiresAuth: true, roles: ['student'] },
    },
    {
        path: '/student/courses/:courseId/assessments',
        name: 'StudentCourseAssessments',
        component: () => import('../views/student/CourseAssessmentsView.vue'),
        meta: { requiresAuth: true, roles: ['student'] },
    },
    {
        path: '/student/courses/:courseId/materials',
        name: 'StudentCourseMaterials',
        component: () => import('../views/student/StudentMaterialsView.vue'),
        meta: { requiresAuth: true, roles: ['student'] },
    },
    {
        path: '/student/courses/:courseId/report',
        name: 'StudentReport',
        component: () => import('../views/student/StudentReportView.vue'),
        meta: { requiresAuth: true, roles: ['student'] },
    },
    // Rutas de Estudiante - Evaluaciones
    {
        path: '/student/courses/:courseId/assessments/:assessmentId/take',
        name: 'TakeAssessment',
        component: () => import('../views/assessments/TakeAssessmentView.vue'),
        meta: { requiresAuth: true, roles: ['student'] },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // Si hay token pero no hay usuario, obtenerlo
    if (authStore.token && !authStore.user) {
        try {
            await authStore.fetchUser();
        } catch (error) {
            authStore.clearAuth();
            if (to.meta.requiresAuth) {
                return next('/login');
            }
        }
    }

    // Verificar si la ruta requiere autenticación
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next('/login');
    }

    // Si ya está autenticado y va a login/register, redirigir
    if (to.meta.guest && authStore.isAuthenticated) {
        return next('/dashboard');
    }

    // Verificar roles si es necesario
    if (to.meta.roles && authStore.user) {
        if (!to.meta.roles.includes(authStore.user.role)) {
            return next('/dashboard');
        }
    }

    next();
});

export default router;
