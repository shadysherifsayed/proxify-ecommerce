<template>
  <AuthLayout>
    <v-card-title class="text-h5 text-center">Login</v-card-title>
    <v-card-text>
      <v-alert
        v-if="errorMessage"
        type="error"
        class="mb-4"
        :text="errorMessage"
      />

      <v-form>
        <v-label> Email </v-label>
        <v-text-field
          type="email"
          required
          v-model="form.email"
          :error-messages="validationErrors.email"
        />

        <v-label> Password </v-label>
        <v-text-field
          type="password"
          required
          v-model="form.password"
          :error-messages="validationErrors.password"
        />
        <v-btn
          color="primary"
          block
          class="mt-4"
          @click="login"
          :loading="isLoading"
          :disabled="isLoading"
          text="Login"
        />
      </v-form>
    </v-card-text>
    <v-card-actions class="justify-center">
      <v-btn
        text="Don't have an account? Register"
        @click="$router.push('register')"
        :disabled="isLoading"
        block
      />
    </v-card-actions>
  </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useAuthStore } from '@/Stores/auth';
import { LoginRequest } from '@/Types/requests';
import { ref } from 'vue';

const form = ref<LoginRequest>({
  email: '',
  password: '',
});

const authStore = useAuthStore();

const isLoading = ref(false);
const validationErrors = ref<Record<string, string[]>>({});
const errorMessage = ref<string>('');

async function login() {
  try {
    isLoading.value = true;
    validationErrors.value = {};
    errorMessage.value = '';

    await authStore.login(form.value);
  } catch (error: any) {
    if (error.validationErrors) {
      validationErrors.value = error.validationErrors;
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'An error occurred during login';
    }
  } finally {
    isLoading.value = false;
  }
}
</script>
