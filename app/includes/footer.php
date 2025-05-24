<link rel="stylesheet" href="assets/css/style.css">
<footer class="site-footer" style="text-align: center; padding: 5px 0; font-size: 14px; color: #888;">
    <p>
        <a href="#" onclick="openModal('privacy')">Privacy</a> · 
        <a href="#" onclick="openModal('terms')">Terms</a> · 
        <a href="#" onclick="openModal('cookies')">Cookies</a><br> 
        <b>FriendNest © <?= date('Y') ?> </b>
    </p>
</footer>

<!-- Modal Container -->
<div id="legalModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <div id="modalText" class="modal-text">
            <!-- Content loaded via JavaScript -->
        </div>
    </div>
</div>

<!-- Modal Styles -->
<style>
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.modal-content {
    background: #fff;
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 10px;
    padding: 30px;
    position: relative;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}
.close-btn {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 28px;
    color: #333;
    cursor: pointer;
}
.modal-text {
    font-size: 15px;
    color: #333;
    line-height: 1.6;
}
</style>

<script>
const legalTexts = {
    privacy: `<?php ob_start(); include('assets/legal/privacy.php'); echo addslashes(ob_get_clean()); ?>`,
terms: `<?php ob_start(); include('assets/legal/terms.php'); echo addslashes(ob_get_clean()); ?>`,
cookies: `<?php ob_start(); include('assets/legal/cookies.php'); echo addslashes(ob_get_clean()); ?>`
};

function openModal(type) {
    document.getElementById('modalText').innerHTML = legalTexts[type];
    document.getElementById('legalModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('legalModal').style.display = 'none';
}
</script>