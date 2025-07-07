<script lang="ts" setup>
import EmptyState from '@/Components/General/EmptyState.vue';
import SpinnerLoader from '@/Components/General/SpinnerLoader.vue';
import ProductDetails from '@/Components/Products/ProductDetails.vue';
import ProductForm from '@/Components/Products/ProductForm.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useCategoriesStore } from '@/Stores/categories';
import { useProductsStore } from '@/Stores/products';
import { Product } from '@/Types/entities';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const error = ref<string | null>(null);
const editMode = ref(false);
const productData = ref<
  Pick<Product, 'title' | 'description' | 'price' | 'category_id'>
>({
  title: '',
  description: '',
  price: 0,
  category_id: 0,
});
const validationErrors = ref<Record<string, string[]>>({});

const productsStore = useProductsStore();
const { product, isLoading, isUpdating } = storeToRefs(productsStore);

const categoriesStore = useCategoriesStore();
const { categories } = storeToRefs(categoriesStore);

// Image handling
const selectedImageFile = ref<File | null>(null);

const productId = computed(() => route.params.id as unknown as number);

async function fetchProduct() {
  if (!productId.value || isNaN(productId.value)) {
    error.value = 'Invalid product ID';
    return;
  }
  await productsStore.fetchProduct(productId.value);
  if (!product.value) {
    error.value = 'Product not found';
    return;
  }
}

function startEdit() {
  if (!product.value) return;
  productData.value = {
    title: product.value.title,
    description: product.value.description,
    price: product.value.price,
    category_id: product.value.category_id,
  };
  editMode.value = true;
  validationErrors.value = {};
}

function cancelEdit() {
  editMode.value = false;
  validationErrors.value = {};
  selectedImageFile.value = null;
  // Reset product data to original values
  if (product.value) {
    productData.value = {
      title: product.value.title,
      description: product.value.description,
      price: product.value.price,
      category_id: product.value.category_id,
    };
  }
}

async function saveChanges() {
  if (!product.value) return;
  try {
    validationErrors.value = {};
    await productsStore.updateProduct(product.value.id, productData.value);
    editMode.value = false;
  } catch (err: any) {
    if (err.validationErrors) {
      validationErrors.value = err.validationErrors;
    }
  }
}

async function handleImageUpload() {
  if (!selectedImageFile.value || !product.value) return;
  try {
    await productsStore.updateProductImage(
      product.value.id,
      selectedImageFile.value,
    );
  } catch {
    validationErrors.value.image = ['Failed to upload image'];
  }
}

onMounted(fetchProduct);
</script>

<template>
  <MainLayout>
    <v-container class="py-6" max-width="1200">
      <!-- Back Button and Header -->
      <div class="d-flex justify-space-between mb-6">
        <v-btn
          variant="text"
          prepend-icon="mdi-arrow-left"
          @click="$router.push('/products')"
          class="mr-4"
        >
          Back to Products
        </v-btn>

        <v-btn
          v-if="product && !editMode"
          color="primary"
          prepend-icon="mdi-pencil"
          @click="startEdit"
          variant="elevated"
        >
          Edit Product
        </v-btn>

        <div v-else-if="editMode" class="d-flex ga-2">
          <v-btn
            color="success"
            prepend-icon="mdi-check"
            @click="saveChanges"
            :isLoading="isUpdating"
            :disabled="isUpdating"
            variant="elevated"
          >
            Save Changes
          </v-btn>
          <v-btn
            color="error"
            prepend-icon="mdi-close"
            @click="cancelEdit"
            :disabled="isUpdating"
            variant="outlined"
          >
            Cancel
          </v-btn>
        </div>
      </div>

      <!-- isLoading State -->
      <SpinnerLoader v-if="isLoading" text="Loading product details..." />

      <!-- Product Details -->
      <div v-else-if="product">
        <v-row class="mb-6">
          <!-- Product Image -->
          <v-col cols="12" lg="4">
            <v-card class="product-image-card" elevation="3">
              <div class="pa-4">
                <v-img
                  :src="product.image"
                  :alt="product.title"
                  contain
                  class="product-image mb-4"
                >
                  <template #placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                      <v-progress-circular
                        color="grey-lighten-4"
                        indeterminate
                      />
                    </div>
                  </template>
                  <template #error>
                    <div
                      class="d-flex align-center justify-center fill-height bg-grey-lighten-3"
                    >
                      <v-icon icon="mdi-image-broken" color="grey" size="64" />
                    </div>
                  </template>
                </v-img>

                <!-- Edit Image Input -->
                <div v-if="editMode">
                  <v-file-input
                    v-model="selectedImageFile"
                    label="Upload Image"
                    prepend-inner-icon="mdi-camera"
                    variant="outlined"
                    density="comfortable"
                    accept="image/*"
                    :error-messages="validationErrors.image"
                    @change="handleImageUpload"
                  />
                </div>
              </div>
            </v-card>
          </v-col>
          <!-- Product Information -->
          <v-col cols="12" lg="8">
            <ProductDetails v-if="!editMode" :product="product" />

            <ProductForm
              v-else
              :product="product"
              :categories="categories"
              :validation-errors="validationErrors"
              v-model="productData"
            />
          </v-col>
        </v-row>
      </div>

      <!-- Not Found State -->
      <EmptyState
        v-else
        title="Product Not Found"
        description="The product you are looking for does not exist or has been removed."
        icon="mdi-package-variant-closed"
        :back-action="() => $router.push('/products')"
        back-text="Back to Products"
      />
    </v-container>
  </MainLayout>
</template>

<style scoped>
.product-image-card {
  position: sticky;
  top: 20px;
  border-radius: 16px !important;
  overflow: hidden;
}

.product-info-card {
  border-radius: 16px !important;
  overflow: hidden;
}

.product-image {
  border-radius: 12px;
  transition: all 0.3s ease;
}

.product-image:hover {
  transform: scale(1.02);
}

.v-card {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.v-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.text-h3 {
  line-height: 1.2;
}

.line-height-relaxed {
  line-height: 1.7;
}

.v-rating {
  margin-right: 8px;
}

.v-chip {
  transition: all 0.2s ease;
}

.v-chip:hover {
  transform: translateY(-1px);
}

.v-btn {
  transition: all 0.2s ease;
}

.v-btn:hover {
  transform: translateY(-1px);
}

.bg-grey-lighten-5 {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

@media (max-width: 960px) {
  .product-image-card {
    position: static;
  }

  .v-card:hover {
    transform: none;
  }
}
</style>
