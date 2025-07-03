<template>
  <MainLayout>
    <v-container class="py-6">
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
            :loading="saving"
            variant="elevated"
          >
            Save Changes
          </v-btn>
          <v-btn
            color="error"
            prepend-icon="mdi-close"
            @click="cancelEdit"
            variant="outlined"
          >
            Cancel
          </v-btn>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <v-progress-circular color="primary" indeterminate size="64" />
        <p class="text-h6 mt-4">Loading product...</p>
      </div>

      <!-- Error State -->
      <v-alert v-else-if="error" type="error" class="mb-4" :text="error" />

      <!-- Product Details -->
      <div v-else-if="product">
        <v-row class="mb-6">
          <!-- Product Image -->
          <v-col cols="12" lg="6">
            <v-card class="product-image-card" elevation="3">
              <div class="pa-4">
                <v-img
                  :src="editMode ? editForm.image : product.image"
                  :alt="product.title"
                  height="400"
                  cover
                  class="product-image mb-4"
                >
                  <template v-slot:placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                      <v-progress-circular
                        color="grey-lighten-4"
                        indeterminate
                      />
                    </div>
                  </template>
                  <template v-slot:error>
                    <div
                      class="d-flex align-center justify-center fill-height bg-grey-lighten-3"
                    >
                      <v-icon icon="mdi-image-broken" color="grey" size="64" />
                    </div>
                  </template>
                </v-img>

                <!-- Edit Image Input -->
                <div v-if="editMode">
                  <v-tabs v-model="imageInputTab" color="primary" class="mb-4">
                    <v-tab value="url">Image URL</v-tab>
                    <v-tab value="upload">Upload Image</v-tab>
                  </v-tabs>

                  <v-window v-model="imageInputTab">
                    <v-window-item value="url">
                      <v-text-field
                        v-model="editForm.image"
                        label="Image URL"
                        prepend-inner-icon="mdi-link"
                        variant="outlined"
                        density="comfortable"
                        :error-messages="validationErrors.image"
                        placeholder="https://example.com/image.jpg"
                      />
                    </v-window-item>

                    <v-window-item value="upload">
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

                      <v-progress-linear
                        v-if="uploadingImage"
                        color="primary"
                        indeterminate
                        class="mt-2"
                      />

                      <div
                        v-if="
                          selectedImageFile &&
                          selectedImageFile.length > 0 &&
                          !uploadingImage
                        "
                        class="mt-2"
                      >
                        <v-chip color="success" size="small">
                          <v-icon start icon="mdi-check" />
                          Image ready for upload
                        </v-chip>
                      </div>
                    </v-window-item>
                  </v-window>
                </div>
              </div>
            </v-card>
          </v-col>
          <!-- Product Information -->
          <v-col cols="12" lg="6">
            <v-card elevation="3" class="h-100 product-info-card">
              <v-card-text class="pa-6">
                <!-- Title -->
                <div class="mb-6">
                  <v-label
                    class="text-subtitle-1 font-weight-medium mb-3 d-block"
                    >Product Title</v-label
                  >
                  <div v-if="!editMode">
                    <h2 class="text-h5 font-weight-bold text-primary">
                      {{ product.title }}
                    </h2>
                  </div>
                  <v-text-field
                    v-else
                    v-model="editForm.title"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="validationErrors.title"
                    placeholder="Enter product title"
                  />
                </div>

                <!-- Price -->
                <div class="mb-6">
                  <v-label
                    class="text-subtitle-1 font-weight-medium mb-3 d-block"
                    >Price</v-label
                  >
                  <div v-if="!editMode">
                    <div class="text-h4 text-success font-weight-bold">
                      ${{ product.price.toFixed(2) }}
                    </div>
                  </div>
                  <v-text-field
                    v-else
                    v-model.number="editForm.price"
                    variant="outlined"
                    density="comfortable"
                    type="number"
                    step="0.01"
                    min="0"
                    prepend-inner-icon="mdi-currency-usd"
                    :error-messages="validationErrors.price"
                    placeholder="0.00"
                  />
                </div>

                <!-- Category -->
                <div class="mb-6">
                  <v-label
                    class="text-subtitle-1 font-weight-medium mb-3 d-block"
                    >Category</v-label
                  >
                  <div v-if="!editMode">
                    <v-chip
                      color="primary"
                      variant="elevated"
                      size="large"
                      prepend-icon="mdi-tag"
                    >
                      {{ product.category.name }}
                    </v-chip>
                  </div>
                  <v-select
                    v-else
                    v-model="editForm.category_id"
                    :items="categories"
                    item-title="name"
                    item-value="id"
                    variant="outlined"
                    density="comfortable"
                    prepend-inner-icon="mdi-tag"
                    :error-messages="validationErrors.category_id"
                    placeholder="Select a category"
                    :loading="loadingCategories"
                  />
                </div>

                <!-- Rating -->
                <div class="mb-6">
                  <v-label
                    class="text-subtitle-1 font-weight-medium mb-3 d-block"
                    >Customer Rating</v-label
                  >
                  <div class="d-flex align-center">
                    <v-rating
                      :model-value="product.rating"
                      readonly
                      density="comfortable"
                      color="amber"
                      half-increments
                      size="large"
                    />
                    <div class="ml-4">
                      <div class="text-h6 font-weight-bold">
                        {{ product.rating }}/5
                      </div>
                      <div class="text-body-2 text-grey-darken-1">
                        ({{ product.reviews_count }} reviews)
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                  <v-label
                    class="text-subtitle-1 font-weight-medium mb-3 d-block"
                    >Description</v-label
                  >
                  <div v-if="!editMode">
                    <p
                      class="text-body-1 text-grey-darken-2 line-height-relaxed"
                    >
                      {{ product.description }}
                    </p>
                  </div>
                  <v-textarea
                    v-else
                    v-model="editForm.description"
                    variant="outlined"
                    rows="5"
                    :error-messages="validationErrors.description"
                    placeholder="Enter product description"
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
import CategoryService from '@/Services/CategoryService';
import FileUploadService from '@/Services/FileUploadService';
import ProductService from '@/Services/ProductService';
import { Category, Product } from '@/Types/entities';
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const product = ref<Product | null>(null);
const categories = ref<Category[]>([]);
const loading = ref(false);
const loadingCategories = ref(false);
const error = ref('');
const editMode = ref(false);
const saving = ref(false);
const successMessage = ref('');
const validationErrors = ref<Record<string, string[]>>({});

