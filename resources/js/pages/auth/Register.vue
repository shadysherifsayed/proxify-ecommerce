<!-- pages/Register.vue -->
<template>
  <AuthLayout>
    <h1 class="text-h4 font-weight-bold text-white mb-2">Register</h1>
    <p class="text-white mb-4">
      Already have an account? <RouterLink to="/login" class="text-primary">Login</RouterLink>
    </p>

    <v-form>
      <v-text-field
        v-model="name"
        label="Name"
        variant="outlined"
        color="primary"
        class="mb-3"
        placeholder="Your name"
        hide-details
      />
      <v-text-field
        v-model="email"
        label="Email"
        variant="outlined"
        color="primary"
        class="mb-3"
        placeholder="you@example.com"
        hide-details
      />
      <v-text-field
        v-model="password"
        label="Password"
        type="password"
        variant="outlined"
        color="primary"
        placeholder="Create a password"
        hide-details
      />

      <v-btn color="primary" block class="mt-2">Register</v-btn>
    </v-form>
  </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthLayout.vue'
import { ref } from 'vue'

const name = ref('')
const email = ref('')
const password = ref('')
</script>
