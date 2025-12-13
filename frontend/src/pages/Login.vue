<template>
  <div class="login-container">
    <h1>Login</h1>
    <form @submit.prevent="handleLogin" class="login-form">
      <div class="form-group">
        <label>
          Email:
          <input v-model="email" type="email" required />
        </label>
      </div>
      <div class="form-group">
        <label>
          Password:
          <input v-model="password" type="password" required />
        </label>
      </div>
      <button type="submit">Login</button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import http from "../api/http";

const email = ref("");
const password = ref("");

const handleLogin = async () => {
  try {
    const response = await http.post("/api/login", {
      email: email.value,
      password: password.value,
    });
    console.log(response.data);
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped>
.login-container {
  max-width: 400px;
  margin: 0 auto;
  padding: 2rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
}

.form-group input {
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

.form-group input:focus {
  outline: none;
  border-color: #646cff;
}

button {
  padding: 0.75rem 1.5rem;
  background-color: #646cff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.25s;
}

button:hover {
  background-color: #535bf2;
}
</style>

