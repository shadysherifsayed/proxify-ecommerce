<template>
    <v-app>
        <v-main class="auth-layout">
            <!-- Background Image -->
            <div class="background-image" />

            <!-- Overlay -->
            <div class="overlay" />

            <!-- Centered Auth Card -->
            <v-container class="fill-height" fluid>
                <v-row align="center" justify="center">
                    <v-col cols="12" sm="8" md="4">
                        <v-card class="pa-6" elevation="12" rounded="lg">
                            <slot />
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.auth-layout {
    position: relative;
    height: 100vh;
    width: 100%;
    overflow: hidden;
}

.background-image {
    background-image: url('https://images.pexels.com/photos/3944405/pexels-photo-3944405.jpeg');
    background-size: cover;
    background-position: center;
    position: absolute;
    inset: 0;
    z-index: 1;
}

.overlay {
    background-color: rgba(0, 0, 0, 0.6); /* Opacity over image */
    position: absolute;
    inset: 0;
    z-index: 2;
}
.v-container {
    position: relative;
    z-index: 3;
}
</style>
