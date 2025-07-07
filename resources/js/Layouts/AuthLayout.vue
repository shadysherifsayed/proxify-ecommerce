<template>
  <v-app>
    <v-main class="auth-layout">
      <!-- Centered Auth Card -->
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" sm="8" md="5" lg="4">
            <!-- Logo/Brand Section -->
            <div class="brand-section text-center mb-8">
              <div class="logo-container">
                <v-icon
                  icon="mdi-storefront"
                  size="48"
                  color="white"
                  class="logo-icon"
                />
              </div>
              <h1 class="brand-title">ShadeLane</h1>
              <p class="brand-subtitle">Modern E-commerce Experience</p>
            </div>

            <!-- Glass Card -->
            <v-card class="glass-card pa-8" elevation="0" rounded="xl">
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
  background-image: url('https://images.pexels.com/photos/5926398/pexels-photo-5926398.jpeg');
  background-size: cover;
  background-position: center;
  position: absolute;
  inset: 0;
  z-index: 1;
}

.floating-shapes {
  position: absolute;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.brand-section {
  margin-bottom: 2rem;
}

.logo-container {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  margin-bottom: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.logo-icon {
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.brand-title {
  color: white;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: 1px;
}

.brand-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.95rem;
  font-weight: 400;
  margin: 0;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.glass-card {
  background: rgba(255, 255, 255, 0.15) !important;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow:
    0 25px 45px rgba(0, 0, 0, 0.1),
    0 0 0 1px rgba(255, 255, 255, 0.05) inset;
  transition: all 0.3s ease;
}

.glass-card:hover {
  transform: translateY(-2px);
  box-shadow:
    0 35px 65px rgba(0, 0, 0, 0.15),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
}

.glass-card :deep(.v-card-title) {
  color: white !important;
  font-weight: 600;
}

.glass-card :deep(.v-label) {
  color: rgba(255, 255, 255, 0.9) !important;
  font-weight: 500;
}

.glass-card :deep(.v-field__input) {
  color: white !important;
}

.glass-card :deep(.v-field) {
  background: rgba(255, 255, 255, 0.1) !important;
  backdrop-filter: blur(10px);
  border-radius: 12px !important;
}

.glass-card :deep(.v-field--focused) {
  background: rgba(255, 255, 255, 0.2) !important;
}

.glass-card :deep(.v-btn--variant-text) {
  background: transparent !important;
  color: rgba(255, 255, 255, 0.8) !important;
  box-shadow: none !important;
}

.glass-card :deep(.v-btn--variant-text:hover) {
  background: rgba(255, 255, 255, 0.1) !important;
  color: white !important;
}

.glass-card :deep(.v-alert) {
  background: rgba(244, 67, 54, 0.2) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(244, 67, 54, 0.3);
  color: white !important;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .brand-title {
    font-size: 1.75rem;
  }

  .logo-container {
    width: 70px;
    height: 70px;
  }

  .glass-card {
    margin: 0 1rem;
  }
}
</style>
