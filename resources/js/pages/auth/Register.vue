<template>
    <AuthLayout>
        <v-card-title class="text-h5 text-center">Register</v-card-title>
        <v-card-text>
            <v-form>
                <v-text-field label="Name" required v-model="form.name" />
                <v-text-field label="Email" type="email" required v-model="form.email" />
                <v-text-field label="Password" type="password" required v-model="form.password" />
                <v-text-field label="Password" type="password" required v-model="form.password_confirmation" />

                <v-btn block class="mt-4" @click="register" :loading="isLoading" :disabled="isLoading">Create Account</v-btn>
            </v-form>
        </v-card-text>
        <v-card-actions class="justify-center">
            <v-btn text @click="$router.push('login')"
            :disabled="isLoading"
            >Already have an account? Login</v-btn>
        </v-card-actions>
    </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { useAuthStore } from '@/Stores/auth';
import { ref } from 'vue';

const form = ref<RegisterRequest>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const isLoading = ref(false)

const authStore = useAuthStore()

async function register() {
    try {
        isLoading.value = true
        await authStore.register(form.value)
    } finally {
        isLoading.value = false
    }
}


</script>
