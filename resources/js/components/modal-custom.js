const openModal = ({ title, body, confirmText, confirmClass, onConfirm }) => {
    document.getElementById("modal-title").textContent = title;
    document.getElementById("modal-body").textContent = body;
    const btn = document.getElementById("modal-confirm");
    btn.textContent = confirmText;
    btn.className = "btn " + (confirmClass || "");
    btn.onclick = onConfirm;
    document.getElementById("modal").classList.add("open");
};

window.openModal = openModal;
export { openModal };
