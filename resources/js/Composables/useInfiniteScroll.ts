import { ref, onMounted, onUnmounted } from 'vue';

interface UseInfiniteScrollOptions {
  loadMore: () => Promise<void>;
  hasMore: () => boolean;
  isLoading: () => boolean;
  threshold?: number;
}

export function useInfiniteScroll({
  loadMore,
  hasMore,
  isLoading,
  threshold = 200
}: UseInfiniteScrollOptions) {
  const isLoadingMore = ref(false);

  const handleScroll = async () => {
    if (isLoading() || isLoadingMore.value || !hasMore()) {
      return;
    }

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    // Check if user has scrolled near the bottom
    if (scrollTop + windowHeight >= documentHeight - threshold) {
      isLoadingMore.value = true;
      try {
        await loadMore();
      } catch (error) {
        console.error('Error loading more items:', error);
      } finally {
        isLoadingMore.value = false;
      }
    }
  };

  const throttledHandleScroll = throttle(handleScroll, 100);

  onMounted(() => {
    window.addEventListener('scroll', throttledHandleScroll);
  });

  onUnmounted(() => {
    window.removeEventListener('scroll', throttledHandleScroll);
  });

  return {
    isLoadingMore
  };
}

// Simple throttle function
function throttle<T extends (...args: any[]) => any>(
  func: T,
  delay: number
): (...args: Parameters<T>) => void {
  let timeoutId: ReturnType<typeof setTimeout> | null = null;
  let lastExecTime = 0;

  return (...args: Parameters<T>) => {
    const currentTime = Date.now();

    if (currentTime - lastExecTime > delay) {
      func(...args);
      lastExecTime = currentTime;
    } else {
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
      timeoutId = setTimeout(() => {
        func(...args);
        lastExecTime = Date.now();
        timeoutId = null;
      }, delay - (currentTime - lastExecTime));
    }
  };
}