// Image handling
const imageInputTab = ref('url');
const selectedImageFile = ref<File[]>([]);
const uploadingImage = ref(false);

const editForm = ref({
  title: '',
  description: '',
  price: 0,
  image: '',
  category_id: 0,
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

async function fetchCategories() {
  loadingCategories.value = true;

  try {
    const response = await CategoryService.fetchCategories();
    categories.value = response.categories;
  } catch (err: any) {
    console.error('Error fetching categories:', err);
    // Don't show error to user as this is not critical
  } finally {
    loadingCategories.value = false;
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
    category_id: product.value.category.id,
  };
  validationErrors.value = {};

  // Fetch categories when entering edit mode
  fetchCategories();
}

function cancelEdit() {
  editMode.value = false;
  validationErrors.value = {};
  selectedImageFile.value = [];
  uploadingImage.value = false;
  imageInputTab.value = 'url';
  editForm.value = {
    title: '',
    description: '',
    price: 0,
    image: '',
    category_id: 0,
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

async function handleImageUpload() {
  if (selectedImageFile.value.length === 0) return;

  const file = selectedImageFile.value;

  // Validate file
  const validation = FileUploadService.validateImageFile(file);
  if (!validation.isValid) {
    validationErrors.value.image = [validation.error || 'Invalid file'];
    return;
  }

  uploadingImage.value = true;
  validationErrors.value.image = [];

  try {
    const result = await FileUploadService.uploadImage(productId.value, file);
    editForm.value.image = result.url;
  } catch (err) {
    console.error('Error uploading image:', err);
    validationErrors.value.image = ['Failed to upload image'];
  } finally {
    uploadingImage.value = false;
  }
}

onMounted(fetchProduct);
</script>

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
