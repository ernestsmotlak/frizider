import {createRouter, createWebHistory} from "vue-router";
import Login from "../pages/Login.vue";
import Dashboard from "../pages/Dashboard.vue";
import Error from "../pages/Error.vue";
import {useAuthStore} from "../stores/auth";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/login",
            name: "login",
            component: Login,
            meta: {guest: true},
        },
        {
            path: "/",
            name: "dashboard",
            component: Dashboard,
            meta: {requiresAuth: true},
        },
        {
            path: "/error",
            name: 'Error',
            component: Error,
            meta: {requiresAuth: false}
        },
    ],
});

router.beforeEach(async (to, _from, next) => {
    const auth = useAuthStore();

    if (!auth.initialized) {
        await auth.fetchUser();
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        next("/login");
    } else if (to.meta.guest && auth.isAuthenticated) {
        next("/");
    } else {
        next();
    }
});

export default router;

