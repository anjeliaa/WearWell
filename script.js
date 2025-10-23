const bar = document.getElementById('bar');
const close = document.getElementById('close');
const nav = document.getElementById('navbar');

if (bar) {
    bar.addEventListener('click', () => {
        nav.classList.add('active');
    })
}

if (close) {
    close.addEventListener('click', () => {
        nav.classList.remove('active');
    })
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.cart-btn').forEach(button => {
        button.addEventListener('click', function(e){
            e.preventDefault();

            const name  = this.dataset.name;
            const price = this.dataset.price;
            const image = this.dataset.image;

            fetch('cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `product_name=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}&image=${encodeURIComponent(image)}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(err => console.error(err));
        });
    });
});

// Ambil semua link di navbar
const navLinks = document.querySelectorAll('.navbar .menu a');

// Ambil nama file saat ini, misal dashboard_user.php
const currentPage = window.location.pathname.split("/").pop();

navLinks.forEach(link => {
    const linkPage = link.getAttribute('href'); // ambil href
    if (linkPage === currentPage) {
        link.classList.add('active'); // tambahkan class active
    }
});

function toggleCheckout() {
    const form = document.getElementById('checkoutForm');
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}