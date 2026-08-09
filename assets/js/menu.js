const burgerMenu = document.querySelector(".burger-menu");
const nav = document.querySelector(".nav");

burgerMenu.addEventListener("click", function () {
  nav.classList.toggle("nav-open");
});
