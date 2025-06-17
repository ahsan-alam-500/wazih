// resources/js/composables/useCart.js
import { ref, watch } from 'vue';

const storedCart = localStorage.getItem('cart');
const cart = ref(storedCart ? JSON.parse(storedCart) : []);

export default function useCart() {
    const addToCart = (product) => {
        const existing = cart.value.find((item) => item.id === product.id);
        if (existing) {
            // Do not increase quantity here
            return false; // indicate not added
        } else {
            cart.value.push({ ...product, quantity: 1 });
            return true; // indicate added
        }
    };

    const removeFromCart = (productId) => {
        cart.value = cart.value.filter((item) => item.id !== productId);
    };

    const clearCart = () => {
        cart.value = [];
    };

    function increaseQuantity(productId) {
        const item = cart.value.find((i) => i.id === productId);
        if (item) item.quantity++;
    }

    function decreaseQuantity(productId) {
        const item = cart.value.find((i) => i.id === productId);
        if (item && item.quantity > 1) {
            item.quantity--;
        }
    }

    watch(
        cart,
        (newCart) => {
            localStorage.setItem('cart', JSON.stringify(newCart));
        },
        { deep: true },
    );

    return {
        cart,
        addToCart,
        removeFromCart,
        clearCart,
        increaseQuantity,
        decreaseQuantity,
    };
}

