import { ref, readonly } from 'vue';
import axios from 'axios';

const countries = ref([]);
const categories = ref([]);
const loading = ref(false);
const loaded = ref(false);

export function useFilterOptions() {
  async function fetchOptions() {
    if (loaded.value) return;
    loading.value = true;
    try {
      const { data } = await axios.get('/api/filter-options');
      countries.value = data.countries || [];
      categories.value = data.categories || [];
      loaded.value = true;
    } catch (e) {
      console.error('Failed to load filter options:', e);
    } finally {
      loading.value = false;
    }
  }

  // Automatically fetch on first call
  if (!loaded.value && !loading.value) {
    fetchOptions();
  }

  return {
    countries: readonly(countries),
    categories: readonly(categories),
    loading: readonly(loading),
    refresh: fetchOptions,
  };
}