<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useAuthStore } from '@/Stores/auth';
import { LoginRequest } from '@/Types/requests';
import { validateEmail } from '@/Utils/validation';
import { ref } from 'vue';

const authStore = useAuthStore();

const form = ref<LoginRequest>({
  email: '',
  password: '',
});

const formRef = ref<any>(null);

const rules = ref({
  email: [
    (value: string) => !!value || 'Email is required',
    (value: string) => validateEmail(value) || 'Email must be valid',
  ],
  password: [(value: string) => !!value || 'Password is required'],
});

const isSubmitting = ref(false);
const validationErrors = ref<Record<string, string[]>>({});

async function login() {
  try {
    const { valid } = await formRef.value?.validate();
    if (!valid) {
      return;
    }
    isSubmitting.value = true;
    validationErrors.value = {};
    await authStore.login(form.value);
  } catch (error: any) {
    if (error.validationErrors) {
      validationErrors.value = error.validationErrors;
    }
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<template>
  <AuthLayout>
    <v-card-title class="text-h5 text-center">Login</v-card-title>
    <v-card-text>
      <v-form ref="formRef" validate-on="submit" autocomplete="off">
        <v-label> Email </v-label>
        <v-text-field
          type="email"
          required
          v-model="form.email"
          :rules="rules.email"
          :error-messages="validationErrors.email"
          autocomplete="off"
        />

        <v-label> Password </v-label>
        <v-text-field
          type="password"
          required
          v-model="form.password"
          :rules="rules.password"
          :error-messages="validationErrors.password"
          autocomplete="off"
        />
        <v-btn
          color="primary"
          block
          class="mt-4"
          @click="login"
          :loading="isSubmitting"
          :disabled="isSubmitting"
          text="Login"
        />
      </v-form>
    </v-card-text>
    <v-card-actions class="justify-center">
      <v-btn
        text="Don't have an account? Register"
        @click="$router.push('register')"
        :disabled="isSubmitting"
        block
      />
    </v-card-actions>
  </AuthLayout>
</template>
