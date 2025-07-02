<template>
    <AuthLayout>
        <v-card-title class="text-h5 text-center">Login</v-card-title>
        <v-card-text>
            <v-form @submit="authStore.login(form)">
                <v-text-field label="Email" type="email" required v-model="form.email" />
                <v-text-field label="Password" type="password" required v-model="form.password" />
                <v-btn color="primary" block class="mt-4" @click="register"
                    :loading="isLoading"
                    :disabled="isLoading"
                >Login</v-btn>
            </v-form>
        </v-card-text>
        <v-card-actions class="justify-center">
            <v-btn text @click="$router.push('register')">Don't have an account? Register</v-btn>
        </v-card-actions>
    </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { useAuthStore } from '@/Stores/auth';
import { ref } from 'vue';

const form = ref({
    email: '',
    password: '',
});


const authStore = useAuthStore()


</script>
