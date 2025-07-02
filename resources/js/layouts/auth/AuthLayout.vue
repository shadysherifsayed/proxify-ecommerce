<!-- layouts/AuthLayout.vue -->
<template>
  <v-container fluid class="fill-height pa-0">
    <v-row no-gutters class="fill-height">
      <!-- Left Form Side -->
      <v-col
        cols="12"
        md="6"
        class="d-flex align-center justify-center px-8"
        style="background-color: #1e1e1e;"
      >
        <v-card flat color="transparent" max-width="420" class="w-100">
          <slot />
        </v-card>
      </v-col>

      <!-- Right Image Side -->
      <v-col
        md="6"
        class="d-none d-md-flex align-center justify-center"
        style="background: linear-gradient(to left, #ffffff, #f3f3f3);"
      >
        <img
          src="https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?auto=format&fit=crop&w=800&q=80"
          alt="Decor"
          style="max-width: 80%; max-height: 80%;"
        />
      </v-col>
    </v-row>
  </v-container>
</template>
