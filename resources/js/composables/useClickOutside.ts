// composables/useClickOutside.ts
import { onMounted, onUnmounted } from 'vue';

export function useClickOutside(elRef: any, callback: () => void) {
    const handleClick = (e: MouseEvent) => {
        // যদি element attach থাকে এবং click event element এর বাইরে হয়
        if (elRef.value && !elRef.value.contains(e.target)) {
            callback();
        }
    };

    onMounted(() => {
        document.addEventListener('click', handleClick);
    });

    onUnmounted(() => {
        document.removeEventListener('click', handleClick);
    });
}
