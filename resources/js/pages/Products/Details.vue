<template>
  <MainLayout>
    <v-container>
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <v-progress-circular color="primary" indeterminate size="64" />
        <p class="text-h6 mt-4">Loading product...</p>
      </div>

      <!-- Error State -->
      <v-alert v-else-if="error" type="error" class="mb-4" :text="error" />

      <!-- Product Details -->
      <div v-else-if="product">
        <v-row>
          <!-- Product Image -->
          <v-col cols="12" md="6">
            <v-card class="product-image-card" elevation="2">
              <v-img
                :src="editMode ? editForm.image : product.image"
                :alt="product.title"
                height="500"
                cover
                class="product-image"
              >
                <template v-slot:placeholder>
                  <div class="d-flex align-center justify-center fill-height">
                    <v-progress-circular color="grey-lighten-4" indeterminate />
                  </div>
                </template>
              </v-img>

              <!-- Edit Image Input -->
              <v-card-text v-if="editMode" class="pt-2">
                <v-text-field
                  v-model="editForm.image"
                  label="Image URL"
                  prepend-inner-icon="mdi-image"
                  variant="outlined"
                  density="compact"
                  :error-messages="validationErrors.image"
                />
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Product Information -->
          <v-col cols="12" md="6">
            <v-card elevation="2" class="h-100">
              <v-card-title class="d-flex justify-space-between align-center">
                <span>Product Details</span>
                <div>
                  <v-btn
                    v-if="!editMode"
                    icon="mdi-pencil"
                    variant="text"
                    @click="startEdit"
                    color="primary"
                  />
                  <div v-else>
                    <v-btn
                      icon="mdi-check"
                      variant="text"
                      @click="saveChanges"
                      color="success"
                      :loading="saving"
                      class="mr-2"
                    />
                    <v-btn
                      icon="mdi-close"
                      variant="text"
                      @click="cancelEdit"
                      color="error"
                    />
                  </div>
                </div>
              </v-card-title>

              <v-card-text>
                <!-- Title -->
                <div class="mb-4">
                  <v-label class="text-subtitle-2 mb-2">Title</v-label>
                  <div v-if="!editMode">
                    <h2 class="text-h4 font-weight-bold">
                      {{ product.title }}
                    </h2>
                  </div>
                  <v-text-field
                    v-else
                    v-model="editForm.title"
                    variant="outlined"
                    density="compact"
                    :error-messages="validationErrors.title"
                  />
                </div>

                <!-- Price -->
                <div class="mb-4">
                  <v-label class="text-subtitle-2 mb-2">Price</v-label>
                  <div v-if="!editMode">
                    <div class="text-h3 text-primary font-weight-bold">
                      ${{ product.price.toFixed(2) }}
                    </div>
                  </div>
                  <v-text-field
                    v-else
                    v-model.number="editForm.price"
                    variant="outlined"
                    density="compact"
                    type="number"
                    step="0.01"
                    min="0"
                    prepend-inner-icon="mdi-currency-usd"
                    :error-messages="validationErrors.price"
                  />
                </div>

                <!-- Category -->
                <div class="mb-4">
                  <v-label class="text-subtitle-2 mb-2">Category</v-label>
                  <v-chip color="primary" variant="tonal" size="small">
                    {{ product.category.name }}
                  </v-chip>
                </div>

                <!-- Rating -->
                <div class="mb-4">
                  <v-label class="text-subtitle-2 mb-2">Rating</v-label>
                  <div class="d-flex align-center">
                    <v-rating
                      :model-value="product.rating"
                      readonly
                      density="compact"
                      color="amber"
                      half-increments
                    />
                    <span class="ml-2 text-body-2">
                      {{ product.rating }}/5 ({{ product.reviews_count }} reviews)
                    </span>
                  </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                  <v-label class="text-subtitle-2 mb-2">Description</v-label>
                  <div v-if="!editMode">
                    <p class="text-body-1">{{ product.description }}</p>
                  </div>
                  <v-textarea
                    v-else
                    v-model="editForm.description"
                    variant="outlined"
                    rows="4"
                    :error-messages="validationErrors.description"
                  />
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Success Message -->
        <v-alert
          v-if="successMessage"
          type="success"
          class="mt-4"
          :text="successMessage"
          closable
          @update:model-value="successMessage = ''"
        />
      </div>
    </v-container>
  </MainLayout>
</template>

<script lang="ts" setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import ProductService from '@/Services/ProductService';
import { useCartStore } from '@/Stores/cart';
import { Product } from '@/Types/entities';
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();

const product = ref<Product | null>(null);
const loading = ref(false);
const error = ref('');
const editMode = ref(false);
const saving = ref(false);
const addingToCart = ref(false);
const successMessage = ref('');
const validationErrors = ref<Record<string, string[]>>({});

const editForm = ref({
  title: '',
  description: '',
  price: 0,
  image: '',
});

const productId = computed(() => route.params.id as string);

async function fetchProduct() {
  loading.value = true;
  error.value = '';

  try {
    const response = await ProductService.fetchProduct(
      parseInt(productId.value),
    );
    product.value = response.product;
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load product';
    console.error('Error fetching product:', err);
  } finally {
    loading.value = false;
  }
}

function startEdit() {
  if (!product.value) return;

  editMode.value = true;
  editForm.value = {
    title: product.value.title,
    description: product.value.description,
    price: product.value.price,
    image: product.value.image,
  };
  validationErrors.value = {};
}

function cancelEdit() {
  editMode.value = false;
  validationErrors.value = {};
  editForm.value = {
    title: '',
    description: '',
    price: 0,
    image: '',
  };
}

async function saveChanges() {
  if (!product.value) return;

  saving.value = true;
  validationErrors.value = {};

  try {
    const updatedProduct = await ProductService.updateProduct(
      product.value.id,
      editForm.value,
    );
    product.value = updatedProduct.product;
    editMode.value = false;
    successMessage.value = 'Product updated successfully!';

    // Auto-hide success message after 3 seconds
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (err: any) {
    if (err.validationErrors) {
      validationErrors.value = err.validationErrors;
    } else {
      error.value = err.response?.data?.message || 'Failed to update product';
    }
    console.error('Error updating product:', err);
  } finally {
    saving.value = false;
  }
}

onMounted(fetchProduct);
</script>

<style scoped>
.product-image-card {
  position: sticky;
  top: 20px;
}

.product-image {
  border-radius: 8px;
}

.v-card {
  transition: all 0.3s ease;
}

.v-card:hover {
  transform: translateY(-2px);
}

.text-h3 {
  line-height: 1.2;
}

.v-rating {
  margin-right: 8px;
}

@media (max-width: 960px) {
  .product-image-card {
    position: static;
  }
}
</style>
