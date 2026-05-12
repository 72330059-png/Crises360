document.addEventListener("DOMContentLoaded", () => {

    /* ------------------------------
        SIDEBAR TOGGLE
    ------------------------------ */
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("crm-sidebar");

    if (toggleSidebar && sidebar) {
        toggleSidebar.addEventListener("click", () => {
            sidebar.classList.toggle("collapsed");
            sidebar.classList.toggle("expanded");
        });
    }

   /* ------------------------------
    USER DROPDOWN
------------------------------ */
const userDropdown = document.querySelector(".crm-user-dropdown");
const dropdownMenu = document.querySelector(".crm-user-dropdown .dropdown-menu");
const arrow = document.querySelector(".admin-arrow");

if (userDropdown && dropdownMenu && arrow) {
    userDropdown.addEventListener("click", (e) => {
        e.stopPropagation();

        dropdownMenu.classList.toggle("show-menu");
        arrow.classList.toggle("rotate");   
    });

    document.addEventListener("click", () => {
        dropdownMenu.classList.remove("show-menu");
        arrow.classList.remove("rotate");   
    });
}




});


