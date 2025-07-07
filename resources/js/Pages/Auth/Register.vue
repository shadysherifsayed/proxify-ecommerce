<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useAuthStore } from '@/Stores/auth';
import { RegisterRequest } from '@/Types/requests';
import { validateEmail, validatePassword } from '@/Utils/validation';
import { ref } from 'vue';

const authStore = useAuthStore();

const form = ref<RegisterRequest>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const formRef = ref<any>(null);

const rules = ref({
  name: [
    (value: string) => !!value || 'Name is required',
    (value: string) =>
      value.length >= 3 || 'Name must be at least 3 characters long',
    (value: string) =>
      value.length <= 50 || 'Name must be at most 50 characters long',
  ],
  email: [
    (value: string) => !!value || 'Email is required',
    (value: string) => validateEmail(value) || 'Email must be valid',
  ],
  password: [
    (value: string) => !!value || 'Password is required',
    (value: string) =>
      validatePassword(value) ||
      'Password must be at least 8 characters long and contain at least one number and one special character',
  ],
  password_confirmation: [
    (value: string) => !!value || 'Password confirmation is required',
    (value: string) =>
      value === form.value.password || 'Passwords do not match',
  ],
});

const isSubmitting = ref(false);
const validationErrors = ref<Record<string, string[]>>({});
const errorMessage = ref<string>('');

async function register() {
  try {
    validationErrors.value = {};
    const { valid } = await formRef.value?.validate();
    if (!valid) {
      return;
    }
    isSubmitting.value = true;
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
    isSubmitting.value = false;
  }
}
</script>

<template>
  <AuthLayout>
    <v-card-title class="text-h5 text-center">Register</v-card-title>
    <v-card-text>
      <v-form ref="formRef" validate-on="submit" autocomplete="off">
        <v-label> Name </v-label>
        <v-text-field
          required
          v-model="form.name"
          :error-messages="validationErrors.name"
          :rules="rules.name"
          autocomplete="off"
        />

        <v-label> Email </v-label>
        <v-text-field
          type="email"
          required
          v-model="form.email"
          :error-messages="validationErrors.email"
          :rules="rules.email"
          autocomplete="off"
        />

        <v-label> Password </v-label>
        <v-text-field
          type="password"
          required
          v-model="form.password"
          :error-messages="validationErrors.password"
          :rules="rules.password"
          autocomplete="off"
        />

        <v-label> Confirm Password </v-label>
        <v-text-field
          type="password"
          required
          v-model="form.password_confirmation"
          :error-messages="validationErrors.password_confirmation"
          :rules="rules.password_confirmation"
          autocomplete="off"
        />

        <v-btn
          block
          class="mt-4"
          :loading="isSubmitting"
          :disabled="isSubmitting"
          text="Create Account"
          @click="register"
        />
      </v-form>
    </v-card-text>
    <v-card-actions class="justify-center">
      <v-btn
        @click="$router.push('login')"
        :disabled="isSubmitting"
        block
        text="Already have an account? Login"
      />
    </v-card-actions>
  </AuthLayout>
</template>
