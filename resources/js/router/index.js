import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth'; 

const routes = [
  /* Default Route */
  {
    path: '/',
    redirect : '/dashboard',
    name: 'home',
    meta: {
      module: null,
    },
  },
  /* Guest Routes */
  {
    path: '/login',
    name: 'login',
    component: () => import('../Pages/Auth/LoginPage.vue'),
    meta: { 
      guestOnly: true,
      title: 'Login',
    },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../Pages/Auth/RegisterPage.vue'),
    meta: { 
      guestOnly: true, 
      title: 'Register', 
    },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('../Pages/Auth/ForgotPasswordPage.vue'),
    meta: { 
      guestOnly: true, 
      title: 'Forgot Password', 
    },
  },
  /* Protected Routes */
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../Pages/DashboardPage.vue'),
    meta: { 
      requiresAuth: true,
      module: null,
    },
  },
  /* Settings Module */
  {
    path: '/settings/users',
    name: 'settings.users.list',
    component: () => import('../Pages/settings/users/UsersListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Users List',
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/settings/users/create',
    name: 'settings.users.create',
    component: () => import('../Pages/settings/users/UserProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Add User',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/settings/users/:id/profile',
    name: 'settings.users.profile',
    component: () => import('../Pages/settings/users/UserProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'User Profile',
      headerIcon: 'pencil',
      crud: 'edit.auth-user',
    },
  },
  {
    path: '/settings/users/:id/data',
    name: 'settings.users.data.edit',
    component: () => import('../Pages/settings/users/UserProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Edit User',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  {
    path: '/settings/languages',
    name: 'settings.languages.list',
    component: () => import('../Pages/settings/languages/LanguagesListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Languages List',
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/settings/languages/create',
    name: 'settings.languages.create',
    component: () => import('../Pages/settings/languages/LanguageFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Add Language',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/settings/languages/:id/data',
    name: 'settings.languages.edit',
    component: () => import('../Pages/settings/languages/LanguageFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Edit Language',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  /* Academics Module */
  {
    path: '/academics/settings/language-levels',
    name: 'academics.settings.language-levels.list',
    component: () => import('../Pages/academics/settings/language-levels/LanguageLevelsListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Language Levels List',
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/academics/settings/language-levels/create',
    name: 'academics.settings.language-levels.create',
    component: () => import('../Pages/academics/settings/language-levels/LanguageLevelFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Add Language Level',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/academics/settings/language-levels/:id/data',
    name: 'academics.settings.language-levels.edit',
    component: () => import('../Pages/academics/settings/language-levels/LanguageLevelFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Edit Language Level',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  {
    path: '/academics/settings/courses',
    name: 'academics.settings.courses.list',
    component: () => import('../Pages/academics/settings/courses/CoursesListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Courses List',
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/academics/settings/courses/create',
    name: 'academics.settings.courses.create',
    component: () => import('../Pages/academics/settings/courses/CourseFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Add Course',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/academics/settings/courses/:id/data',
    name: 'academics.settings.courses.edit',
    component: () => import('../Pages/academics/settings/courses/CourseFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Edit Course',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  {
    path: '/academics/settings/teachers',
    name: 'academics.settings.teachers.list',
    component: () => import('../Pages/academics/settings/teachers/TeachersListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Teachers List',
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/academics/settings/teachers/create',
    name: 'academics.settings.teachers.create',
    component: () => import('../Pages/academics/settings/teachers/TeacherProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Add Teacher',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/academics/settings/teachers/:id/data',
    name: 'academics.settings.teachers.edit',
    component: () => import('../Pages/academics/settings/teachers/TeacherProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Edit Teacher',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  {
    path: '/academics/settings/students',
    name: 'academics.settings.students.list',
    component: () => import('../Pages/academics/settings/students/StudentsListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Students List',
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/academics/settings/students/create',
    name: 'academics.settings.students.create',
    component: () => import('../Pages/academics/settings/students/StudentProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Add Student',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/academics/settings/students/:id/data',
    name: 'academics.settings.students.edit',
    component: () => import('../Pages/academics/settings/students/StudentProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'settings', 
      title: 'Edit Student',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  {
    path: '/academics/classes/class-schedules',
    name: 'academics.classes.class-schedules.list',
    component: () => import('../Pages/academics/classes/class-schedules/ClassSchedulesListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'classes', 
      title: 'Class Schedules List',
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/academics/classes/class-schedules/create',
    name: 'academics.classes.class-schedules.create',
    component: () => import('../Pages/academics/classes/class-schedules/ClassScheduleFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'classes', 
      title: 'Add Class Schedule',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/academics/classes/class-schedules/:id/data',
    name: 'academics.classes.class-schedules.edit',
    component: () => import('../Pages/academics/classes/class-schedules/ClassScheduleFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academics', 
      submodule: 'classes', 
      title: 'Edit Class Schedule',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  {
    path: '/academics/classes/class-schedules/:scheduleId/details/:detailId/data',
    name: 'academics.classes.class-schedules.details.edit',
    component: () => import('../Pages/academics/classes/class-schedules/ClassScheduleDetailFormPage.vue'),
    meta: {
      requiresAuth: true,
      module: 'academics',
      submodule: 'classes',
      title: 'Edit Class Schedule Detail',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  /* Fallback Route */
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../Pages/NotFoundPage.vue'),
  }
];

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore(); 

  if (!authStore.ready && !authStore.loading) {
    try {
      await authStore.checkAuth();
    } catch (error) {
      console.error('Failed to resolve auth state before navigation:', error);
      if (error.response && error.response.status === 419) {
        return next({ name: 'login', query: { redirect: to.fullPath } });
      }
    }
  }

  const isAuthenticated = authStore.isAuthenticated;

  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } });
  }

  const userId = authStore.user?.id;
  const userStatus = authStore.user?.status;

  if (isAuthenticated && userStatus === 'pending' && userId) {
    console.log('Redirecting to own profile for pending user.', isAuthenticated, userStatus, userId);
    const profileRoute = { name: 'settings.users.profile', params: { id: userId } };
    const isOnOwnProfile = to.name === profileRoute.name && `${to.params.id ?? ''}` === `${userId}`;

    if (!isOnOwnProfile) {
      return next(profileRoute);
    }
  }

  if (to.meta.guestOnly && isAuthenticated) {
    const redirect = to.query.redirect || { name: 'dashboard' };
    return next(redirect);
  }

  next();
  
});

export default router;
