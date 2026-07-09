// =============================================
// FreshMart Online - Main JavaScript
// =============================================

// Add to Cart via AJAX
function addToCart(productId) {
    fetch(baseUrl + '/src/views/public/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add&product_id=' + productId + '&qty=1'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Produk ditambahkan ke keranjang!');
            updateCartCount(data.cart_count);
        } else {
            alert(data.message || 'Gagal menambahkan ke keranjang');
        }
    })
    .catch(() => alert('Terjadi kesalahan jaringan'));
}

// Update cart count in navbar
function updateCartCount(count) {
    const badge = document.querySelector('.badge.bg-danger');
    if (badge) {
        badge.textContent = count;
    }
}

// Remove from cart via AJAX
function removeFromCart(productId) {
    fetch(baseUrl + '/src/views/public/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=remove&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(() => alert('Terjadi kesalahan'));
}

// Update quantity in cart
function updateCartQty(productId, qty) {
    if (qty < 1) {
        removeFromCart(productId);
        return;
    }
    fetch(baseUrl + '/src/views/public/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=update&product_id=' + productId + '&qty=' + qty
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(() => alert('Terjadi kesalahan'));
}

// Confirm delete action
function confirmDelete(message) {
    return confirm(message || 'Apakah Anda yakin ingin menghapus?');
}

// Format number
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Star rating widget
function initStarRating(containerId, inputId) {
    const container = document.getElementById(containerId);
    const input = document.getElementById(inputId);
    if (!container || !input) return;

    container.querySelectorAll('i').forEach((star, index) => {
        star.addEventListener('click', () => {
            input.value = index + 1;
            container.querySelectorAll('i').forEach((s, i) => {
                s.classList.toggle('text-warning', i <= index);
            });
        });

        star.addEventListener('mouseover', () => {
            container.querySelectorAll('i').forEach((s, i) => {
                s.classList.toggle('text-warning', i <= index);
            });
        });
    });

    container.addEventListener('mouseleave', () => {
        const val = parseInt(input.value) || 0;
        container.querySelectorAll('i').forEach((s, i) => {
            s.classList.toggle('text-warning', i < val);
        });
    });
}

// Image preview before upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        reader.onload = (e) => { preview.src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    }
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        });
    }, 5000);
});
