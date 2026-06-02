


const images = [
    "Image/bg1.jpg",
    "Image/bg2.jpg",
    "Image/bg3.jpg",
    "Image/bg4.jpg"
];

let index = 0;

function changeBackground() {
    document.body.style.backgroundImage = `url(${images[index]})`;
    document.body.style.backgroundSize = "cover";
    document.body.style.backgroundPosition = "center";
    document.body.style.transition = "1s";

    index++;

    if (index >= images.length) {
        index = 0;
    }
}

setInterval(changeBackground, 3000);
changeBackground();




let cart = [];

function addToCart(name, price) {
    cart.push({ name, price });

    alert(name + " added to cart ✔");

    updateCartCount();

    console.log("Cart Items:", cart);
}

function updateCartCount() {
    let cartCount = document.getElementById("cartCount");

    if (cartCount) {
        cartCount.innerText = cart.length;
    }
}




document.querySelectorAll("a[href^='#']").forEach(anchor => {
    anchor.addEventListener("click", function (e) {
        e.preventDefault();

        document.querySelector(this.getAttribute("href"))
            .scrollIntoView({
                behavior: "smooth"
            });
    });
});



window.addEventListener("scroll", function () {
    let nav = document.querySelector("nav");

    if (!nav) return;

    if (window.scrollY > 50) {
        nav.style.background = "rgba(0,0,0,0.8)";
        nav.style.transition = "0.3s";
    } else {
        nav.style.background = "transparent";
    }
});





window.addEventListener("load", function () {
    document.body.style.opacity = "0";

    setTimeout(() => {
        document.body.style.transition = "1s";
        document.body.style.opacity = "1";
    }, 200);
});



console.log("Smart Canteen System Loaded ✔");
