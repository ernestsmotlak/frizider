import {createRouter, createWebHistory} from "vue-router";
import Login from "../pages/Login.vue";
import Dashboard from "../pages/Dashboard.vue";
import Error from "../pages/Error.vue";
import {useAuthStore} from "../stores/auth";
import Register from "../pages/Register.vue";
import Landing from "../pages/Landing.vue";
import GroceryListsPage from "../pages/GroceryList/GroceryListsPage.vue";
import IngredientsPage from "../pages/IngredientsPage.vue";
import RecipesPage from "../pages/Recipe/RecipesPage.vue";
import RecipePage from "../pages/Recipe/RecipePage.vue";
import NewRecipe from "../pages/NewRecipe.vue";
import GroceryListPage from "../pages/GroceryList/GroceryListPage.vue";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/",
            name: "landing",
            component: Landing,
            meta: {guest: true},
        },
        {
            path: "/login",
            name: "login",
            component: Login,
            meta: {guest: true},
        },
        {
            path: "/dashboard",
            name: "dashboard",
            component: Dashboard,
            meta: {requiresAuth: true},
        },
        {
            path: "/grocery-list",
            name: "grocery-list",
            component: GroceryListsPage,
            meta: {requiresAuth: true},
        },
        {
            path: "/grocery-list/:id",
            name: "Grocery List",
            component: GroceryListPage,
            meta: {requiresAuth: true}
        },
        {
            path: "/ingredients",
            name: "ingredients",
            component: IngredientsPage,
            meta: {requiresAuth: true},
        },
        {
            path: "/recipes",
            name: "recipes",
            component: RecipesPage,
            meta: {requiresAuth: true},
        },
        {
            path: "/recipe/:id",
            name: 'Recipe',
            component: RecipePage,
            meta: {requiresAuth: true}
        },
        {
            path: "/new/recipe",
            name: 'New recipe',
            component: NewRecipe,
            meta: {requiresAuth: true}
        },
        {
            path: "/error",
            name: 'Error',
            component: Error,
            meta: {requiresAuth: false}
        },
        {
            path: '/register',
            name: 'Register',
            component: Register,
            meta: {guest: true}
        },
        {
            path: "/:pathMatch(.*)*",
            name: "NotFound",
            redirect: () => ({
                name: 'Error',
                query: {
                    code: 'Koji kurac!?',
                    message: 'The page you\'re looking for doesn\'t exist.'
                }
            }),
            meta: {requiresAuth: false}
        }
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
        next("/dashboard");
    } else {
        next();
    }
});

export default router;

