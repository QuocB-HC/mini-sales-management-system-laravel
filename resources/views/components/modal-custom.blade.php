<link rel="stylesheet" href="{{ asset('css/components/modal-custom.css') }}">

<div class="modal-backdrop" id="modal" onclick="handleBackdropClick(event)">
    <div class="modal-box">
        <div class="modal-header">
            <span class="modal-title" id="modal-title"></span>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body" id="modal-body"></div>
        <div class="modal-footer">
            <button class="btn " onclick="closeModal()">Cancel</button>
            <button class="btn" id="modal-confirm"></button>
        </div>
    </div>
</div>

<script>
    function closeModal() {
        document.getElementById("modal").classList.remove("open");
    }

    function handleBackdropClick(e) {
        if (e.target === document.getElementById("modal")) closeModal();
    }
</script>
