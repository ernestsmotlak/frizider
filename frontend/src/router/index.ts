import { createRouter, createWebHistory } from "vue-router";
import Login from "../pages/Login.vue";
import Dashboard from "../pages/Dashboard.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/login",
      name: "login",
      component: Login,
    },
    {
      path: "/",
      name: "dashboard",
      component: Dashboard,
    },
  ],
});

router.beforeEach((to, from, next) => {
  const isLoggedIn = false;

  if (!isLoggedIn && to.path !== "/login") {
    next("/login");
  } else if (isLoggedIn && to.path === "/login") {
    next("/");
  } else {
    next();
  }
});

export default router;

