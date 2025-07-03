<template>
  <AuthLayout>
    <v-card-title class="text-h5 text-center">Register</v-card-title>
    <v-alert
      v-if="errorMessage"
      type="error"
      class="mb-4"
      :text="errorMessage"
    />
    <v-card-text>
      <v-form>
        <v-label> Name </v-label>
        <v-text-field
          required
          v-model="form.name"
          :error-messages="validationErrors.name"
        />

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

        <v-label> Confirm Password </v-label>
        <v-text-field
          type="password"
          required
          v-model="form.password_confirmation"
          :error-messages="validationErrors.password_confirmation"
        />

        <v-btn
          block
          class="mt-4"
          @click="register"
          :loading="isLoading"
          :disabled="isLoading"
          text="Create Account"
        />
      </v-form>
    </v-card-text>
    <v-card-actions class="justify-center">
      <v-btn
        @click="$router.push('login')"
        :disabled="isLoading"
        block
        text="Already have an account? Login"
      />
    </v-card-actions>
  </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useAuthStore } from '@/Stores/auth';
import { RegisterRequest } from '@/Types/requests';
import { ref } from 'vue';

const form = ref<RegisterRequest>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const isLoading = ref(false);
const validationErrors = ref<Record<string, string[]>>({});
const errorMessage = ref<string>('');

const authStore = useAuthStore();

async function register() {
  try {
    isLoading.value = true;
    await authStore.register(form.value);
  } catch (error: any) {
    if (error.validationErrors) {
      validationErrors.value = error.validationErrors;
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'An error occurred during registration.';
    }
  } finally {
    isLoading.value = false;
  }
}
</script>
